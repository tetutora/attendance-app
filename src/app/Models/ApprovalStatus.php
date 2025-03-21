<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalStatus extends Model
{
    use HasFactory;

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
