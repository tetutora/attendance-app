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
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'break_start' => 'nullable|date_format:H:i|before:clock_out',
            'break_end' => 'nullable|date_format:H:i|before:clock_out',
            'remarks' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_start.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'remarks.required' => '備考を記入してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breakStart = $this->input('break_start');
            $breakEnd = $this->input('break_end');

            if ($clockIn && $clockOut && Carbon::parse($clockIn)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            if ($breakStart && $clockOut && Carbon::parse($breakStart)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('break_start', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            // 休憩終了時間が退勤時間より後の場合
            if ($breakEnd && $clockOut && Carbon::parse($breakEnd)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('break_end', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            // 備考欄が空の場合
            if (!$this->input('remarks')) {
                $validator->errors()->add('remarks', '備考を記入してください。');
            }
        });
    }

}
