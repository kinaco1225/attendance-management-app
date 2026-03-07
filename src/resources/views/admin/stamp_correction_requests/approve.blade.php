@extends('layouts.admin-header')

@section('title', '修正申請承認画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">

  <h1 class="page-title">勤怠詳細</h1>

  <form method="POST" action="{{ route('admin.stamp_correction_request.update', $attendance_correct_request->id) }}">
  @csrf
  @method('PUT')
    
    <table class="detail-table">
      <tr>
        <th>名前</th>
        <td>
          <span class="name-text">
            {{ $user->name }}
          </span>
        </td>
      </tr>

      <tr>
        <th>日付</th>
        <td>
          <div class="date-flex">
            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
          </div>
        </td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td>

          @if (in_array($attendance_correct_request->status, ['pending', 'approved']))
            
            <span class="time-text-left">
              {{ $clockIn
                  ? \Carbon\Carbon::parse($clockIn)->format('H:i')
                  : '' }}
            </span>

            <span class="text-span-from">～</span>

            <span class="time-text">
              {{ $clockOut
                  ? \Carbon\Carbon::parse($clockOut)->format('H:i')
                  : '' }}
            </span>

          @else
            
            <input type="time"
              name="clock_in"
              value="{{ old('clock_in',
                  $clockIn ? \Carbon\Carbon::parse($clockIn)->format('H:i') : '') }}"
              step="60">

            <span class="span-from">～</span>

            <input type="time"
              name="clock_out"
              value="{{ old('clock_out',
                  $clockOut ? \Carbon\Carbon::parse($clockOut)->format('H:i') : '') }}"
              step="60">
          @endif

        </td>
      </tr>

      <tbody id="break-body">
      @foreach ($breaks as $i => $break)
      <tr>
        <th>休憩{{ $i + 1 }}</th>
        <td>

          @if (in_array($attendance_correct_request->status, ['pending', 'approved']))
            
            <span class="time-text-left">
              {{ $break->request_break_start
                  ? \Carbon\Carbon::parse($break->request_break_start)->format('H:i')
                  : '' }}
            </span>

            <span class="text-span-from">～</span>

            <span class="time-text">
              {{ $break->request_break_end
                  ? \Carbon\Carbon::parse($break->request_break_end)->format('H:i')
                  : '' }}
            </span>

          @else
            
            <input type="time"
              name="breaks[{{ $i }}][start]"
              value="{{ old("breaks.$i.start",
                  $break->request_break_start
                      ? \Carbon\Carbon::parse($break->request_break_start)->format('H:i')
                      : ''
              ) }}"
              step="60">

            <span class="span-from">～</span>

            <input type="time"
              name="breaks[{{ $i }}][end]"
              value="{{ old("breaks.$i.end",
                  $break->request_break_end
                      ? \Carbon\Carbon::parse($break->request_break_end)->format('H:i')
                      : ''
              ) }}"
              step="60">
          @endif

        </td>
      </tr>
      @endforeach
      </tbody>
      
      <tr>
        <th>備考</th>
        <td>
          @if (in_array($attendance_correct_request->status, ['pending', 'approved']))
            <p class="remark-text">
              {{ $remark }}
            </p>
          @else
            <textarea name="remark" rows="3">
              {{ old('remark', $remark) }}
            </textarea>
          @endif
        </td>
      </tr>
    </table>

    <div class="button-area">
      @if ($attendance_correct_request->status === 'approved')
        <button type="button" class="btn-gray" disabled>
          承認済み
        </button>
      @else
        <button type="submit" class="btn-black">
          承認
        </button>
      @endif
    </div>

  </form>
</div>
@endsection