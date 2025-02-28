<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attendance_statuses')->insert([
            ['status' => 'in_office', 'description' => '出勤中'],
            ['status' => 'on_break', 'description' => '休憩中'],
            ['status' => 'clocked_out', 'description' => '退勤済'],
            ['status' => 'off_duty', 'description' => '勤務外'],
        ]);
    }
}
