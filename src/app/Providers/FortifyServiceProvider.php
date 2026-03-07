<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
  
    $this->app->singleton(LoginResponse::class, function () {
      return new class implements LoginResponse {
        public function toResponse($request)
        {
          $user = auth()->user();

          if ($user->role === 'admin') {
            return redirect('/admin/attendance/list');
          }

          return redirect('/attendance');
        }
      };
    });

    $this->app->singleton(
      CreatesNewUsers::class,
      CreateNewUser::class
    );

    $this->app->singleton(
      \Laravel\Fortify\Http\Requests\LoginRequest::class,
      LoginRequest::class
    );

    $this->app->singleton(RegisterResponse::class, function () {
      return new class implements RegisterResponse {
        public function toResponse($request)
        {
          // トップページへ
          return redirect('/');
        }
      };
    });

    $this->app->singleton(LogoutResponse::class, function () {
      return new class implements LogoutResponse {
        public function toResponse($request)
        {
          if ($request->logout_type === 'admin') {
            return redirect('/admin/login');
          }

          return redirect('/login');
        }
      };
    });

  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    /**
     * ログイン画面
     */
    Fortify::loginView(function (Request $request) {

      if ($request->is('admin/login')) {
        return view('admin.login');
      }

      return view('auth.login');
    });

    /**
     * 登録画面
     */
    Fortify::registerView(fn() => view('auth.register'));

    /**
     * 🔐 認証処理
     */
    Fortify::authenticateUsing(function (Request $request) {

      $user = User::where('email', $request->email)->first();

      if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
          'email' => ['ログイン情報が登録されていません'],
        ]);
      }

      if ($request->login_type === 'admin') {
        if ($user->role !== 'admin') {
          throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
          ]);
        }
      }

      if ($request->login_type === 'user') {
        if ($user->role !== 'user') {
          throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
          ]);
        }
      }

      return $user;
    });

    /**
     * 🚦 ログイン試行回数制限（回数を増やす）
     * 1分間に50回まで（email + IP）
     */
    RateLimiter::for('login', function (Request $request) {
      return Limit::perMinute(50)
        ->by($request->email . $request->ip());
    });
  }
  
}
