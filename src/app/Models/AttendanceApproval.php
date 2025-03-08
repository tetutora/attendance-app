<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceApproval extends Model
{
    protected $table = 'attendance_approvals';

    protected $fillable = [
        'user_id',
        'attendance_id',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approvedAttendance()
    {
        return $this->hasOne(AttendanceApproved::class, 'attendance_id', 'attendance_id');
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ApprovalStatus::class, 'approval_status_id');
    }
}
