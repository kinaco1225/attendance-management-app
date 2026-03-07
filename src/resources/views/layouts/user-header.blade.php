<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title', 'ユーザー')</title>
  <link rel="stylesheet" href="{{asset('css/sanitize.css')}}?v={{ time() }}">
  <link rel="stylesheet" href="{{asset('css/header.css')}}?v={{ time() }}">
  @yield('css')  
</head>
  
<body>

<header class="site-header">
  <div class="header-inner">

    <div class="header-left">
      <a href="">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="header-logo">
      </a>
    </div>

    <div class="header-right">
      <a class="header-link" href="{{ route('attendance') }}">勤怠</a>
      <a class="header-link" href="{{ route('attendance.list') }}">勤怠一覧</a>
      <a class="header-link" href="{{ route('stamp_correction_requests.list') }}">申請</a>
      <form  action="{{ route('logout') }}" method="POST" class="inline-form logout-form">
          @csrf
        <input type="hidden" name="logout_type" value="user">
        <button type="submit" class="header-link">ログアウト</button>
      </form>
    </div>

  </div>

</header>

<main>
  @yield('content')
  @yield('script')
</main>

{{-- 共通JS --}}
<script>
  function updateTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    const timeEl = document.getElementById('current-time');
    if (timeEl) {
      timeEl.textContent = `${hours}:${minutes}`;
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateTime();
    setInterval(updateTime, 1000);
  });
</script>

@yield('js')

</body>
</html>


