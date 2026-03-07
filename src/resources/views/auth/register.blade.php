@extends('layouts.app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="auth-container">
  <h1 class="auth-title">会員登録</h1>

  <form method="POST" action="/register" class="auth-form">
    @csrf

    <div class="form-group">
      <label>ユーザー名</label>
      <input type="text" name="name" value="{{ old('name') }}">
      @error('name')
        <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label>メールアドレス</label>
      <input type="text" name="email" value="{{ old('email') }}">
      @error('email')
        <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label>パスワード</label>
      <input type="password" name="password">
      @error('password')
        <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label>確認用パスワード</label>
      <input type="password" name="password_confirmation">
      @error('password_confirmation')
        <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn-primary">登録する</button>
  </form>

  <div class="auth-footer">
    <a href="/login">ログインはこちら</a>
  </div>
</div>

@endsection