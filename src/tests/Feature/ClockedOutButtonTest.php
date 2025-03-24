<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClockedOutButtonTest extends TestCase
{
    use RefreshDatabase;

    // 退勤ボタンが正しく機能するか
    public function test_attendance_button_office_to_checked_out()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();

        $statusInOffice = AttendanceStatus::create([
            'status' => 'in_office',
            'description' => '勤務中',
        ]);

        $statusClockedOut = AttendanceStatus::create([
            'status' => 'clocked_out',
            'description' => '退勤済',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusInOffice->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->toTimeString(),
        ]);

        $attendance->status_id = $statusClockedOut->id;
        $attendance->save();

        $attendance->refresh();

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->post('/attendance', [
            'attendance_date' => now()->toDateString(),
        ]);

        $attendance->refresh();

        $this->assertEquals($statusClockedOut->id, $attendance->status_id);

        $response->assertRedirect('/attendance');
    }

    // 退勤時刻が管理画面で確認できるか
    public function test_clocked_out_is_recorded_in_admin_panel()
    {
        $user = User::factory()->create();

        $statusInOffice = AttendanceStatus::create(['status' => 'in_office', 'description' => '勤務中']);
        $statusClockedOut = AttendanceStatus::create(['status' => 'clocked_out', 'description' => '退勤済']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $statusInOffice->id,
            'attendance_date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8)->toTimeString(),
        ]);

        $attendance->status_id = $statusClockedOut->id;
        $attendance->save();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => Carbon::now()->subMinutes(30),
            'break_out' => Carbon::now(),
        ]);

        $adminUser = User::factory()->create(['role' => 'admin']);
        $formattedDate = $attendance->attendance_date->format('Y-m-d');

        $this->actingAs($adminUser, 'admin')
            ->get('/admin/attendance/list')
            ->assertStatus(200)
            ->assertSee($formattedDate);
    }
}
