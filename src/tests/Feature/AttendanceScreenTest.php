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
    public function test_current_datetime()
    {
        $this->withoutMiddleware();

        // 現在の日時を取得
        $now = Carbon::now('Asia/Tokyo');

        // 曜日を日本語に変換
        $weekdays = [
            "Sunday" => "(日)",
            "Monday" => "(月)",
            "Tuesday" => "(火)",
            "Wednesday" => "(水)",
            "Thursday" => "(木)",
            "Friday" => "(金)",
            "Saturday" => "(土)"
        ];

        // フォーマットした日付
        $formattedDate = $now->format('Y年m月d日') . ' ' . $weekdays[$now->format('l')];

        // ページをリクエスト
        $response = $this->get('/attendance');

        // レスポンスにフォーマットされた日付が含まれているか確認
        $response->assertSee('2025年03月12日 (水)');
    }

}
