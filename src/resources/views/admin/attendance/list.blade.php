@extends('layouts.admin-header')

@section('title', '勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-list-container">
  <h1 class="page-title">
    {{ $currentDate->format('Y年n月j日') }}の勤怠
  </h1>

  <div class="day-switch">
    <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
      ← 前日
    </a>
    
    <span class="date-with-icon">
      <img  src="{{ asset('images/カレンダー.png') }}" alt=""> 
      {{ $currentDate->format('Y/m/d') }}
    </span>

    <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">
      翌日 →
    </a>
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
    @foreach ($users as $user)
    @php
        $attendance = $attendances[$user->id] ?? null;
    @endphp

    <tr>
      <td>{{ $user->name }}</td>
      <td>
  {{ $attendance?->clock_in
      ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
      : '' }}
</td>

<td>
  {{ $attendance?->clock_out
      ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
      : '' }}
</td>
      <td>
        {{ $attendance && $attendance->totalBreakMinutes() > 0
            ? gmdate('H:i', $attendance->totalBreakMinutes() * 60)
            : '' }}
      </td>
      <td>
        {{ $attendance && $attendance->workMinutes() !== null
            ? gmdate('H:i', $attendance->workMinutes() * 60)
            : '' }}
      </td>
      <td class="detail">
        <a href="{{ route('admin.attendance.detail', [
            'attendance' => $attendance?->id,
            'user_id' => $user->id,
            'date' => $currentDate->toDateString(),]) }}">
          詳細
        </a>
      </td>
    </tr>
    @endforeach
    </tbody>

  </table>

</div>
@endsection