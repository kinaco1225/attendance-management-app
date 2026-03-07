<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      return [
        'clok_in' => ['nullable', 'date_format:H:i'],
        'clok_out' => ['nullable', 'date_format:H:i'],

        'breaks.*.start' => ['nullable', 'date_format:H:i'],
        'breaks.*.end'   => ['nullable', 'date_format:H:i'],
        
        'remark' => ['required'],
      ];
    }

    public function messages()
    {
      return [
        'remark.required' => '備考を記入してください'
      ];
    }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {

      $clockIn  = $this->clock_in;
      $clockOut = $this->clock_out;

      if ($clockIn && $clockOut && $clockIn > $clockOut) {
        $validator->errors()->add(
          'clock_in',
          '出勤時間もしくは退勤時間が不適切な値です'
        );
      }

      if ($this->breaks) {

        foreach ($this->breaks as $index => $break) {

          $start = $break['start'] ?? null;
          $end   = $break['end'] ?? null;

          // 🔹 両方空白ならスキップ（空白行はOK）
          if (!$start && !$end) {
            continue;
          }

          // 🔹 片方だけ入力はエラー
          if (($start && !$end) || (!$start && $end)) {
            $validator->errors()->add(
              "breaks.$index.start",
              '休憩時間が不適切な値です'
            );
            continue;
          }

          // 🔹 出勤前 or 退勤後
          if ($clockIn && $start < $clockIn) {
            $validator->errors()->add(
              "breaks.$index.start",
              '休憩時間が不適切な値です'
            );
          }

          if ($clockOut && $start > $clockOut) {
            $validator->errors()->add(
              "breaks.$index.start",
              '休憩時間が不適切な値です'
            );
          }

          // 🔹 終了が退勤後
          if ($clockOut && $end > $clockOut) {
            $validator->errors()->add(
              "breaks.$index.end",
              '休憩時間もしくは退勤時間が不適切な値です'
            );
          }

          // 🔹 start > end
          if ($start > $end) {
            $validator->errors()->add(
              "breaks.$index.start",
              '休憩時間が不適切な値です'
            );
          }
        }
      }
    });
  } 
}
