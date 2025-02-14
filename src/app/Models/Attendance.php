<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'break_time',
        'total_work_time',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
