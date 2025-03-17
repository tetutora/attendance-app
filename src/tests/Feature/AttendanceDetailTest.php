<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Database\Seeders\AttendanceStatusSeeder;
use App\Models\BreakTime;


class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendanceStatusSeeder::class);
    }

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_user_name_on_attendance_detail()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);
        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_user_attendance_date_on_attendance_detail()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);
        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->attendance_date);
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_correct_times_on_attendance_detail()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);
        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->clock_in);
        $response->assertSee($user->clock_out);
    }

    // 「休憩時間」にて記されている時間がログインユーザーの打刻と一致している
    public function test_correct_break_times_on_attendance_detail()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => now()->addHours(2)->format('H:i'),
            'break_out' => now()->addHours(3)->format('H:i'),
        ]);

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($break->break_in);
        $response->assertSee($break->break_out);
    }
}
