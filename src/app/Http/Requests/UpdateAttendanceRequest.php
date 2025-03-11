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
            'attendance_date' => 'required|date',
            'clock_in' => [
                'required',
                'date_format:H:i'
            ],
            'clock_out' => [
                'required',
                'date_format:H:i',
                'after:clock_in',
                function ($attribute, $value, $fail) {
                    $clockIn = $this->input('clock_in');
                    if ($clockIn && $value) {
                        try {
                            $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
                            $clockOutTime = Carbon::createFromFormat('H:i', $value);

                            if ($clockOutTime <= $clockInTime) {
                                $fail('退勤時間は出勤時間より後である必要があります。');
                            }
                        } catch (\Exception $e) {
                            $fail('時間の形式が正しくありません。');
                        }
                    }
                }
            ],
            'break_in' => ['nullable', 'array'],
            'break_in.*' => ['nullable', 'date_format:H:i'],
            'break_out' => ['nullable', 'array'],
            'break_out.*' => ['nullable', 'date_format:H:i'],

            // 休憩時間が勤務時間外かどうかをチェック
            function ($attribute, $value, $fail) {
                $breakIn = $this->input('break_in', []);
                $breakOut = $this->input('break_out', []);
                $clockIn = $this->input('clock_in');
                $clockOut = $this->input('clock_out');

                try {
                    $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
                    $clockOutTime = Carbon::createFromFormat('H:i', $clockOut);

                    foreach ($breakIn as $index => $inTime) {
                        if (!isset($breakOut[$index])) {
                            continue;
                        }

                        $outTime = $breakOut[$index];
                        $breakInTime = Carbon::createFromFormat('H:i', $inTime);
                        $breakOutTime = Carbon::createFromFormat('H:i', $outTime);

                        // 休憩開始時間が出勤時間より前、または休憩終了時間が退勤時間より後であればエラー
                        if ($breakInTime->lt($clockInTime) || $breakOutTime->gt($clockOutTime)) {
                            $fail('休憩時間が勤務時間外です');
                        }

                        // 休憩終了時間が休憩開始時間より前の場合のエラー
                        if ($breakOutTime->lte($breakInTime)) {
                            $fail("休憩終了時間（{$outTime}）は休憩開始時間（{$inTime}）より後である必要があります。");
                        }
                    }
                } catch (\Exception $e) {
                    $fail("時間の形式が正しくありません。");
                }
            },

            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'attendance_date.required' => '日付は必須です。',
            'attendance_date.date' => '有効な日付を入力してください。',
            'clock_in.required' => '出勤時間は必須です。',
            'clock_in.date_format' => '出勤時間は「HH:mm」の形式で入力してください。',
            'clock_out.required' => '退勤時間は必須です。',
            'clock_out.date_format' => '退勤時間は「HH:mm」の形式で入力してください。',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_in.array' => '休憩開始時間は複数入力できますが、正しい形式で入力してください。',
            'break_in.*.date_format' => '休憩開始時間は「HH:mm」の形式で入力してください。',
            'break_out.array' => '休憩終了時間は複数入力できますが、正しい形式で入力してください。',
            'break_out.*.date_format' => '休憩終了時間は「HH:mm」の形式で入力してください。',
            'remarks.required' => '備考欄を記入してください。',
            'remarks.string' => '備考は文字列で入力してください。',
            'remarks.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
