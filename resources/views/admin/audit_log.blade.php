@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">System</div>
        <h5 class="page-heading">Audit Logs</h5>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">System activity logs</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                placeholder="Search logs..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="auditTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Table</th>
                <th>Record ID</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $log->timestamp }}</td>
                <td>
                    <span class="user-badge">
                        <i class="ti ti-user"></i>
                        {{ $log->user->username }}
                    </span>
                </td>
                <td>
                    @php
                    $badgeClass = match($log->action) {
                    'INSERT' => 'badge-insert',
                    'UPDATE' => 'badge-update',
                    'DELETE' => 'badge-delete',
                    'LOGIN' => 'badge-login',
                    'LOGOUT' => 'badge-logout',
                    'LOGIN_FAILED' => 'badge-failed',
                    'LOGIN_LOCKED' => 'badge-locked',
                    'LOGIN_BLOCKED' => 'badge-locked',
                    'PASSWORD_RESET' => 'badge-reset',
                    default => 'badge-update'
                    };
                    @endphp
                    <span class="action-badge {{ $badgeClass }}">{{ $log->action }}</span>
                </td>
                <td><span class="table-badge">{{ $log->table_name }}</span></td>
                <td>{{ $log->record_id }}</td>
                <td style="font-size:12px; color:#bbb;">{{ $log->changes }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-row">No logs found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#auditTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }
</script>
@endsection