<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalDetailRequest extends FormRequest
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

    private function validateBreakTimes($fail, $clockInTime, $clockOutTime)
    {
        $breakIn = $this->input('break_in', []);
        $breakOut = $this->input('break_out', []);

        // 休憩なしの場合、break_in と break_out が空であれば問題なし
        if (empty($breakIn) && empty($breakOut)) {
            return;
        }

        // 休憩がある場合、配列が一致しているか確認
        if (count($breakIn) !== count($breakOut)) {
            $fail('休憩開始時間は複数入力できますが、正しい形式で入力してください。');
            return;
        }

        foreach ($breakIn as $index => $inTime) {
            $breakInTime = Carbon::createFromFormat('H:i', $inTime);
            $breakOutTime = Carbon::createFromFormat('H:i', $breakOut[$index]);

            if ($breakInTime->gt($clockOutTime)) {
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
