<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('bmi', 5, 2);
            $table->string('bmi_category');
            $table->json('plan_data'); // Store the entire diet plan as JSON
            $table->timestamps();

            // Index for faster queries
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
