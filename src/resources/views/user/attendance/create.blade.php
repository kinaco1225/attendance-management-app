@extends('layouts.user-header')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/registration.css') }}?v={{ time() }}">
@endsection

@section('content')
<main class="attendance-container">

  <div class="attendance-card">

    {{-- 状態別表示 --}}

    {{-- 出勤前 --}}
    @if ($status === 'before_work')
      <div class="attendance-status">勤務外</div>

    {{-- 出勤中 --}}
    @elseif ($status === 'working')
      <div class="attendance-status">出勤中</div>

    {{--休憩中  --}}
    @elseif ($status === 'break')
      <div class="attendance-status">休憩中</div>

    {{-- 退勤 --}}
    @elseif ($status === 'finished')
      <div class="attendance-status">退勤済</div>
    @endif

    {{-- 日付 --}}
    <div class="attendance-date">
      {{ now()->locale('ja')->isoFormat('YYYY年M月D日（dd）') }}
    </div>

    {{-- 時刻（共通JSで更新） --}}
    <div class="attendance-time" id="current-time">
      {{ now()->format('H:i') }}
    </div>

    {{-- 状態別表示 --}}

    {{-- 出勤前 --}}
    @if ($status === 'before_work')
      <form method="POST" action="{{ route('attendance.clock_in') }}">
        @csrf
        <button type="submit" class="btn attendance-button">
          出勤
        </button>
      </form>
    
    {{-- 出勤中 --}}
    @elseif ($status === 'working')
      <div class="buttons">
        <form method="POST" action="{{ route('attendance.clock_out') }}">
          @csrf
          <button class="btn leaving-button">退勤</button>
        </form>

        <form method="POST" action="{{ route('attendance.break.start') }}">
          @csrf
          <button class="btn break-button">休憩入</button>
        </form>
      </div>

    {{-- 休憩中 --}}
    @elseif ($status === 'break')
      <form method="POST" action="{{ route('attendance.break.end') }}">
        @csrf
        <button class="btn break-button">休憩戻</button>
      </form>
    
    {{-- 退勤 --}}
    @elseif ($status === 'finished')
      <p class="attendance-finished-message">
      お疲れさまでした。
      </p>
    @endif

  </div>

</main>
@endsection