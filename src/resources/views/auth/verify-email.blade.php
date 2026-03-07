@extends('layouts.app')

@section('title', 'メール承認誘導')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
@endsection

@section('content')

<div class="email-verify-container">
  <div class="email-verify-message">
    <p class="email-verify-text">登録していただいたメールアドレスに確認メールを送付しました</p>
    <p class="email-verify-text">メール承認を完了してください</p>
  </div>

  <div class="email-verify-actions">
    
    <form class="email-verify-form">
      <button
        type="button"
        class="email-verify-button"
        onclick="location.href='{{ url('/email/verify/send') }}'"
      >
        承認はここから
      </button>
    </form>

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="email-verify-resend">承認メールを再送する</button>
    </form>
  </div>
</div>

@endsection