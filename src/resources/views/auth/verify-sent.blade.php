@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
@endsection

@section('content')

<div class="email-verify-container">
  <div class="email-verify-message">
    <p class="email-verify-text">
      認証メールを送信しました
    </p>
    <p class="email-verify-text">
      メールに記載されたリンクをクリックして、<br>
      メール認証を完了してください
    </p>
  </div>

  <div class="email-verify-actions">
    <p class="email-verify-note">
      ※ 認証完了後、自動的にプロフィール設定画面へ遷移します
    </p>
  </div>
</div>

@endsection