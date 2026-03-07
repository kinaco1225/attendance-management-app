@extends('layouts.user-header')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="request-list-container">
  <h1 class="page-title">申請一覧</h1>
  <div class="request-tabs">
    <a href="{{ route('stamp_correction_requests.list', ['status' => 'pending']) }}"
      class="tab {{ $status === 'pending' ? 'active' : '' }}">
      承認待ち
    </a>

    <a href="{{ route('stamp_correction_requests.list', ['status' => 'approved']) }}"
      class="tab {{ $status === 'approved' ? 'active' : '' }}">
      承認済み
    </a>
  </div>

  <table class="request-table">
    <thead>
      <tr>
        <th>状態</th>
        <th>名前</th>
        <th>対象日時</th>
        <th>申請理由</th>
        <th>申請日時</th>
        <th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($requests as $request)
      <tr>
        {{-- 状態 --}}
        <td>
          @if ($request->status === 'pending')
            承認待ち
          @elseif ($request->status === 'approved')
            承認済み
          @elseif ($request->status === 'rejected')
            却下
          @endif
        </td>

        {{-- 名前 --}}
        <td>
          {{ $request->user->name ?? '' }}
        </td>

        {{-- 対象日時 --}}
        <td>
          {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}
        </td>

        {{-- 申請理由 --}}
        <td>
          {{ $request->request_remark }}
        </td>

        {{-- 申請日時 --}}
        <td>
          {{ $request->created_at->format('Y/m/d') }}
        </td>

        {{-- 詳細 --}}
        <td class="detail">
          <a href="{{ route('attendance.detail', $request->attendance->id) }}">
            詳細
          </a>
        </td>
      </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align:center;">
            申請はありません
          </td>
        </tr>
      @endforelse
      
    </tbody>

  </table>

</div>
@endsection