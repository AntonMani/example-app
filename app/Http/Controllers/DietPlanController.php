<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DietPlanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('diet-plan', compact('user'));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();

        // Check if user has required data (age OR birth_date, plus height and weight)
        $hasAge = $user->age || $user->birth_date;
        $hasHeight = $user->height !== null && $user->height > 0;
        $hasWeight = $user->weight !== null && $user->weight > 0;

        if (!$hasAge || !$hasHeight || !$hasWeight) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please complete your profile (birth date, height, weight) first!');
        }


        // Calculate BMI
        $heightInMeters = $user->height / 100;
        $bmi = $user->weight / ($heightInMeters * $heightInMeters);

        // Prepare the prompt for Claude
        $prompt = $this->buildPrompt($user, $bmi);

        try {
            // Call Anthropic API (Claude)
            $response = Http::withHeaders([
                'x-api-key' => env('ANTHROPIC_API_KEY'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $dietPlan = $data['content'][0]['text'];

                return view('diet-plan', [
                    'user' => $user,
                    'dietPlan' => $dietPlan,
                    'bmi' => round($bmi, 1)
                ]);
            } else {
                return back()->with('error', 'Failed to generate diet plan. Please try again.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function buildPrompt($user, $bmi)
    {
        $bmiCategory = $this->getBMICategory($bmi);

        // Calculate age from birth_date if age is not set
        $age = $user->age;
        if (!$age && $user->birth_date) {
            $age = \Carbon\Carbon::parse($user->birth_date)->age;
        }

        return "You are a professional nutritionist. Create a personalized diet plan for the following person:

Name: {$user->first_name} {$user->last_name}
Age: {$age} years
Height: {$user->height} cm
Weight: {$user->weight} kg
BMI: " . round($bmi, 1) . " ({$bmiCategory})

Please provide:
1. A brief health assessment based on their BMI
2. Daily calorie recommendation
3. Macronutrient breakdown (proteins, carbs, fats)
4. A sample 7-day meal plan with breakfast, lunch, dinner, and snacks
5. General nutrition tips specific to their profile
6. Foods to prioritize and foods to limit

Make it practical, easy to follow, and encouraging. Format the response in a clear, organized way.";
    }

    private function getBMICategory($bmi)
    {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal weight';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }
}
