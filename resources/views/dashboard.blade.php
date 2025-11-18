<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">
                        Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }} 👋
                    </h3>
                    <p class="text-gray-600 mb-4">
                        You're logged in! This is your dashboard.
                    </p>

                    @if(Auth::user()->birth_date || Auth::user()->height || Auth::user()->weight)
                        <div class="mt-6 border-t pt-4">
                            <h4 class="text-lg font-semibold mb-3">Your Profile</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @if(Auth::user()->birth_date)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Birth Date</p>
                                        <p class="text-lg font-medium">{{ Auth::user()->birth_date->format('F d, Y') }}</p>
                                        <p class="text-sm text-gray-500">Age: {{ \Carbon\Carbon::parse(Auth::user()->birth_date)->age }} years</p>
                                    </div>
                                @endif

                                @if(Auth::user()->height)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Height</p>
                                        <p class="text-lg font-medium">{{ Auth::user()->height }} cm</p>
                                    </div>
                                @endif

                                @if(Auth::user()->weight)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-600">Weight</p>
                                        <p class="text-lg font-medium">{{ Auth::user()->weight }} kg</p>
                                        @if(Auth::user()->height && Auth::user()->weight)
                                            @php
                                                $heightInMeters = Auth::user()->height / 100;
                                                $bmi = Auth::user()->weight / ($heightInMeters * $heightInMeters);
                                            @endphp
                                            <p class="text-sm text-gray-500">BMI: {{ number_format($bmi, 1) }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
