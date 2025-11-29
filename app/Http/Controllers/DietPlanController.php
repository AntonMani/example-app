<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

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

        // Get mock diet plan as JSON
        $dietPlanData = $this->getMockDietPlanJSON($user, $age, $bmi);

        return view('diet-plan', [
            'user' => $user,
            'dietPlanData' => $dietPlanData,
            'bmi' => round($bmi, 1),
            'showPlan' => true
        ]);
    }

    private function getMockDietPlanJSON($user, $age, $bmi)
    {
        $bmiCategory = $this->getBMICategory($bmi);

        return [
            'user' => [
                'name' => $user->first_name . ' ' . $user->last_name,
                'age' => $age,
                'height' => $user->height,
                'weight' => $user->weight,
                'bmi' => round($bmi, 1),
                'bmi_category' => $bmiCategory
            ],
            'health_assessment' => [
                'summary' => "Your BMI indicates that you are in the {$bmiCategory} category. This diet plan is designed to help you maintain a healthy weight and improve your overall nutrition.",
                'recommendations' => [
                    'Maintain current weight through balanced nutrition',
                    'Focus on whole, unprocessed foods',
                    'Include regular physical activity (150 minutes per week)',
                    'Stay hydrated with 8-10 glasses of water daily'
                ]
            ],
            'daily_calories' => [
                'total' => 2200,
                'breakdown' => [
                    'breakfast' => ['calories' => 500, 'percentage' => 23],
                    'lunch' => ['calories' => 700, 'percentage' => 32],
                    'dinner' => ['calories' => 700, 'percentage' => 32],
                    'snacks' => ['calories' => 300, 'percentage' => 13]
                ]
            ],
            'macronutrients' => [
                'protein' => [
                    'grams' => 110,
                    'percentage' => 20,
                    'description' => 'Essential for muscle maintenance and repair'
                ],
                'carbohydrates' => [
                    'grams' => 275,
                    'percentage' => 50,
                    'description' => 'Primary energy source for daily activities'
                ],
                'fats' => [
                    'grams' => 73,
                    'percentage' => 30,
                    'description' => 'Healthy fats for hormone balance and brain health'
                ]
            ],
            'meal_plan' => [
                [
                    'day' => 'Monday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Oatmeal with berries and almonds',
                                'Greek yogurt (200g)',
                                'Green tea'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Grilled chicken breast (150g)',
                                'Quinoa salad with vegetables',
                                'Olive oil dressing',
                                'Apple'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Baked salmon (180g)',
                                'Roasted sweet potato',
                                'Steamed broccoli',
                                'Mixed green salad'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Handful of mixed nuts',
                                'Banana',
                                'Carrot sticks with hummus'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Tuesday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Whole grain toast with avocado',
                                'Scrambled eggs (2 eggs)',
                                'Orange juice'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Turkey and vegetable wrap',
                                'Brown rice',
                                'Mixed fruit salad'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Lean beef stir-fry with vegetables',
                                'Jasmine rice',
                                'Side salad'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Greek yogurt with honey',
                                'Apple slices with peanut butter'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Wednesday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Smoothie bowl with granola',
                                'Chia seeds and berries',
                                'Herbal tea'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Tuna salad sandwich on whole grain',
                                'Vegetable soup',
                                'Orange'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Grilled chicken with herbs',
                                'Quinoa pilaf',
                                'Roasted vegetables'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Rice cakes with almond butter',
                                'Grapes'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Thursday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Protein pancakes with berries',
                                'Greek yogurt',
                                'Coffee'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Mediterranean chickpea bowl',
                                'Falafel (3 pieces)',
                                'Tzatziki sauce'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Baked cod with lemon',
                                'Wild rice',
                                'Asparagus'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Trail mix',
                                'Pear'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Friday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Egg white omelet with vegetables',
                                'Whole grain toast',
                                'Fresh juice'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Chicken Caesar salad (light dressing)',
                                'Whole grain roll',
                                'Melon'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Shrimp pasta with tomato sauce',
                                'Side salad',
                                'Garlic bread (1 slice)'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Cottage cheese with berries',
                                'Almonds'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Saturday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'French toast with fruit',
                                'Turkey bacon (2 strips)',
                                'Green tea'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Veggie burger with sweet potato fries',
                                'Coleslaw',
                                'Apple'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Grilled steak (150g)',
                                'Baked potato',
                                'Green beans'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Protein bar',
                                'Orange'
                            ]
                        ]
                    ]
                ],
                [
                    'day' => 'Sunday',
                    'meals' => [
                        'breakfast' => [
                            'calories' => 500,
                            'items' => [
                                'Breakfast burrito with eggs and beans',
                                'Salsa',
                                'Coffee'
                            ]
                        ],
                        'lunch' => [
                            'calories' => 700,
                            'items' => [
                                'Grilled vegetable sandwich',
                                'Lentil soup',
                                'Mixed berries'
                            ]
                        ],
                        'dinner' => [
                            'calories' => 700,
                            'items' => [
                                'Roasted chicken thighs',
                                'Couscous',
                                'Mediterranean salad'
                            ]
                        ],
                        'snacks' => [
                            'calories' => 300,
                            'items' => [
                                'Dark chocolate (30g)',
                                'Strawberries'
                            ]
                        ]
                    ]
                ]
            ],
            'foods_to_prioritize' => [
                [
                    'category' => 'Lean Proteins',
                    'items' => ['Chicken', 'Fish', 'Turkey', 'Eggs', 'Greek yogurt'],
                    'icon' => '🥩'
                ],
                [
                    'category' => 'Whole Grains',
                    'items' => ['Oatmeal', 'Quinoa', 'Brown rice', 'Whole wheat bread'],
                    'icon' => '🌾'
                ],
                [
                    'category' => 'Healthy Fats',
                    'items' => ['Avocado', 'Nuts', 'Olive oil', 'Fatty fish'],
                    'icon' => '🥑'
                ],
                [
                    'category' => 'Vegetables',
                    'items' => ['Leafy greens', 'Broccoli', 'Carrots', 'Peppers'],
                    'icon' => '🥦'
                ],
                [
                    'category' => 'Fruits',
                    'items' => ['Berries', 'Apples', 'Oranges', 'Bananas'],
                    'icon' => '🍎'
                ]
            ],
            'foods_to_limit' => [
                [
                    'category' => 'Processed Foods',
                    'items' => ['Chips', 'Cookies', 'Frozen meals'],
                    'reason' => 'High in sodium and preservatives',
                    'icon' => '🚫'
                ],
                [
                    'category' => 'Sugary Drinks',
                    'items' => ['Soda', 'Sweetened juices', 'Energy drinks'],
                    'reason' => 'Empty calories with no nutritional value',
                    'icon' => '🥤'
                ],
                [
                    'category' => 'Refined Carbs',
                    'items' => ['White bread', 'Pastries', 'White rice'],
                    'reason' => 'Low nutritional value, causes blood sugar spikes',
                    'icon' => '🍞'
                ],
                [
                    'category' => 'Fried Foods',
                    'items' => ['Fast food', 'Deep-fried items', 'Chips'],
                    'reason' => 'High in unhealthy fats and calories',
                    'icon' => '🍟'
                ]
            ],
            'nutrition_tips' => [
                '💧 Stay Hydrated: Drink 8-10 glasses of water daily',
                '🍱 Meal Prep: Prepare meals in advance for busy days',
                '🍽️ Portion Control: Use smaller plates to manage portions',
                '🧘 Mindful Eating: Eat slowly and avoid distractions',
                '🏃 Regular Exercise: Combine with 150 minutes of moderate activity per week',
                '😴 Sleep Well: Aim for 7-9 hours of quality sleep',
                '📝 Track Progress: Keep a food journal to stay accountable',
                '🎯 Flexibility: Allow yourself occasional treats (80/20 rule)'
            ],
            'generated_at' => now()->format('F d, Y \a\t h:i A')
        ];
    }

    private function getBMICategory($bmi)
    {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal weight';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }
}
