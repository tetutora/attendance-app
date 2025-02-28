<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStatus extends Model
{
    protected $table = 'approval_statuses';
    protected $fillable = ['name'];

    public function attendanceApprovals()
    {
        return $this->hasMany(AttendanceApproval::class);
    }

    public function attendanceApproved()
    {
        return $this->hasMany(AttendanceApproved::class);
    }
}
