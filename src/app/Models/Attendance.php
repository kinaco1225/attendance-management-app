<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'remark',
    ];

    // ユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 休憩（確定データ）
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    // 修正申請
    public function requests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }


    public function totalBreakMinutes(): int
    {
        return $this->breaks->sum(function ($break) {
            if (!$break->break_end) return 0;
            return Carbon::parse($break->break_start)
                ->diffInMinutes(Carbon::parse($break->break_end));
        });
    }
    

    public function workMinutes(): ?int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $total = Carbon::parse($this->clock_in)
            ->diffInMinutes(Carbon::parse($this->clock_out));

        $work = $total - $this->totalBreakMinutes();

        return max($work, 0); 
    }
    

}
