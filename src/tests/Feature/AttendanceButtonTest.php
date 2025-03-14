<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Support\Facades\Auth;

class AttendanceButtonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外ステータス（off_duty）の場合、出勤ボタンが表示されることを確認する
     */
    public function test_attendance_button_off_duty()
    {
        $statusOffDuty = AttendanceStatus::create([
            'status' => 'off_duty',
            'description' => '勤務外',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusOffDuty->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertSee('出勤');
    }

    /**
     * 勤務中（in_office）の場合、出勤ボタンが表示されないことを確認する
     */
    public function test_attendance_button_is_not_clocked_in()
    {
        $statusInOffice = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '勤務中',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusInOffice->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /**
     * 勤務外（off_duty）ステータスから「勤務中」ステータスに変更する場合のテスト
     */
    public function test_attendance_button_changed_status_in_office()
    {
        $statusOffDuty = AttendanceStatus::create([
            'status' => 'off_duty',
            'description' => '勤務外',
        ]);

        $statusInOffice = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '勤務中',
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusOffDuty->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/attendance', [
            'attendance_date' => now()->toDateString(),
            'status' => 'in_office',
        ]);

        $attendance->refresh();

        $this->assertEquals($statusInOffice->id, $attendance->status_id);

        $response->assertRedirect('/attendance');
    }
}
