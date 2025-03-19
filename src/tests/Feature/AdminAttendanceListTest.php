<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Database\Seeders\AttendanceStatusSeeder;
use Database\Seeders\ApprovalStatusSeeder;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendanceStatusSeeder::class);
        $this->seed(ApprovalStatusSeeder::class);
    }

    // 当日の全ユーザの勤怠情報が確認できるか
    public function test_admin_attendance_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $status = AttendanceStatus::where('status', 'clocked_out')->first();

        $attendance1 = Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status_id' => $status->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
            'work_time' => 480,
            'attendance_date' => now()->toDateString(),
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status_id' => $status->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_time' => 45,
            'work_time' => 495,
            'attendance_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $response->assertStatus(200);

        $response->assertSee($attendance1->user->name);
        $response->assertSee($attendance2->user->name);

        $response->assertSee($attendance1->clock_in);
        $response->assertSee($attendance1->clock_out);
        $response->assertSee(sprintf('%02d:%02d', floor($attendance1->break_time / 60), $attendance1->break_time % 60));
        $response->assertSee(sprintf('%02d:%02d', floor($attendance1->work_time / 60), $attendance1->work_time % 60));

        $response->assertSee($attendance2->clock_in);
        $response->assertSee($attendance2->clock_out);
        $response->assertSee(sprintf('%02d:%02d', floor($attendance2->break_time / 60), $attendance2->break_time % 60));
        $response->assertSee(sprintf('%02d:%02d', floor($attendance2->work_time / 60), $attendance2->work_time % 60));
    }

    // 遷移した際に現在の日付が表示されるか
    public function test_admin_attendance_list_shows_current_date()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $currentData = now()->format('Y-m-d');

        $response->assertStatus(200);
        $response->assertSee($currentData);
    }

    // 「前日」を押下した際に前の日の勤怠情報が表示されるか
    public function test_admin_attendance_list_previous_day()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $previousDay = now()->subDay()->toDateString();

        $status = AttendanceStatus::where('status', 'clocked_out')->first();

        $attendancePreviousDay = Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status_id' => $status->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_time' => 60,
            'work_time' => 480,
            'attendance_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $response->assertStatus(200);

        $response->assertSee($attendancePreviousDay->user->name);
        $response->assertSee($attendancePreviousDay->clock_in);
        $response->assertSee($attendancePreviousDay->clock_out);
        $response->assertSee(sprintf('%02d:%02d', floor($attendancePreviousDay->break_time / 60), $attendancePreviousDay->break_time % 60));
        $response->assertSee(sprintf('%02d:%02d', floor($attendancePreviousDay->work_time / 60), $attendancePreviousDay->work_time % 60));
    }

    // 「翌日」を押下した際に次の日の勤怠が表示される
    public function test_admin_attendance_list_next_day()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $nextDay = now()->addDay()->toDateString();

        $status = AttendanceStatus::where('status', 'clocked_out')->first();

        $attendanceNextDay = Attendance::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status_id' => $status->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'break_time' => 60,
            'work_time' => 480,
            'attendance_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $response->assertStatus(200);

        $response->assertSee($attendanceNextDay->user->name);
        $response->assertSee($attendanceNextDay->clock_in);
        $response->assertSee($attendanceNextDay->clock_out);
        $response->assertSee(sprintf('%02d:%02d', floor($attendanceNextDay->break_time / 60), $attendanceNextDay->break_time % 60));
        $response->assertSee(sprintf('%02d:%02d', floor($attendanceNextDay->work_time / 60), $attendanceNextDay->work_time % 60));
    }
}
