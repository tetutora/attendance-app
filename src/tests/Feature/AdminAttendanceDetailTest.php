<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use App\Models\ApprovalStatus;
use App\Models\AttendanceApproval;
use Illuminate\Support\Facades\Auth;
use Database\Seeders\AttendanceStatusSeeder;
use Database\Seeders\ApprovalStatusSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\BreaksTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendancesTableSeeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_database_connection()
    {
        $dbName = DB::connection()->getDatabaseName();
        echo "Using database: " . $dbName;
        $this->assertEquals('demo_test', $dbName);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AttendanceStatusSeeder::class);
        $this->seed(ApprovalStatusSeeder::class);
        $this->seed(AdminUserSeeder::class);
        $this->seed(AttendancesTableSeeder::class);

        $this->seed(UsersTableSeeder::class);

    }

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_attendance_detail_displays_correct_information()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $status = AttendanceStatus::where('status', 'clocked_out')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->toDateTimeString(),
            'clock_out' => now()->addHours(8)->toDateTimeString(),
        ]);

        $clock_in = \Carbon\Carbon::parse($attendance->clock_in);
        $clock_out = \Carbon\Carbon::parse($attendance->clock_out);

        $this->actingAs($admin);

        $response = $this->get(route('general.attendance-detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $formattedDate = Carbon::parse($attendance->attendance_date)->format('Y年n月j日');

        $response->assertSee(Carbon::parse($attendance->attendance_date)->year . '年');
        $response->assertSee(Carbon::parse($attendance->attendance_date)->month . '月' . Carbon::parse($attendance->attendance_date)->day . '日');

        $response->assertSee($attendance->user->name);
        $response->assertSee($clock_in->format('H:i'));
        $response->assertSee($clock_out->format('H:i'));
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_after_clock_out_validation()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $pendingStatus = \App\Models\ApprovalStatus::where('status', 'pending')->first();

        $attendance = \App\Models\Attendance::create([
            'approval_status_id' => $pendingStatus->id,
            'attendance_date' => now()->toDateString(),
            'user_id' => User::factory()->create()->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_time' => 0,
            'work_time' => 480,
        ]);

        $attendanceApproval = \App\Models\AttendanceApproval::create([
            'approval_status_id' => $pendingStatus->id,
            'attendance_date' => now()->toDateString(),
            'user_id' => User::factory()->create()->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '10:00',
            'clock_out' => '09:00',
            'break_time' => 0,
            'work_time' => 480,
            'remarks' => '不正な勤務時間',
        ]);

        $response = $this->post(route('admin.attendance.approve', ['attendance_correct_request' => $attendanceApproval->id]), [
            'clock_in' => '10:00',
            'clock_out' => '09:00',
            'remarks' => '不正な勤務時間',
        ]);

        $response->assertSessionHasErrors('clock_out');
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_out_after_break_in_validation()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($admin);

        $pendingStatus = \App\Models\ApprovalStatus::where('status', 'pending')->first();

        $attendance = \App\Models\Attendance::create([
            'approval_status_id' => $pendingStatus->id,
            'attendance_date' => now()->toDateString(),
            'user_id' => User::factory()->create()->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_time' => 0,
            'work_time' => 480,
        ]);

        $attendanceApproval = \App\Models\AttendanceApproval::create([
            'approval_status_id' => $pendingStatus->id,
            'attendance_date' => now()->toDateString(),
            'user_id' => User::factory()->create()->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_time' => 0,
            'work_time' => 480,
            'remarks' => '不正な勤務時間',
        ]);

        $response = $this->post(route('admin.attendance.approve', ['attendance_correct_request' => $attendanceApproval->id]), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => ['break_in' => '18:00'],
            'remarks' => '不正な勤務時間',
        ]);
        $response->assertRedirect();
    }

    // 備考欄が未入力の場合エラーメッセージが表示される
    public function test_remarks_is_required()
    {
        $admin = User::factory()->create([
            'role' => 'admin'
        ]);
        $this->actingAs($admin);

        $user = User::factory()->create();
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $approvalStatus = ApprovalStatus::first(); 

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get(route('admin.attendance-detail', ['attendance' => $attendance->id]));
        $response->assertStatus(200);

        $data = [
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => null,
            'approval_status_id' => $approvalStatus->id,
        ];

        $attendanceApproval = \App\Models\AttendanceApproval::create([
            'approval_status_id' => $approvalStatus->id,
            'attendance_date' => now()->toDateString(),
            'user_id' => User::factory()->create()->id,
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_time' => 0,
            'work_time' => 480,
            'remarks' => '不正な勤務時間',
        ]);

        $response = $this->post(route('admin.attendance.approve', ['attendance_correct_request' => $attendanceApproval->id]), [
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => ['break_in' => '18:00'],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors('remarks');
    }
}
