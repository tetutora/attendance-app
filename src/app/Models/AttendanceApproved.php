<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceApproved extends Model
{
    protected $table = 'attendance_approved';

    protected $fillable = [
        'attendance_id',
        'user_id',
        'status',
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
}
