<div class="attendance-status">出勤中</div>

<div class="buttons">
    <form method="POST" action="{{ route('attendance.clock_out') }}">
        @csrf
        <button class="btn leaving-button">退勤</button>
    </form>

    <form method="POST" action="{{ route('attendance.break.start') }}">
        @csrf
        <button class="btn break-button">休憩入</button>
    </form>
</div>
