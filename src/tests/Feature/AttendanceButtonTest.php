<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Support\Facades\Auth;

class AttendanceButtonTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_attendance_button_off_duty()
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

        $response->assertSee('出勤');
    }

    public function test_attendance_button_is_not_clocked_in()
    {
        $status = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '勤務中',
        ]);

        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->toTimeString(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function test_attendance_button_changed_status_in_office()
    {
        // 勤務外ステータスを作成
        $statusOffDuty = AttendanceStatus::create([
            'status' => 'off_duty',
            'description' => '勤務外'
        ]);

        // 勤務中ステータスを作成
        $statusInOffice = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '勤務中'
        ]);

        // ユーザーを作成
        $user = User::factory()->create();

        // 勤務外ステータスで出勤情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusOffDuty->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $this->actingAs($user);

        // 出勤ボタンを押して、勤務状態を「勤務中」に変更
        $response = $this->post('/attendance', [
            'attendance_date' => now()->toDateString(),
        ]);

        // 出勤情報を再取得してステータスが変更されたことを確認
        $attendance->refresh();

        // dd($attendance);

        // ステータスが「勤務中」に変更されていることを確認
        $this->assertEquals($statusInOffice->id, $attendance->status_id);

        // 画面が出勤画面にリダイレクトされることを確認
        $response->assertRedirect('/attendance');
    }
}
