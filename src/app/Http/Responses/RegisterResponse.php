<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
  public function toResponse($request)
  {
    dd('RegisterResponse called');

    $user = $request->user();

    if ($user && !$user->hasVerifiedEmail()) {
      return redirect()->route('verification.notice');
    }

    if ($user && !$user->is_profile_completed) {
      return redirect()->route('mypage.profile');
    }

    return redirect()->route('items.index');
  }
}
