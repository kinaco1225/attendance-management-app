<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
  public function toResponse($request)
  {
    $user = $request->user();

    // メール認証が有効な場合：未認証なら認証誘導へ
    if ($user && method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
      return redirect()->route('verification.notice');
    }

    // 初回ログイン（プロフィール未設定）ならプロフィール設定へ
    if ($user && !$user->is_profile_completed) {
      return redirect()->route('profile.edit');
    }

    // 通常はトップへ
    return redirect()->route('items.index');
  }
}
