<?php

namespace App\Http\Controllers;

use App\Models\DietPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Carbon\Carbon;

class DietPlanController extends Controller
{
    // Maximum number of plans a user can generate
    const MAX_PLANS_PER_USER = 2;

    public function index()
    {
        $user = Auth::user();

        // Get user's existing plans count
        $plansCount = $user->dietPlans()->count();
        $hasReachedLimit = $plansCount >= self::MAX_PLANS_PER_USER;

        // Get latest plan if exists
        $latestPlan = $user->dietPlans()->latest()->first();

        return view('diet-plan', [
            'user' => $user,
            'plansCount' => $plansCount,
            'maxPlans' => self::MAX_PLANS_PER_USER,
            'hasReachedLimit' => $hasReachedLimit,
            'latestPlan' => $latestPlan
        ]);
    }

    public function generate(Request $request)
    {
        $user = Auth::user();

        // Check if user has reached the limit
        $plansCount = $user->dietPlans()->count();
        if ($plansCount >= self::MAX_PLANS_PER_USER) {
            return redirect()->route('diet-plan')
                ->with('error', "You've reached your limit of " . self::MAX_PLANS_PER_USER . " diet plans. Delete an existing plan to generate a new one.");
        }

        // Calculate age from birth_date if age field is not set
        $age = $user->age;
        if (!$age && $user->birth_date) {
            $age = Carbon::parse($user->birth_date)->age;
        }

        // Check if user has required data
        $hasAge = $age !== null;
        $hasHeight = $user->height !== null && $user->height > 0;
        $hasWeight = $user->weight !== null && $user->weight > 0;

        if (!$hasAge || !$hasHeight || !$hasWeight) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please complete your profile (birth date, height, weight) first!');
        }

        // Calculate BMI
        $heightInMeters = $user->height / 100;
        $bmi = $user->weight / ($heightInMeters * $heightInMeters);
        $bmiCategory = $this->getBMICategory($bmi);

        // Build prompt for AI
        $prompt = $this->buildPrompt($user, $age, $bmi, $bmiCategory);

        try {
            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini', // or 'gpt-4o' for better quality
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional nutritionist. You provide personalized diet plans in JSON format only, with no additional text or markdown formatting.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiResponse = $data['choices'][0]['message']['content'];

                // Parse AI response - remove markdown code blocks if present
                $aiResponse = $this->cleanJsonResponse($aiResponse);

                // Decode JSON
                $dietPlanData = json_decode($aiResponse, true);

                if (!$dietPlanData) {
                    return back()->with('error', 'Failed to parse AI response. Please try again.');
                }

                // Add user info and metadata
                $dietPlanData['user'] = [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'age' => $age,
                    'height' => $user->height,
                    'weight' => $user->weight,
                    'bmi' => round($bmi, 1),
                    'bmi_category' => $bmiCategory
                ];
                $dietPlanData['generated_at'] = now()->format('F d, Y \a\t h:i A');

                // Save to database
                $dietPlan = DietPlan::create([
                    'user_id' => $user->id,
                    'bmi' => round($bmi, 2),
                    'bmi_category' => $bmiCategory,
                    'plan_data' => $dietPlanData,
                ]);

                return redirect()->route('diet-plan.show', $dietPlan->id)
                    ->with('success', 'Your personalized diet plan has been generated successfully!');
            } else {
                $errorMessage = $response->json()['error']['message'] ?? 'Unknown error';
                return back()->with('error', 'OpenAI API Error: ' . $errorMessage);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating diet plan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $dietPlan = DietPlan::where('user_id', Auth::id())->findOrFail($id);

        return view('diet-plan', [
            'user' => Auth::user(),
            'dietPlanData' => $dietPlan->plan_data,
            'bmi' => $dietPlan->bmi,
            'showPlan' => true,
            'planId' => $dietPlan->id,
            'plansCount' => Auth::user()->dietPlans()->count(),
            'maxPlans' => self::MAX_PLANS_PER_USER,
            'hasReachedLimit' => Auth::user()->dietPlans()->count() >= self::MAX_PLANS_PER_USER,
        ]);
    }

    public function destroy($id)
    {
        $dietPlan = DietPlan::where('user_id', Auth::id())->findOrFail($id);
        $dietPlan->delete();

        return redirect()->route('diet-plan')
            ->with('success', 'Diet plan deleted successfully!');
    }

    public function myPlans()
    {
        $user = Auth::user();
        $plans = $user->dietPlans()->latest()->get();

        return view('diet-plan-list', [
            'user' => $user,
            'plans' => $plans,
            'plansCount' => $plans->count(),
            'maxPlans' => self::MAX_PLANS_PER_USER,
        ]);
    }

    /**
     * Build prompt for OpenAI
     */
    private function buildPrompt($user, $age, $bmi, $bmiCategory)
    {
        return "Create a comprehensive, personalized diet plan for the following person:

**Person Details:**
- Name: {$user->first_name} {$user->last_name}
- Age: {$age} years
- Height: {$user->height} cm
- Weight: {$user->weight} kg
- BMI: " . round($bmi, 1) . " ({$bmiCategory})

**Instructions:**
Provide a complete 7-day diet plan in the following JSON structure. Return ONLY valid JSON with no markdown formatting, no code blocks, no additional text.
```json
{
  \"health_assessment\": {
    \"summary\": \"Brief assessment based on their BMI category\",
    \"recommendations\": [
      \"recommendation 1\",
      \"recommendation 2\",
      \"recommendation 3\",
      \"recommendation 4\"
    ]
  },
  \"daily_calories\": {
    \"total\": 2200,
    \"breakdown\": {
      \"breakfast\": {\"calories\": 500, \"percentage\": 23},
      \"lunch\": {\"calories\": 700, \"percentage\": 32},
      \"dinner\": {\"calories\": 700, \"percentage\": 32},
      \"snacks\": {\"calories\": 300, \"percentage\": 13}
    }
  },
  \"macronutrients\": {
    \"protein\": {
      \"grams\": 110,
      \"percentage\": 20,
      \"description\": \"Essential for muscle maintenance and repair\"
    },
    \"carbohydrates\": {
      \"grams\": 275,
      \"percentage\": 50,
      \"description\": \"Primary energy source for daily activities\"
    },
    \"fats\": {
      \"grams\": 73,
      \"percentage\": 30,
      \"description\": \"Healthy fats for hormone balance and brain health\"
    }
  },
  \"meal_plan\": [
    {
      \"day\": \"Monday\",
      \"meals\": {
        \"breakfast\": {
          \"calories\": 500,
          \"items\": [\"item 1\", \"item 2\", \"item 3\"]
        },
        \"lunch\": {
          \"calories\": 700,
          \"items\": [\"item 1\", \"item 2\", \"item 3\", \"item 4\"]
        },
        \"dinner\": {
          \"calories\": 700,
          \"items\": [\"item 1\", \"item 2\", \"item 3\", \"item 4\"]
        },
        \"snacks\": {
          \"calories\": 300,
          \"items\": [\"item 1\", \"item 2\", \"item 3\"]
        }
      }
    }
    // Include all 7 days: Monday through Sunday
  ],
  \"foods_to_prioritize\": [
    {
      \"category\": \"Lean Proteins\",
      \"items\": [\"chicken\", \"fish\", \"turkey\", \"eggs\", \"greek yogurt\"],
      \"icon\": \"🥩\"
    },
    {
      \"category\": \"Whole Grains\",
      \"items\": [\"oatmeal\", \"quinoa\", \"brown rice\", \"whole wheat bread\"],
      \"icon\": \"🌾\"
    },
    {
      \"category\": \"Healthy Fats\",
      \"items\": [\"avocado\", \"nuts\", \"olive oil\", \"fatty fish\"],
      \"icon\": \"🥑\"
    },
    {
      \"category\": \"Vegetables\",
      \"items\": [\"leafy greens\", \"broccoli\", \"carrots\", \"peppers\"],
      \"icon\": \"🥦\"
    },
    {
      \"category\": \"Fruits\",
      \"items\": [\"berries\", \"apples\", \"oranges\", \"bananas\"],
      \"icon\": \"🍎\"
    }
  ],
  \"foods_to_limit\": [
    {
      \"category\": \"Processed Foods\",
      \"items\": [\"chips\", \"cookies\", \"frozen meals\"],
      \"reason\": \"High in sodium and preservatives\",
      \"icon\": \"🚫\"
    },
    {
      \"category\": \"Sugary Drinks\",
      \"items\": [\"soda\", \"sweetened juices\", \"energy drinks\"],
      \"reason\": \"Empty calories with no nutritional value\",
      \"icon\": \"🥤\"
    },
    {
      \"category\": \"Refined Carbs\",
      \"items\": [\"white bread\", \"pastries\", \"white rice\"],
      \"reason\": \"Low nutritional value, causes blood sugar spikes\",
      \"icon\": \"🍞\"
    },
    {
      \"category\": \"Fried Foods\",
      \"items\": [\"fast food\", \"deep-fried items\"],
      \"reason\": \"High in unhealthy fats and calories\",
      \"icon\": \"🍟\"
    }
  ],
  \"nutrition_tips\": [
    \"💧 Stay Hydrated: Drink 8-10 glasses of water daily\",
    \"🍱 Meal Prep: Prepare meals in advance for busy days\",
    \"🍽️ Portion Control: Use smaller plates to manage portions\",
    \"🧘 Mindful Eating: Eat slowly and avoid distractions\",
    \"🏃 Regular Exercise: Combine with 150 minutes of moderate activity per week\",
    \"😴 Sleep Well: Aim for 7-9 hours of quality sleep\",
    \"📝 Track Progress: Keep a food journal to stay accountable\",
    \"🎯 Flexibility: Allow yourself occasional treats (80/20 rule)\"
  ]
}
```

Make the diet plan practical, easy to follow, and specifically tailored to a person with BMI in the {$bmiCategory} category. Ensure all 7 days are included in the meal plan.";
    }

    /**
     * Clean JSON response from AI (remove markdown code blocks)
     */
    private function cleanJsonResponse($response)
    {
        // Remove markdown code blocks
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/', '', $response);

        // Trim whitespace
        $response = trim($response);

        return $response;
    }

    /**
     * Get BMI category
     */
    private function getBMICategory($bmi)
    {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal weight';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }
}
