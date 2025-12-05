<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bmi',
        'bmi_category',
        'plan_data',
    ];

    protected $casts = [
        'plan_data' => 'array', // Automatically cast JSON to array
        'bmi' => 'decimal:2',
    ];

    // Relationship: A diet plan belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
