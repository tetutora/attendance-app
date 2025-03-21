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
            ['id' => 1, 'status' => 'in_office', 'description' => '出勤中'],
            ['id' => 2, 'status' => 'on_break', 'description' => '休憩中'],
            ['id' => 3, 'status' => 'clocked_out', 'description' => '退勤済'],
            ['id' => 4, 'status' => 'off_duty', 'description' => '勤務外'],
        ]);
    }
}
