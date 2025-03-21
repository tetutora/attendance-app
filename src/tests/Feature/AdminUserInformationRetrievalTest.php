<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\AttendanceStatus;
use Database\Seeders\AttendanceStatusSeeder;
use Database\Seeders\ApprovalStatusSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\BreaksTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendancesTableSeeder;
use function App\Helpers\formatMinutesToTimeString;


class AdminUserInformationRetrievalTest extends TestCase
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
        $this->seed(AdminUserSeeder::class);
        $this->seed(AttendancesTableSeeder::class);

        $this->seed(UsersTableSeeder::class);

    }

    // 管理者が全一般ユーザーの「氏名」「メールアドレス」を確認できるか
    public function test_admin_can_view_staff_list_with_name_and_email()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $staff = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff-list'));

        $response->assertStatus(200);

        $response->assertSee($staff->name);
        $response->assertSee($staff->email);
    }

    // ユーザーの勤怠情報が正しく表示されるか
    public function test_admin_can_view_user_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $status = AttendanceStatus::where('status', 'in_office')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => '2025-03-21',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 30,
            'work_time' => 480,
        ]);

        $breakTime = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00',
            'break_out' => '12:30',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff-attendance', ['id' => $user->id]));

        $response->assertStatus(200);

        $response->assertSee($attendance->clock_in);
        $response->assertSee($attendance->clock_out);
        $response->assertSee($attendance->break_time);
        $response->assertSee(convertToHoursMinutes($attendance->work_time));
    }

    // 「前月」を押下した際に表示月の前月の情報が表示されるか
    public function test_admin_can_view_previous_month_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => $previousMonth . '-15', 
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
            'work_time' => 480,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff-attendance', ['id' => $user->id]));

        $response->assertSee($currentMonth->format('Y-m'));

        $response = $this->get(route('admin.staff-attendance', ['id' => $user->id, 'month' => $previousMonth]));

        $response->assertSee($previousMonth);
        $response->assertSee(convertToHoursMinutes($attendance->work_time));
        $response->assertSee(convertToHoursMinutes($attendance->break_time));

        $response->assertStatus(200);
    }

    // 「翌月を押下した際に表示月の前月の情報が表示されるか
    public function test_admin_can_view_next_month_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 前月の日付を取得
        $currentMonth = Carbon::now()->startOfMonth();
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 前月の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => $nextMonth . '-15',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
            'work_time' => 480,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff-attendance', ['id' => $user->id]));

        $response->assertSee($currentMonth->format('Y-m'));

        $response = $this->get(route('admin.staff-attendance', ['id' => $user->id, 'month' => $nextMonth]));

        $response->assertSee($nextMonth);
        $response->assertSee(convertToHoursMinutes($attendance->work_time));
        $response->assertSee(convertToHoursMinutes($attendance->break_time));

        $response->assertStatus(200);
    }
    
    public function test_admin_can_navigate_to_attendance_detail_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
            'work_time' => 480,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance-list'));

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        Carbon::setLocale('ja');
        $formattedDate = Carbon::parse($attendance->attendance_date)->format('m/d (D)');

        $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
        $response->assertSee(Carbon::parse($attendance->clock_out)->format('H:i'));
    }
}
