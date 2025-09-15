<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    public function rules(): array
    {
        return [
            'requested_work_start' => 'date_format:H:i|before:requested_work_end',
            'requested_work_end' => 'date_format:H:i|after:requested_work_start',
            'requested_break_start' => 'date_format:H:i|before:requested_work_end|after:requested_work_start',
            'requested_break_end' => 'date_format:H:i|before:requested_work_end|after:requested_break_start',
            'note' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'requested_work_start.date_format' => '時:分の形式で入力してください',
            'requested_work_start.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_work_end.date_format' => '時:分の形式で入力してください',
            'requested_work_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_break_start.before' => '休憩時間が不適切な値です',
            'requested_break_start.after' => '休憩時間が不適切な値です',
            'requested_break_end.date_format' => '時:分の形式で入力してください',
            'requested_break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'requested_break_end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.string' => '備考は文字列で入力してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
