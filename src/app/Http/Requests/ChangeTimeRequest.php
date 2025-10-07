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
            'user_id' => [
                'sometimes', // フィールドがあるときのみ
                'required',
                'exists:users,id',
            ],
            'requested_work_start' => [
                'nullable',
                'required_with:requested_work_end',
                'date_format:H:i',
                'before:requested_work_end',
            ],
            'requested_work_end' => [
                'nullable',
                'required_with:requested_work_start',
                'date_format:H:i',
                'after:requested_work_start',
            ],
            'breaks' =>[
                'nullable',
                'array',
                // 休憩時間の重複を避けるためのルール
                function($attribute, $value, $fail) {
                    $breakTimes = array_filter($value, fn($break) => !empty($break['start']) && !empty($break['end']));
                    if (count($breakTimes) <= 1) {
                        return; // 休憩が1つ以下なら重複しない
                    }
                    // 休憩時間を開始時刻の早い順に並べる
                    usort($breakTimes, fn($a, $b) => strcmp($a['start'], $b['start']));

                    for ($i = 0; $i < count($breakTimes) - 1; $i++) {
                        $current_break_end = $breakTimes[$i]['end'];
                        $next_break_start = $breakTimes[$i + 1]['start'];
                        if ($current_break_end > $next_break_start) {
                            $fail('休憩時間が重複しています');
                            return;
                        }
                    }
                },
            ],
            'breaks.*.start' => [
                'nullable',
                'required_with:breaks.*.end',
                'date_format:H:i',
                'before:requested_work_end',
                'after:requested_work_start',
            ],
            'breaks.*.end' => [
                'nullable',
                'required_with:breaks.*.start',
                'date_format:H:i',
                'before:requested_work_end',
                'after:breaks.*.start',
            ],
            'note' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages()
    {
        return [
            'requested_work_start.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_work_start.required_with' => '出勤時間も入力してください',
            'requested_work_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_work_end.required_with' => '退勤時間も入力してください',
            'breaks.*.start.required_with' =>'休憩開始時間も入力してください',
            'breaks.*.start.before' => '休憩時間が不適切な値です',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.end.required_with' => '休憩終了時間も入力してください',
            'breaks.*.end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
            'note.string' => '備考は文字列で入力してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }
}
