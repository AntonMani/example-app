<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DietPlanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Diet Plan routes
    Route::get('/diet-plan', [DietPlanController::class, 'index'])->name('diet-plan');
    Route::post('/diet-plan/generate', [DietPlanController::class, 'generate'])->name('diet-plan.generate');

    // Diet Plan routes
    Route::get('/diet-plan', [DietPlanController::class, 'index'])->name('diet-plan');
    Route::post('/diet-plan/generate', [DietPlanController::class, 'generate'])->name('diet-plan.generate');
    Route::get('/diet-plan/{id}', [DietPlanController::class, 'show'])->name('diet-plan.show');
    Route::delete('/diet-plan/{id}', [DietPlanController::class, 'destroy'])->name('diet-plan.destroy');
    Route::get('/my-diet-plans', [DietPlanController::class, 'myPlans'])->name('diet-plan.list');
});

require __DIR__.'/auth.php';
