@extends('layouts.app')

@section('title', 'ユーザーログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="auth-container">
  <h1 class="auth-title">ログイン</h1>

  <form method="POST" action="/login" class="auth-form">
    @csrf

    <input type="hidden" name="login_type" value="user">

    <div class="form-group">
      <label>メールアドレス</label>
      <input type="email" name="email" value="{{ old('email') }}">
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

    <button type="submit" class="btn-primary">ログインする</button>
  </form>

  <div class="auth-footer">
    <a href="/register">会員登録はこちら</a>
  </div>
</div>

@endsection