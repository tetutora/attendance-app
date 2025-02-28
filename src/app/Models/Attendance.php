<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'status_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out',
        'break_time',
        'work_time',
    ];

    // ユーザーとの関連
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠承認との関連
    public function approval()
    {
        return $this->hasOne(AttendanceApproval::class);
    }

    // 勤怠承認との関連（別名）
    public function attendanceApproval(): HasOne
    {
        return $this->hasOne(AttendanceApproval::class, 'attendance_id');
    }

    // 承認済みの勤怠との関連
    public function attendanceApproved()
    {
        return $this->hasOne(AttendanceApproved::class);
    }

    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }

    // モデルの保存前に勤務時間を計算
    public static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clock_in_time = Carbon::parse($attendance->clock_in);
                $clock_out_time = Carbon::parse($attendance->clock_out);

                // 出勤と退勤が逆になっている場合を考慮
                if ($clock_in_time->gt($clock_out_time)) {
                    $attendance->work_time = 0;
                    return;
                }

                $work_duration = $clock_in_time->diffInMinutes($clock_out_time);

                if ($attendance->break_time) {
                    $work_duration -= $attendance->break_time;
                }

                $attendance->work_time = max($work_duration, 0); // 負の値にならないようにする
            } else {
                $attendance->work_time = 0;
            }
        });
    }
}
