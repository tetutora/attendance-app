<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceScreenTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    // 現在の日時情報がUIと同じ形式で出力されるか
    public function test_current_datetime()
    {
        $this->withoutMiddleware();

        $now = Carbon::now('Asia/Tokyo');

        $weekdays = [
            "Sunday" => "(日)",
            "Monday" => "(月)",
            "Tuesday" => "(火)",
            "Wednesday" => "(水)",
            "Thursday" => "(木)",
            "Friday" => "(金)",
            "Saturday" => "(土)"
        ];

        $formattedDate = $now->format('Y年m月d日') . ' ' . $weekdays[$now->format('l')];

        $response = $this->get('/attendance');

        $response->assertSee('2025年03月12日 (水)');
    }

}
