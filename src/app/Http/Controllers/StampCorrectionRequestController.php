<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;


class StampCorrectionRequestController extends Controller
{
  public function index(Request $request)
  {
    $status = $request->get('status', 'pending');

    $requests = AttendanceRequest::with(['attendance', 'user'])
      ->where('user_id', auth()->id())
      ->where('status', $status)
      ->latest()
      ->get();

    return view('user.stamp_correction_requests.list', compact('requests', 'status'));
  }

}
