<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = BreakTime::class;
    
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_in' => $this->faker->time(),
            'break_out' => $this->faker->time(),
        ];
    }
}
