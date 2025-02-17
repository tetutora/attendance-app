<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'break_time',
        'total_work_time',
        'remarks',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function approval()
    {
        return $this->hasOne(AttendanceApproval::class);
    }
}
