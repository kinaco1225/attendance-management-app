<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'request_clock_in',
        'request_clock_out',
        'request_remark',
        'status',
    ];

    /* ========= リレーション ========= */

    // 元の勤怠
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 申請者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 修正申請の休憩
    public function requestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreakTime::class);
    }
}
