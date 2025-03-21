<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceApproval;

class AttendanceApprovalSeeder extends Seeder
{
    public function run()
    {
        AttendanceApproval::factory()->count(10)->create();
    }
}
