<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBreakTimes($validator);
        });
    }
    
    public function validateBreakTimes($validator)
    {
        $clockInTime = Carbon::createFromFormat('H:i', $this->input('clock_in'));
        $clockOutTime = Carbon::createFromFormat('H:i', $this->input('clock_out'));

        $breakIn = $this->input('break_in', []);
        $breakOut = $this->input('break_out', []);

        // 休憩開始と終了の時間が不正な場合のバリデーション
        if (count($breakIn) !== count($breakOut)) {
            $validator->errors()->add('break_in', '休憩開始時間と終了時間のペアが正しくありません。');
            return;
        }

        foreach ($breakIn as $index => $inTime) {
            if (empty($inTime) || empty($breakOut[$index])) {
                continue;
            }

            try {
                $breakInTime = Carbon::createFromFormat('H:i', $inTime);
                $breakOutTime = Carbon::createFromFormat('H:i', $breakOut[$index]);

                // break_inがclock_outより後の時間かチェック
                if ($breakInTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_in.{$index}", '休憩開始時間は退勤時間より後には設定できません。');
                }

                // 休憩時間が出勤時間と退勤時間内に収まっているか
                if ($breakInTime->lt($clockInTime) || $breakInTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_in.{$index}", '出勤時間もしくは退勤時間が不適切な値です。');
                }

                if ($breakOutTime->lt($clockInTime) || $breakOutTime->gt($clockOutTime)) {
                    $validator->errors()->add("break_out.{$index}", '出勤時間もしくは退勤時間が不適切な値です。');
                }

                // 休憩終了時間が開始時間より前でないかチェック
                if ($breakOutTime->lte($breakInTime)) {
                    $validator->errors()->add("break_out.{$index}", "休憩終了時間（{$breakOutTime->format('H:i')}）は休憩開始時間（{$breakInTime->format('H:i')}）より後である必要があります。");
                }
            } catch (\Exception $e) {
                $validator->errors()->add("break_in.{$index}", '無効な時間形式です。');
            }
        }
    }
}
