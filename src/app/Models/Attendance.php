<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'status_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'break_time',
        'work_time',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }


    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceApprovals()
    {
        return $this->hasMany(AttendanceApproval::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if (!empty($attendance->clock_in) && !empty($attendance->clock_out)) {
                $clock_in_time = Carbon::parse($attendance->clock_in);
                $clock_out_time = Carbon::parse($attendance->clock_out);

                if ($clock_in_time->gt($clock_out_time)) {
                    $clock_out_time->addDay();
                }

                $work_duration = $clock_in_time->diffInMinutes($clock_out_time);

                $total_break_time = $attendance->breaks()->get()->sum(function ($break) {
                    if (!empty($break->break_in) && !empty($break->break_out)) {
                        return Carbon::parse($break->break_in)->diffInMinutes(Carbon::parse($break->break_out));
                    }
                    return 0;
                });

                $attendance->work_time = max($work_duration - $total_break_time, 0);
                $attendance->break_time = $total_break_time;
            } else {
                $attendance->work_time = 0;
                $attendance->break_time = 0;
            }
        });
    }
}
