<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceApproval extends Model
{
    protected $table = 'attendance_approvals';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'remarks',
        'requested_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
