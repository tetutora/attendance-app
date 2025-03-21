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


class AdminApprovalTest extends TestCase
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

    // 承認待ちの修正申請が全て表示されているか
    public function test_admin_can_see_all_pending_approval_requests()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $attendanceApproval = AttendanceApproval::factory()->create([
            'user_id' => $user->id,
            'approval_status_id' => 1,
            'attendance_date' => Carbon::now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.correction-requests', ['approval_status_id' => 1]));

        $response->assertSee('承認待ち');

        $response->assertSee($attendanceApproval->user->name);
        $response->assertSee($attendanceApproval->attendance_date->format('Y/m/d'));
        $response->assertSee($attendanceApproval->remarks);

        $response->assertSee(route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $attendanceApproval->id]));

        $response->assertStatus(200);
    }

    // 承認済みの修正申請が全て表示されているか
    public function test_admin_can_see_all_approved_approval_requests()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'attendance_date' => Carbon::now()->format('Y-m-d'),
            'work_time' => 540,
        ]);

        $attendanceApproval = AttendanceApproval::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'approval_status_id' => 2,
            'attendance_date' => Carbon::now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.correction-requests', ['approval_status_id' => 2]));

        $response->assertSee('承認済み');
        $response->assertSee($attendanceApproval->user->name);
        $response->assertSee($attendanceApproval->attendance_date->format('Y/m/d'));
        $response->assertSee($attendanceApproval->remarks);

        $response->assertSee(route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $attendanceApproval->id]));

        $response->assertStatus(200);
    }

    // 修正申請の詳細内容が正しく表示されているか
    public function test_admin_can_see_correction_request_details()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create(['role' => 'user']);

        $attendanceDate = Carbon::now();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_date' => $attendanceDate,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendanceApproval = AttendanceApproval::factory()->create([
            'user_id' => $user->id,
            'approval_status_id' => 1,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendanceDate,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $attendanceApproval->id]));

        $response->assertSee($attendanceApproval->user->name);
        $formattedDate = \Carbon\Carbon::parse($attendanceApproval->attendance_date)->format('Y年');
        $formattedDate = \Carbon\Carbon::parse($attendanceApproval->attendance_date)->format('n月j日');
        $response->assertSee($formattedDate);
        $response->assertSee($attendanceApproval->remarks);

        $response->assertSee($attendance->clock_in);
        $response->assertSee($attendance->clock_out);

        $response->assertStatus(200);
    }

    // 修正申請の承認処理が正しく行われるか
    public function test_admin_can_approve_correction_request_and_update_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $pendingStatus = ApprovalStatus::create([
            'status' => 'pending',
            'description' => '承認待ち',
        ]);

        $approvedStatus = ApprovalStatus::create([
            'status' => 'approved',
            'description' => '承認済み',
        ]);

        $offDutyStatus = AttendanceStatus::create([
            'status' => 'off_duty',
            'description' => '勤務外',
        ]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'attendance_date' => now()->toDateString(),
            'status_id' => $offDutyStatus->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_time' => 60,
            'work_time' => 480,
        ]);

        $attendanceApproval = AttendanceApproval::create([
            'approval_status_id' => 2,
            'user_id' => $admin->id,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => '修正申請内容',
        ]);

        $approveRoute = route('admin.attendance.approve', $attendanceApproval->id);

        $this->actingAs($admin);

        $response = $this->post($approveRoute);

        $attendanceApproval->refresh();
        $this->assertEquals('approved', $attendanceApproval->approvalStatus->status);

        $response = $this->get(route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $attendanceApproval->id]));
        $response->assertSee($attendanceApproval->user->name);
        $formattedDate = \Carbon\Carbon::parse($attendanceApproval->attendance_date)->format('Y年');
        $formattedDate = \Carbon\Carbon::parse($attendanceApproval->attendance_date)->format('n月j日');
        $response->assertSee($formattedDate);
        $response->assertSee($attendanceApproval->remarks);

        $response->assertSee($attendance->clock_in);
        $response->assertSee($attendance->clock_out);

        $response->assertStatus(200);
    }
}
