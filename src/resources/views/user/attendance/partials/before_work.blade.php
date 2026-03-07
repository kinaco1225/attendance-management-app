<div class="attendance-status">勤務外</div>

<form method="POST" action="{{ route('attendance.clock_in') }}">
  @csrf
  <button type="submit" class="btn attendance-button">
    出勤
  </button>
</form>
