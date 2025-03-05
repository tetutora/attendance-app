<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id', 'break_in', 'break_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

        public function getBreakTimeAttribute()
    {
        if ($this->break_in && $this->break_out) {
            $break_in_time = Carbon::parse($this->break_in);
            $break_out_time = Carbon::parse($this->break_out);

            if ($break_out_time->lessThan($break_in_time)) {
                $break_out_time->addDay();
            }

            return $break_in_time->diffInMinutes($break_out_time);
        }
        return 0;
    }
}
