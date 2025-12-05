<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Diet Plan Generator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Plans Limit Info -->
            @if(isset($plansCount))
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-700">
                                <strong>Diet Plans:</strong> You have generated {{ $plansCount }} out of {{ $maxPlans }} allowed plans.
                            </p>
                        </div>
                        @if($hasReachedLimit)
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Limit Reached</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- User Profile Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Your Profile</h3>

                    @php
                        $age = $user->age ?? ($user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->age : null);
                        $hasCompleteProfile = $age && $user->height && $user->weight;
                    @endphp

                    @if($hasCompleteProfile)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Age</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $age }} years</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Height</p>
                                <p class="text-2xl font-bold text-green-600">{{ $user->height }} cm</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Weight</p>
                                <p class="text-2xl font-bold text-purple-600">{{ $user->weight }} kg</p>
                            </div>
                        </div>

                        @if(!isset($showPlan) && !$hasReachedLimit)
                            <form method="POST" action="{{ route('diet-plan.generate') }}">
                                @csrf
                                <button type="submit" style="background: #FFA500; color: white; padding: 10px 40px; font-size: 18px; font-weight: bold; border: none; border-radius: 10px; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#FF8C00'" onmouseout="this.style.background='#FFA500'">
                                    Generate My Personalized Diet Plan
                                </button>
                            </form>
                        @elseif($hasReachedLimit && !isset($showPlan))
                            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-red-700">
                                            <strong>⚠️ Limit Reached!</strong> You've generated the maximum number of diet plans.
                                        </p>
                                        <p class="text-xs text-red-600 mt-1">
                                            Go to "My Plans" to view and delete existing plans.
                                        </p>
                                    </div>
                                    <a href="{{ route('diet-plan.list') }}" style="background: #4F46E5; color: white; padding: 8px 20px; font-size: 14px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#4338CA'" onmouseout="this.style.background='#4F46E5'">
                                        View My Plans
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Please complete your profile first!
                                        <a href="{{ route('profile.edit') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                            Go to Profile
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if(isset($dietPlanData))
                <!-- Health Assessment -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-900">Your Health Assessment</h3>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                                @if($bmi < 18.5) bg-blue-100 text-blue-800
                                @elseif($bmi < 25) bg-green-100 text-green-800
                                @elseif($bmi < 30) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                BMI: {{ $dietPlanData['user']['bmi'] }} - {{ $dietPlanData['user']['bmi_category'] }}
                            </span>
                        </div>
                        <p class="text-gray-700 mb-4">{{ $dietPlanData['health_assessment']['summary'] }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($dietPlanData['health_assessment']['recommendations'] as $recommendation)
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">{{ $recommendation }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Daily Calories -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Daily Calorie Target</h3>
                        <div class="text-center mb-6">
                            <p class="text-5xl font-bold text-indigo-600">{{ $dietPlanData['daily_calories']['total'] }}</p>
                            <p class="text-gray-600">calories per day</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($dietPlanData['daily_calories']['breakdown'] as $meal => $data)
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg text-center">
                                    <p class="text-sm text-gray-600 capitalize">{{ $meal }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $data['calories'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $data['percentage'] }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Macronutrients -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Macronutrient Breakdown</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($dietPlanData['macronutrients'] as $name => $data)
                                <div class="bg-gradient-to-br
                                    @if($name === 'protein') from-red-50 to-red-100
                                    @elseif($name === 'carbohydrates') from-yellow-50 to-yellow-100
                                    @else from-blue-50 to-blue-100
                                    @endif p-6 rounded-lg">
                                    <h4 class="text-lg font-semibold capitalize
                                        @if($name === 'protein') text-red-700
                                        @elseif($name === 'carbohydrates') text-yellow-700
                                        @else text-blue-700
                                        @endif mb-2">{{ $name }}</h4>
                                    <p class="text-3xl font-bold
                                        @if($name === 'protein') text-red-600
                                        @elseif($name === 'carbohydrates') text-yellow-600
                                        @else text-blue-600
                                        @endif">{{ $data['grams'] }}g</p>
                                    <p class="text-sm
                                        @if($name === 'protein') text-red-600
                                        @elseif($name === 'carbohydrates') text-yellow-600
                                        @else text-blue-600
                                        @endif mb-2">{{ $data['percentage'] }}% of calories</p>
                                    <p class="text-sm text-gray-600">{{ $data['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 7-Day Meal Plan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">7-Day Meal Plan</h3>
                        <div class="space-y-6">
                            @foreach($dietPlanData['meal_plan'] as $day)
                                <div class="border-2 border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-3">
                                        <h4 class="text-xl font-bold text-white">{{ $day['day'] }}</h4>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                                        @foreach(['breakfast', 'lunch', 'dinner', 'snacks'] as $mealType)
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h5 class="font-semibold text-gray-900 capitalize">{{ $mealType }}</h5>
                                                    <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                                        {{ $day['meals'][$mealType]['calories'] }} cal
                                                    </span>
                                                </div>
                                                <ul class="space-y-1">
                                                    @foreach($day['meals'][$mealType]['items'] as $item)
                                                        <li class="text-sm text-gray-700 flex items-start">
                                                            <span class="text-indigo-500 mr-1">•</span>
                                                            <span>{{ $item }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Foods to Prioritize & Limit -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Prioritize -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-green-700 mb-4">✅ Foods to Prioritize</h3>
                            <div class="space-y-4">
                                @foreach($dietPlanData['foods_to_prioritize'] as $food)
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <h4 class="font-semibold text-green-800 mb-2">
                                            <span class="text-2xl mr-2">{{ $food['icon'] }}</span>
                                            {{ $food['category'] }}
                                        </h4>
                                        <p class="text-sm text-green-700">
                                            {{ implode(', ', $food['items']) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Limit -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-red-700 mb-4">⚠️ Foods to Limit</h3>
                            <div class="space-y-4">
                                @foreach($dietPlanData['foods_to_limit'] as $food)
                                    <div class="bg-red-50 p-4 rounded-lg">
                                        <h4 class="font-semibold text-red-800 mb-2">
                                            <span class="text-2xl mr-2">{{ $food['icon'] }}</span>
                                            {{ $food['category'] }}
                                        </h4>
                                        <p class="text-sm text-red-700 mb-1">
                                            {{ implode(', ', $food['items']) }}
                                        </p>
                                        <p class="text-xs text-red-600 italic">{{ $food['reason'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nutrition Tips -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">💡 Nutrition Tips for Success</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($dietPlanData['nutrition_tips'] as $tip)
                                <div class="flex items-start bg-white p-3 rounded-lg shadow-sm">
                                    <span class="text-gray-700">{{ $tip }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(isset($showPlan) && isset($planId))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 flex justify-between items-center flex-wrap gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Generated at {{ $dietPlanData['generated_at'] }}</p>
                            </div>
                            <div class="flex gap-4">
                                @if(!$hasReachedLimit)
                                    <form method="POST" action="{{ route('diet-plan.generate') }}">
                                        @csrf
                                        <button type="submit" style="background: #FFA500; color: white; padding: 8px 32px; font-size: 16px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#FF8C00'" onmouseout="this.style.background='#FFA500'">
                                            Generate New Plan
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('diet-plan.destroy', $planId) }}" onsubmit="return confirm('Are you sure you want to delete this diet plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #DC2626; color: white; padding: 8px 32px; font-size: 16px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'">
                                        Delete Plan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Disclaimer -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Disclaimer:</strong> This is a general diet plan generated for educational purposes. Individual needs may vary based on activity level, medical conditions, and personal preferences. Always consult with a healthcare professional or registered dietitian before making significant dietary changes.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- How it Works (shown when no plan generated yet) -->
            @if(!isset($showPlan) && $hasCompleteProfile)
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">💡 How it works</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Our AI analyzes your age, height, and weight</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Calculates your BMI and health status</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Generates a personalized 7-day meal plan</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Provides nutrition tips tailored to your profile</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
