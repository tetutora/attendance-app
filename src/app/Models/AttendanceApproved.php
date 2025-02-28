<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceApproved extends Model
{
    protected $table = 'attendance_approved';

    protected $fillable = [
        'attendance_id',
        'user_id',
        'approval_status_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out',
        'break_time',
        'work_time',
        'remarks',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }

    public function attendanceApproval()
    {
        return $this->belongsTo(AttendanceApproval::class, 'attendance_id', 'attendance_id');
    }
}
