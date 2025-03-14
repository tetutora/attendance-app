<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    protected $fillable = ['status', 'description'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'status_id');
    }

    public function attendanceApprovals()
    {
        return $this->hasMany(AttendanceApproval::class, 'approval_status_id');
    }
}
