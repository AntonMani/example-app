<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('password');
            $table->decimal('height', 5, 2)->nullable()->after('birth_date')->comment('Height in cm');
            $table->decimal('weight', 5, 2)->nullable()->after('height')->comment('Weight in kg');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'height', 'weight']);
        });
    }
};
