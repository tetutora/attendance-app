<?php

namespace Database\Factories;

use App\Models\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalStatusFactory extends Factory
{
    protected $model = ApprovalStatus::class;

    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['pending', 'approved']),
            'description' => $this->faker->sentence,
        ];
    }
}
