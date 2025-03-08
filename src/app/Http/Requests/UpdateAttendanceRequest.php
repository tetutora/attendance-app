<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
                'after:clock_in', // 退勤時間は出勤時間より後である必要がある
                function ($attribute, $value, $fail) {
                    $clockIn = $this->input('clock_in');
                    if ($clockIn && $value) {
                        try {
                            $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
                            $clockOutTime = Carbon::createFromFormat('H:i', $value);

                            // 時間を2桁に整形
                            $clockInFormatted = $clockInTime->format('H:i');
                            $clockOutFormatted = $clockOutTime->format('H:i');

                            // 出勤時間と退勤時間を比較
                            if ($clockOutFormatted <= $clockInFormatted) {
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
            
            // break_in と break_out のペア数チェック
            function ($attribute, $value, $fail) {
                $breakIn = $this->input('break_in', []);
                $breakOut = $this->input('break_out', []);
                
                if (count($breakIn) !== count($breakOut)) {
                    $fail('休憩の開始時間と終了時間の数が一致していません。');
                }
            },

            // 各 break_in と break_out の時間チェック
            function ($attribute, $value, $fail) {
                $breakIn = $this->input('break_in', []);
                $breakOut = $this->input('break_out', []);

                foreach ($breakIn as $index => $inTime) {
                    if (!isset($breakOut[$index])) {
                        continue;
                    }

                    $outTime = $breakOut[$index];

                    try {
                        $inTimeObj = Carbon::createFromFormat('H:i', $inTime);
                        $outTimeObj = Carbon::createFromFormat('H:i', $outTime);

                        if ($outTimeObj->lessThanOrEqualTo($inTimeObj)) {
                            $fail("休憩終了時間（{$outTime}）は休憩開始時間（{$inTime}）より後である必要があります。");
                        }
                    } catch (\Exception $e) {
                        $fail("休憩時間のフォーマットが正しくありません。");
                    }
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
            'clock_out.after' => '退勤時間は出勤時間の後に設定してください。',

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
