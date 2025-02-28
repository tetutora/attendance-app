<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id', 'break_start', 'break_end', 'break_time',
    ];
}
