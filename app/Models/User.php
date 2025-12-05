<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'password',
        'age',
        'birth_date',
        'height',
        'weight',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'age' => 'integer',
            'birth_date' => 'date',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    // Relationship: A user has many diet plans
    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }
}
