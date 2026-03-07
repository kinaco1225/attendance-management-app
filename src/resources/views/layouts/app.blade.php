<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title', '勤怠管理')</title>
  <link rel="stylesheet" href="{{asset('css/sanitize.css')}}?v={{ time() }}">
  <link rel="stylesheet" href="{{asset('css/app.css')}}?v={{ time() }}">
  @yield('css')
</head>
<body>
  <header class="header">
    <div class="header-inner">
      <div class="header-logo">
        @php
          $disableLogoLink = Route::is(
            'verification.notice',
            'verification.send.view',
            'verification.sent'
          );
        @endphp

        @if ($disableLogoLink)
          <img
            src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}"
            alt="COACHTECH"
            class="header-logo"
          >
        @else
          <a href="">
            <img
              src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}"
              alt="COACHTECH"
              class="header-logo"
            >
          </a>
        @endif
      </div>
    </div>
  </header>
  <main>
    @yield('content')
  </main>
</body>
</html>