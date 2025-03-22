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
            'break_in' => 'nullable|array',
            'break_out' => 'nullable|array',
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
            'break_in.array' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_out.array' => '出勤時間もしくは退勤時間が不適切な値です。',
            'remarks.required' => '備考を記入してください。',
            'remarks.string' => '備考は文字列で入力してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBreakTimes($validator);
            $this->validateClockTimes($validator);
        });
    }

    public function validateClockTimes($validator)
    {
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');

        if (!$clockIn || !$clockOut) {
            return;
        }

        try {
            $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
            $clockOutTime = Carbon::createFromFormat('H:i', $clockOut);

            // 退勤時間が出勤時間より前の場合、エラーを追加
            if ($clockOutTime->lt($clockInTime)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です。');
            }
        } catch (\Exception $e) {
            $validator->errors()->add('clock_out', '無効な時間形式です。');
        }
    }
        
    public function validateBreakTimes($validator)
    {
        $clockInTime = Carbon::createFromFormat('H:i', $this->input('clock_in'));
        $clockOutTime = Carbon::createFromFormat('H:i', $this->input('clock_out'));

        $breakIn = $this->input('break_in', []);
        $breakOut = $this->input('break_out', []);

        // 休憩開始と終了の時間が不正な場合のバリデーション
        if (is_array($breakIn) && count($breakIn) !== count($breakOut)) {
            $validator->errors()->add('break_in', '出勤時間もしくは退勤時間が不適切な値です。');
            return;
        }

        foreach ($breakIn as $index => $inTime) {
            if (empty($inTime) || empty($breakOut[$index])) {
                continue;
            }

            try {
                $breakInTime = Carbon::createFromFormat('H:i', $inTime);
                $breakOutTime = Carbon::createFromFormat('H:i', $breakOut[$index]);

                if ($breakInTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_in.{$index}", '休憩開始時間は退勤時間より後には設定できません。');
                }

                if ($breakInTime->lt($clockInTime) || $breakInTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_in.{$index}", '出勤時間もしくは退勤時間が不適切な値です。');
                }

                if ($breakOutTime->lt($clockInTime) || $breakOutTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_out.{$index}", '出勤時間もしくは退勤時間が不適切な値です。');
                }

                if ($breakOutTime->lte($breakInTime)) {
                    $validator->errors()->add("break_out.{$index}", "休憩終了時間（{$breakOutTime->format('H:i')}）は休憩開始時間（{$breakInTime->format('H:i')}）より後である必要があります。");
                }
            } catch (\Exception $e) {
                $validator->errors()->add("break_in.{$index}", '無効な時間形式です。');
            }
        }
    }

}
