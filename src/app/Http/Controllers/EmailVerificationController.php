<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
  public function notice()
  {
    return view('auth.verify-email');
  }

  public function send(Request $request)
  {
    $request->user()->sendEmailVerificationNotification();

    return view('auth.verify-sent');
  }

  public function verify(EmailVerificationRequest $request)
  {
    $request->fulfill();

    return redirect()
      ->route('attendance')
      ->with('success', 'メール認証が完了しました');
  }

  public function resend(Request $request)
  {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', '確認メールを再送しました');
  }

  public function sent()
  {
    return view('auth.verify-sent');
  }
}
