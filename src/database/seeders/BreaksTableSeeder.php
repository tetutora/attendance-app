<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BreakTime;


class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BreakTime::create([
            'attendance_id' => 1,
            'break_in' => '12:00',
            'break_out' => '13:00',
        ]);

        BreakTime::create([
            'attendance_id' => 2,
            'break_in' => '12:00',
            'break_out' => '12:30',
        ]);
    }
}
