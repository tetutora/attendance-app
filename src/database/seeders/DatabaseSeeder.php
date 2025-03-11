<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Attendance;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            AttendanceStatusSeeder::class,
            ApprovalStatusSeeder::class,
            UsersTableSeeder::class,
            AttendancesTableSeeder::class,
            BreaksTableSeeder::class,

        ]);
    }
}
