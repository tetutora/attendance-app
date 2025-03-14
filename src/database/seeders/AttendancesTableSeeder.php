<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        Attendance::create([
            'user_id' => 2,
            'attendance_date' => Carbon::parse('2025-03-01'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
        ]);

        Attendance::create([
            'user_id' => 3,
            'attendance_date' => Carbon::parse('2025-03-01'),
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'break_time' => 30,
        ]);
    }
}
