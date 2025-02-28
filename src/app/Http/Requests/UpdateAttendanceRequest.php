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
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'break_in' => 'nullable|date_format:H:i|before:break_out',
            'break_out' => 'nullable|date_format:H:i|after:break_in',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'attendance_date.required' => '日付は必須です',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_in.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_out.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'remarks.required' => '備考を記入してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breakIn = $this->input('break_in');
            $breakOut = $this->input('break_out');

            if ($clockIn && $clockOut && Carbon::parse($clockIn)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            if ($breakIn && $clockOut && Carbon::parse($breakIn)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('break_in', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            if ($breakOut && $clockOut && Carbon::parse($breakOut)->greaterThan(Carbon::parse($clockOut))) {
                $validator->errors()->add('break_out', '出勤時間もしくは退勤時間が不適切な値です。');
            }
        });
    }

}
