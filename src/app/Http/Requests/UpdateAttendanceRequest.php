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
        'clock_in' => 'required|date_format:H:i',
        'clock_out' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
            $clockIn = $this->input('clock_in'); // 出勤時間
            if ($clockIn && $value) {
                $clockInTime = Carbon::parse($this->input('attendance_date') . ' ' . $clockIn);
                $clockOutTime = Carbon::parse($this->input('attendance_date') . ' ' . $value);

                // clock_outがclock_inより早い場合、clock_outの時間を翌日にする
                if ($clockOutTime->lessThan($clockInTime)) {
                    $clockOutTime->addDay();
                }
            }
        }],
        'break_in' => ['nullable', 'date_format:H:i', function ($attribute, $value, $fail) {
            $breakOut = $this->input('break_out'); // 休憩終了時間
            if ($value && $breakOut) {
                // 休憩開始時間と終了時間を結合してCarbonインスタンスに変換
                $breakInTime = Carbon::parse($this->input('attendance_date') . ' ' . $value);
                $breakOutTime = Carbon::parse($this->input('attendance_date') . ' ' . $breakOut);

                // 休憩終了時間が開始時間より前の場合、翌日を加算
                if ($breakOutTime->lessThan($breakInTime)) {
                    $breakOutTime->addDay();
                }
            }
        }],
        'break_out' => 'nullable|date_format:H:i',
        'remarks' => 'required|string|max:255',
    ];
}

public function messages()
{
    return [
        'attendance_date.required' => '日付は必須です。',
        'attendance_date.date' => '有効な日付を入力してください。',
        
        'clock_in.required' => '出勤時間は必須です。',
        'clock_in.date_format' => '出勤時間は「H:i」の形式で入力してください。',
        
        'clock_out.required' => '退勤時間は必須です。',
        'clock_out.date_format' => '退勤時間は「H:i」の形式で入力してください。',
        'clock_out.after' => '退勤時間は出勤時間の後に設定してください。',
        
        'break_in.date_format' => '休憩開始時間は「H:i」の形式で入力してください。',
        
        'break_out.date_format' => '休憩終了時間は「H:i」の形式で入力してください。',
        
        'remarks.required' => '備考は必須です。',
        'remarks.string' => '備考は文字列で入力してください。',
        'remarks.max' => '備考は255文字以内で入力してください。',
    ];
}


    // 勤務時間の計算
    

}
