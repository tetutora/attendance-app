<?php

namespace Database\Factories;

use App\Models\AttendanceApproval;
use App\Models\ApprovalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceApprovalFactory extends Factory
{
    protected $model = AttendanceApproval::class;

    public function definition()
    {
        return [
            'approval_status_id' => ApprovalStatus::factory(),
            'user_id' => User::factory(),
            'attendance_id' => 1,
            'attendance_date' => $this->faker->date(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 0,
            'work_time' => 540,
            'remarks' => '',
        ];
    }
}
