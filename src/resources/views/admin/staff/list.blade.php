@extends('layouts.admin-header')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-list-container">
  <h1 class="page-title">スタッフ一覧</h1>

  <table class="attendance-table">
    <thead>
      <tr>
        <th>名前</th>
        <th>メールアドレス</th>
        <th>月次勤怠</th>
      </tr>
    </thead>
    <tbody>

    @forelse ($staffs as $staff)
      <tr>
        <td>{{ $staff->name }}</td>
        <td>{{ $staff->email }}</td>
        <td class="detail">
          <a href="{{ route('admin.attendance.staff', $staff->id) }}"
             class="link-detail">
            詳細
          </a>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="3" class="empty-text">
          スタッフが存在しません
        </td>
      </tr>
    @endforelse
    </tbody>

  </table>

</div>
@endsection