<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Support\Facades\Auth;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    // 勤務外の場合、勤怠ステータスが正しく表示されるか
    public function test_status_off_duty()
    {
        $status = AttendanceStatus::create([
            'status' => 'off_duty',
            'description' => '勤務外',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示されるか
    public function test_status_in_office()
    {
        $status = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '出勤中',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示されるか
    public function test_status_on_break()
    {
        $status = AttendanceStatus::create([
            'status' => 'on_break',
            'description' => '休憩中',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSeeText('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示されるか
    public function test_status_clocked_out()
    {
        $status = AttendanceStatus::create([
            'status' => 'clocked_out',
            'description' => '退勤済',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('退勤済');
    }
}
