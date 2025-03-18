<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Database\Seeders\AttendanceStatusSeeder;
use Illuminate\Support\Facades\Artisan;


class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendanceStatusSeeder::class);
    }

    // 自分の勤怠情報がすべて表示される
    public function test_user_can_view_own_attendance()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);
        $response = $this->actingAs($user)->get(route('general.attendance_list'));

        $response->assertStatus(200);

        Carbon::setLocale('ja');
        $formattedDate = Carbon::parse($attendance->attendance_date)->format('m/d (D)');

        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_user_current_month_on_attendance_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('general.attendance_list'));

        $currentMonth = Carbon::now()->format('Y-m');

        $response->assertSee($currentMonth);

        $response->assertStatus(200);
    }

    // 「前月」を押下した際に表示月の前月が表示される
    public function test_user_previous_month_on_attendance_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('general.attendance_list'));

        $currentMonth = Carbon::now()->format('Y-m');

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->get(route('general.attendance_list', ['month' => $previousMonth]));

        $response->assertStatus(200);
        $response->assertSee($previousMonth);
        $response->assertDontSee($currentMonth);
    }

    // 「翌月」を押下した際に表示月の前月が表示される
    public function test_user_next_month_on_attendance_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('general.attendance_list'));

        $currentMonth = Carbon::now()->format('Y-m');

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->get(route('general.attendance_list', ['month' => $nextMonth]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth);
        $response->assertDontSee($currentMonth);
    }

    // 「詳細」を押下するとその日の勤怠詳細画面に遷移する
    public function test_attendance_detail_page()
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

        $response = $this->get(route('general.attendance_list'));

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        Carbon::setLocale('ja');
        $formattedDate = Carbon::parse($attendance->attendance_date)->format('m/d (D)');

        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }
}
