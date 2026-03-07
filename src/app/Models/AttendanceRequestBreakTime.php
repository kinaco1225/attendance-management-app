<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'request_break_start',
        'request_break_end',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
