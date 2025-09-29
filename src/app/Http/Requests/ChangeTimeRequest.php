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
            'user_id' => 'sometimes|required|exists:users,id', // フィールドがあるときのみ
            'requested_work_start' => 'nullable|date_format:H:i|before:requested_work_end',
            'requested_work_end' => 'nullable|date_format:H:i|after:requested_work_start',
            'breaks.*.start' => 'nullable|date_format:H:i|before:requested_work_end|after:requested_work_start',
            'breaks.*.end' => 'nullable|date_format:H:i|before:requested_work_end|after:breaks.*.start',
            'note' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'requested_work_start.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_work_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.before' => '休憩時間が不適切な値です',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.string' => '備考は文字列で入力してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
