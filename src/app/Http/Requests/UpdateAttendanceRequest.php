<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_in' => 'nullable|date_format:H:i|after_or_equal:clock_in|before:clock_out',  // ここを修正
            'break_out' => 'nullable|date_format:H:i|after:break_in|before:clock_out',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間は必須です。',
            'clock_in.date_format' => '出勤時間は「HH:mm」の形式で入力してください。',
            'clock_out.required' => '退勤時間は必須です。',
            'clock_out.date_format' => '退勤時間は「HH:mm」の形式で入力してください。',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_in.array' => '休憩開始時間は複数入力できますが、正しい形式で入力してください。',
            'break_in.*.date_format' => '休憩開始時間は「HH:mm」の形式で入力してください。',
            'break_in.*' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_out.array' => '休憩終了時間は複数入力できますが、正しい形式で入力してください。',
            'break_out.*.date_format' => '休憩終了時間は「HH:mm」の形式で入力してください。',
            'break_out.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'remarks.required' => '備考を記入してください。',
            'remarks.string' => '備考は文字列で入力してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }


        private function validateClockInOutTimes($fail)
    {
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');
        
        if ($clockIn && $clockOut) {
            $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
            $clockOutTime = Carbon::createFromFormat('H:i', $clockOut);
            
            if ($clockOutTime <= $clockInTime) {
                $fail('出勤時間もしくは退勤時間が不適切な値です。');
                return;
            }
        }
        $this->validateBreakTimes($fail, $clockInTime, $clockOutTime);
    }

    private function validateBreakTimes($fail, $clockInTime, $clockOutTime)
    {
        $breakIn = $this->input('break_in', []);
        $breakOut = $this->input('break_out', []);

        $breakIn = is_array($breakIn) ? array_filter($breakIn) : [];
        $breakOut = is_array($breakOut) ? array_filter($breakOut) : [];

        foreach ($breakIn as $index => $inTime) {
            if (!isset($breakOut[$index])) continue;

            $breakInTime = Carbon::createFromFormat('H:i', $inTime);
            $breakOutTime = Carbon::createFromFormat('H:i', $breakOut[$index]);

            // 休憩時間が不適切な場合、出勤時間や退勤時間に関するエラーメッセージを表示
            if ($breakInTime->gt($clockOutTime)) {
                // 休憩開始時間が退勤時間より後の場合、出勤時間もしくは退勤時間が不適切な値ですというエラーメッセージを返す
                $fail('出勤時間もしくは退勤時間が不適切な値です。');
                return;
            }

            if ($breakInTime->lt($clockInTime) || $breakOutTime->gt($clockOutTime)) {
                $fail('出勤時間もしくは退勤時間が不適切な値です。');
                return;
            }

            if ($breakOutTime->lte($breakInTime)) {
                $fail("休憩終了時間（{$breakOutTime->format('H:i')}）は休憩開始時間（{$breakInTime->format('H:i')}）より後である必要があります。");
                return;
            }
        }
    }
}
