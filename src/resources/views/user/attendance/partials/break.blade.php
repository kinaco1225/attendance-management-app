<div class="attendance-status">休憩中</div>

<form method="POST" action="{{ route('attendance.break.end') }}">
  @csrf
  <button class="btn break-button">休憩戻</button>
</form>
