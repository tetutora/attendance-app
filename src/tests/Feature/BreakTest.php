<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    // 休憩ボタンが正しく機能する
    public function test_break_in_button_shown_for_in_office_status()
    {
        $user = User::factory()->create();

        $status = AttendanceStatus::firstOrCreate(
            ['status' => 'in_office'],
            ['description' => '出勤中']
        );

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中')
            ->assertSee('休憩入');
    }

    // 休憩入は一日に何回もできる
    public function test_break_in_button_shown_multiple_times()
    {
        $user = User::factory()->create();

        $status = AttendanceStatus::firstOrCreate(
            ['status' => 'in_office'],
            ['description' => '出勤中']
        );

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])
            ->assertSee('休憩入');

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])
            ->assertSee('休憩戻');

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])
            ->assertSee('休憩入');

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])
            ->assertSee('出勤戻');
    }

    // 休憩戻ボタンが正しく機能する
    public function test_break_out_button_functionality()
    {
        $user = User::factory()->create();

        $status = AttendanceStatus::firstOrCreate(
            ['status' => 'in_office'],
            ['description' => '出勤中']
        );

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])->assertSee('休憩中');

        $attendance->refresh();
        $this->assertEquals('出勤中', $attendance->status->description);

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])->assertSee('休憩戻');

        $attendance->refresh();
        $this->assertEquals('出勤中', $attendance->status->description);
    }

    // 休憩戻は一日に何回もできる
    public function test_break_out_button_shown_multiple_times()
    {
        $user = User::factory()->create();

        $status = AttendanceStatus::firstOrCreate(
            ['status' => 'in_office'],
            ['description' => '出勤中']
        );

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])
            ->assertSee('休憩中');

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])
            ->assertSee('出勤中');

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])
            ->assertSee('休憩中');

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])
            ->assertSee('出勤中');
    }

    // // 休憩時刻が管理画面で確認できる
    public function test_break_time_is_recorded_in_admin_panel()
    {
        $user = User::factory()->create();

        $status = AttendanceStatus::firstOrCreate(
            ['status' => 'in_office'],
            ['description' => '出勤中']
        );

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => Carbon::today(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break_in', ['attendance_id' => $attendance->id])
            ->assertSee('休憩中');

        $this->actingAs($user)
            ->post('/attendance/break_out', ['attendance_id' => $attendance->id])
            ->assertSee('出勤中');

        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($adminUser, 'admin')
            ->get('/admin/attendance/list')
            ->assertStatus(200);
    }
}
