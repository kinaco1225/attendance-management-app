@extends('layouts.user-header')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-list-container">
  <h1 class="page-title">勤怠一覧</h1>
  <div class="month-switch">
    <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前日</a>
    <span class="date-with-icon">
      <img  src="{{ asset('images/カレンダー.png') }}" alt=""> {{ $currentMonth->format('Y/m') }}
    </span>
    <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
  </div>

  <table class="attendance-table">
    <thead>
      <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
      </tr>
    </thead>
    <tbody>
    @php
      $daysInMonth = $currentMonth->daysInMonth;
    @endphp

    @for ($day = 1; $day <= $daysInMonth; $day++)
      @php
        $date = $currentMonth->copy()->day($day);
        $attendance = $attendances->firstWhere('work_date', $date->toDateString());
        $isSaturday = $date->isSaturday();
        $isSunday = $date->isSunday();
      @endphp

      <tr class="{{ $isSaturday ? 'sat' : '' }} {{ $isSunday ? 'sun' : '' }}">
        <td class="date">
          {{ $date->format('m/d') }}（{{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }}）
        </td>

        <td>
          {{ optional($attendance)->clock_in
              ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
              : '' }}
        </td>

        <td>
          {{ optional($attendance)->clock_out
              ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
              : '' }}
        </td>

        <td>
          {{ optional($attendance)->totalBreakMinutes() > 0
              ? gmdate('H:i', $attendance->totalBreakMinutes() * 60)
              : '' }}
        </td>

        <td>
          @if ($attendance && $attendance->clock_in && !$attendance->clock_out)
            勤務中
          @elseif ($attendance && $attendance->workMinutes() !== null)
            {{ gmdate('H:i', $attendance->workMinutes() * 60) }}
          @endif
        </td>

        <td class="detail">
          <a href="{{ route('attendance.detail', $attendance->id) }}">
            詳細
          </a>
        </td>
      </tr>
    @endfor
    </tbody>

  </table>

</div>
@endsection
