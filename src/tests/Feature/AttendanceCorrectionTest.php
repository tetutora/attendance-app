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


class AttendanceCorrectionTest extends TestCase
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

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_after_clock_out_error()
    {
        $user = User::factory()->create();
        $status = AttendanceStatus::where('status', 'in_office')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => '2025-03-17'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $data = [
            'attendance_date' => '2025-03-17',
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'remarks' => 'テスト備考'
        ];

        $response = $this->post(route('general.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors('clock_out');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です。', session('errors')->get('clock_out')[0]);
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_after_clock_out_error()
    {
        $user = User::factory()->create();
        $status = AttendanceStatus::where('status', 'in_office')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $data = [
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_in' => ['19:00'],
            // 'break_out' => ['19:00'],
            'remarks' => 'テスト備考'
        ];

        $response = $this->post(route('general.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors('break_in');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です。', session('errors')->first());
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_after_clock_out_error()
    {
        $user = User::factory()->create();
        $status = AttendanceStatus::where('status', 'in_office')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $data = [
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_in' => ['17:00'],
            'break_out' => ['19:00'],
            'remarks' => 'テスト備考'
        ];

        $response = $this->post(route('general.attendance.update', ['id' => $attendance->id]), $data);

        $errors = session('errors')->get('break_out');
        $this->assertIsArray($errors);
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です。', session('errors')->first());
    }

    // 備考欄が未記入の場合のエラーメッセージが表示される
    public function test_remarks_is_required()
    {
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

        $this->actingAs($user);

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $data = [
            'attendance_date' => '2025-03-17',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => null,
            'approval_status_id' => $approvalStatus->id,
        ];

        $response = $this->post(route('general.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors('remarks');
        $this->assertEquals('備考を記入してください。', session('errors')->get('remarks')[0]);
    }

    // 修正申請処理が実行されるか
    public function test_correction_request_in_adminPanel()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $approvalStatus = \App\Models\ApprovalStatus::where('status', 'pending')->first();
        $status = AttendanceStatus::where('status', 'clocked_out')->first();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'work_time' => 540,
            'break_time' => 0,
        ]);

        $attendanceApproval = AttendanceApproval::create([
            'approval_status_id' => $approvalStatus->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => '修正後の備考'
        ]);

        $userName = $user->name;

        $response = $this->get(route('admin.correction-requests', ['approval_status_id' => 2]));
        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }


    // 「承認待ち」にログインユーザーが行なった申請が全て表示されているか
    public function test_user_can_see_all_their_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $status = AttendanceStatus::where('status', 'clocked_out')->first();
        $approvalStatus = ApprovalStatus::where('status', 'pending')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'attendance_date' => now()->toDateString(),
        ]);

        AttendanceApproval::create([
            'approval_status_id' => $approvalStatus->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => '修正後の備考'
        ]);

        $response = $this->get(route('general.correction-requests'));

        $formattedDate = \Carbon\Carbon::parse($attendance->attendance_date)->format('Y/m/d');

        $response->assertStatus(200);
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されているか
    public function test_approved_attendances_are_displayed()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $user = User::factory()->create();

        $status = AttendanceStatus::where('status', 'clocked_out')->first();
        $approvalStatus = ApprovalStatus::where('status', 'pending')->first();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'attendance_date' => now()->toDateString(),
        ]);

        $attendanceApproval = AttendanceApproval::create([
            'approval_status_id' => $approvalStatus->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => '修正後の備考'
        ]);

        $approvedStatus = ApprovalStatus::where('status', 'approved')->first();
        $attendanceApproval->update([
            'approval_status_id' => $approvedStatus->id,
            'remarks' => '修正後の備考',
        ]);

        // dd(AttendanceApproval::all());

        $this->actingAs($admin);

        $response = $this->get(route('admin.correction-requests'));

        $formattedDate = \Carbon\Carbon::parse($attendance->attendance_date)->format('Y/m/d');

        $response->assertStatus(200);
    }

    // 「詳細」を押下すると申請詳細画面に遷移する
    public function test_attendance_detail_page()
    {
        $status = AttendanceStatus::where('status', 'in_office')->first();
        $approvalStatus = ApprovalStatus::first();

        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status_id' => $status->id,
            'attendance_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        AttendanceApproval::create([
            'approval_status_id' => $approvalStatus->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => '修正後の備考'
        ]);

        $response = $this->get(route('general.correction-requests'));

        $response = $this->get(route('general.attendance_detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        Carbon::setLocale('ja');
        $formattedDate = Carbon::parse($attendance->attendance_date)->format('m/d (D)');

        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }
}
