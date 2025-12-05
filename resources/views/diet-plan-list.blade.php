<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Diet Plans') }}
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

            <!-- Plans Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Your Diet Plans</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                You have <strong>{{ $plansCount }}</strong> out of <strong>{{ $maxPlans }}</strong> diet plans.
                            </p>
                        </div>
                        <div>
                            @if($plansCount < $maxPlans)
                                <a href="{{ route('diet-plan') }}" style="background: #FFA500; color: white; padding: 10px 24px; font-size: 16px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s;" onmouseover="this.style.background='#FF8C00'" onmouseout="this.style.background='#FFA500'">
                                    + Generate New Plan
                                </a>
                            @else
                                <span class="bg-red-100 text-red-800 text-sm font-medium px-4 py-2 rounded">
                                    Limit Reached ({{ $maxPlans }}/{{ $maxPlans }})
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($plansCount >= $maxPlans)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <p class="text-sm text-yellow-700">
                                <strong>⚠️ Limit Reached:</strong> You've reached your limit of {{ $maxPlans }} diet plans. Delete a plan below to generate a new one.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Plans List -->
            @if($plans->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No diet plans yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by generating your first personalized diet plan.</p>
                        <div class="mt-6">
                            <a href="{{ route('diet-plan') }}" style="background: #FFA500; color: white; padding: 10px 24px; font-size: 16px; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s;" onmouseover="this.style.background='#FF8C00'" onmouseout="this.style.background='#FFA500'">
                                Generate Your First Plan
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6">
                    @foreach($plans as $plan)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-gray-200 hover:border-indigo-300 transition-colors">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <h4 class="text-xl font-bold text-gray-900">
                                                Diet Plan for {{ $plan->plan_data['user']['name'] }}
                                            </h4>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                @if($plan->bmi < 18.5) bg-blue-100 text-blue-800
                                                @elseif($plan->bmi < 25) bg-green-100 text-green-800
                                                @elseif($plan->bmi < 30) bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                BMI: {{ $plan->bmi }} - {{ $plan->bmi_category }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                            <div class="bg-gray-50 p-3 rounded">
                                                <p class="text-xs text-gray-600">Age</p>
                                                <p class="text-lg font-semibold text-gray-900">{{ $plan->plan_data['user']['age'] }} years</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <p class="text-xs text-gray-600">Height</p>
                                                <p class="text-lg font-semibold text-gray-900">{{ $plan->plan_data['user']['height'] }} cm</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <p class="text-xs text-gray-600">Weight</p>
                                                <p class="text-lg font-semibold text-gray-900">{{ $plan->plan_data['user']['weight'] }} kg</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded">
                                                <p class="text-xs text-gray-600">Daily Calories</p>
                                                <p class="text-lg font-semibold text-gray-900">{{ $plan->plan_data['daily_calories']['total'] }} kcal</p>
                                            </div>
                                        </div>

                                        <p class="text-sm text-gray-600 mb-3">
                                            {{ Str::limit($plan->plan_data['health_assessment']['summary'], 150) }}
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            <span>Generated: {{ $plan->created_at->format('F d, Y \a\t h:i A') }}</span>
                                            <span class="mx-2">•</span>
                                            <span>{{ $plan->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                    <a href="{{ route('diet-plan.show', $plan->id) }}" style="background: #4F46E5; color: white; padding: 8px 20px; font-size: 14px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; transition: background 0.3s;" onmouseover="this.style.background='#4338CA'" onmouseout="this.style.background='#4F46E5'">
                                        View Full Plan
                                    </a>

                                    <form method="POST" action="{{ route('diet-plan.destroy', $plan->id) }}" onsubmit="return confirm('Are you sure you want to delete this diet plan? This action cannot be undone.');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: #DC2626; color: white; padding: 8px 20px; font-size: 14px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='#B91C1C'" onmouseout="this.style.background='#DC2626'">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
