<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('approval_statuses')->insert([
            ['status' => 'pending', 'description' => '承認待ち'],
            ['status' => 'approved', 'description' => '承認済み'],
        ]);
    }
}
