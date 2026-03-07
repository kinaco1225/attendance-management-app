@extends('layouts.admin-header')

@section('title', '勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-detail">

  <h1 class="page-title">勤怠詳細</h1>

  @if($attendance)
  <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
    @method('PUT')
  @else
  <form method="POST" action="{{ route('admin.attendance.store') }}">
  @endif
    @csrf
    
    <input type="hidden" name="user_id" value="{{ $user->id }}">
    <input type="hidden" name="date" value="{{ $workDate }}">
    <input type="hidden" name="from" value="{{ request('from') }}">
    <input type="hidden" name="month" value="{{ request('month') }}">
    <input type="hidden" name="date" value="{{ request('date') }}">
    
    <table class="detail-table">
      <tr>
        <th>名前</th>
        <td>
          <span class="name-text">
            {{ $user?->name }}
          </span>
        </td>
      </tr>

      <tr>
        <th>日付</th>
        <td>
          <div class="date-flex">
            <span>{{ \Carbon\Carbon::parse($workDate)->format('Y年') }}</span>
            <span>{{ \Carbon\Carbon::parse($workDate)->format('n月j日') }}</span>
          </div>
        </td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td>
          @if ($isPending)
          {{-- 申請データ --}}
            <span class="time-text-left">
              {{ $clockIn
                  ? \Carbon\Carbon::parse($clockIn)->format('H:i')
                  : '' }}
            </span>

            <span class="text-span-from">～</span>

            <span class="time-text">
              {{ $clockOut
                  ? \Carbon\Carbon::parse($clockOut)->format('H:i')
                  : '' }}
            </span>

          @else
          {{-- 通常データ --}}
            <input type="time"
            name="clock_in"
            value="{{ old('clock_in',$clockIn? \Carbon\Carbon::parse($clockIn)->format('H:i'): '') }}"
            step="60">

          <span class="span-from">～</span>
          
          <input type="time"
            name="clock_out"
            value="{{ old('clock_out',$clockOut? \Carbon\Carbon::parse($clockOut)->format('H:i'): '') }}"
            step="60">

          @endif

          @error('clock_in')
            <div class="error-text">
              {{ $message }}
            </div>
          @enderror

        </td>
      </tr>

      <tbody id="break-body">

      @php
        $oldBreaks = old('breaks');
        $displayBreaks = $oldBreaks ?? $breaks;
      @endphp

      {{-- 既存休憩 --}}
      @foreach ($displayBreaks as $i => $break)
      <tr>
        <th>休憩{{ $i + 1 }}</th>
        <td>
          @if($isPending)
            {{-- 申請データ --}}
            <span class="time-text-left">
              {{ $break->request_break_start
                  ? \Carbon\Carbon::parse($break->request_break_start)->format('H:i')
                  : '' }}
            </span>

            <span class="text-span-from">～</span>

            <span class="time-text">
              {{ $break->request_break_end
                  ? \Carbon\Carbon::parse($break->request_break_end)->format('H:i')
                  : '' }}
            </span>

          @else
            
            <input type="hidden"
              name="breaks[{{ $i }}][id]"
              value="{{ is_array($break) ? ($break['id'] ?? '') : ($break->id ?? '') }}">

            {{-- start --}}
            <input type="time"
              name="breaks[{{ $i }}][start]"
              value="{{ old("breaks.$i.start",
                  is_array($break)
                    ? ($break['start'] ?? '')
                    : ($break->break_start
                        ? \Carbon\Carbon::parse($break->break_start)->format('H:i')
                        : '')
              ) }}"
              step="60">

            <span class="span-from">～</span>

            {{-- end --}}
            <input type="time"
              name="breaks[{{ $i }}][end]"
              value="{{ old("breaks.$i.end",
                  is_array($break)
                    ? ($break['end'] ?? '')
                    : ($break->break_end
                        ? \Carbon\Carbon::parse($break->break_end)->format('H:i')
                        : '')
              ) }}"
              step="60">
          @endif

          @error("breaks.$i.start")
            <div class="error-text">{{ $message }}</div>
          @enderror

          @error("breaks.$i.end")
            <div class="error-text">{{ $message }}</div>
          @enderror
        </td>
      </tr>
      @endforeach

      @if (!$isPending && empty($oldBreaks))
      <tr>
        <th>休憩{{ count($displayBreaks) + 1 }}</th>
        <td>
          <input type="time"
            name="breaks[{{ count($displayBreaks) }}][start]"
            step="60">

          <span class="span-from">～</span>

          <input type="time"
            name="breaks[{{ count($displayBreaks) }}][end]"
            step="60">
        </td>
      </tr>
      @endif

      </tbody>
      
      <tr>
        <th>備考</th>
        <td>
          @if ($isPending)
            <p class="remark-text">
              {{ $remark }}
            </p>
          @else
            <textarea name="remark" rows="3">{{ old('remark', $remark) }}</textarea>
          @endif

          @error('remark')
            <div class="error-text">
              {{ $message }}
            </div>
          @enderror
        </td>
      </tr>
    </table>

    {{-- 承認待ちメッセージ --}}
    @if ($isPending)
      <p class="alert">
        ・承認待ちのため修正はできません。
      </p>
    @else
      <div class="button-area">
        <button type="submit" class="btn-black">
          修正
        </button>
      </div>
    @endif

  </form>
</div>
@endsection

@section('script')
<script>
const IS_PENDING = @json($isPending);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    if (IS_PENDING) return;

    const tbody = document.getElementById('break-body');
    if (!tbody) return;

    function getRows() {
        return Array.from(tbody.querySelectorAll('tr'));
    }

    function createRow(index) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <th>休憩${index + 1}</th>
            <td>
                <input type="time" name="breaks[${index}][start]" step="60">
                <span class="span-from">～</span>
                <input type="time" name="breaks[${index}][end]" step="60">
            </td>
        `;
        return tr;
    }

    function updateLabels() {
        getRows().forEach((row, i) => {
            row.querySelector('th').textContent = `休憩${i + 1}`;
        });
    }

    function ensureSingleEmptyRow() {

        const rows = getRows();

        // 空白行を探す
        const emptyRows = rows.filter(row => {
            const inputs = row.querySelectorAll('input');
            return !inputs[0].value && !inputs[1].value;
        });

        // 空白行が2つ以上なら最後以外削除
        if (emptyRows.length > 1) {
            for (let i = 0; i < emptyRows.length - 1; i++) {
                emptyRows[i].remove();
            }
        }

        updateLabels();
    }

    function checkAndAddRow() {

        const rows = getRows();
        const lastRow = rows[rows.length - 1];
        const inputs = lastRow.querySelectorAll('input');

        const start = inputs[0].value;
        const end   = inputs[1].value;

        if (start && end) {
            const newRow = createRow(rows.length);
            tbody.appendChild(newRow);
            attachListeners(newRow);
        }

        ensureSingleEmptyRow();
    }

    function attachListeners(row) {
        row.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', checkAndAddRow);
        });
    }

    // 初期状態チェック
    const rows = getRows();

    if (rows.length === 0) {
        const newRow = createRow(0);
        tbody.appendChild(newRow);
        attachListeners(newRow);
    } else {
        rows.forEach(row => attachListeners(row));

        // ⭐ 追加：空行がなければ1行追加
        const hasEmptyRow = rows.some(row => {
            const inputs = row.querySelectorAll('input');
            return !inputs[0].value && !inputs[1].value;
        });

        if (!hasEmptyRow) {
            const newRow = createRow(rows.length);
            tbody.appendChild(newRow);
            attachListeners(newRow);
        }

        updateLabels();
    }


});
</script>
@endsection