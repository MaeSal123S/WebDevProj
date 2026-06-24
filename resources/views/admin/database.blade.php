@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">System</div>
        <h5 class="page-heading">View Database</h5>
    </div>
</div>

<div class="panel">
    <!-- TABS -->
    <div class="db-tabs">
        @foreach($data as $tableName => $rows)
            <button class="db-tab {{ $loop->first ? 'active' : '' }}"
                    onclick="switchTab('{{ $tableName }}')">
                {{ $tableName }}
                <span class="tab-count">{{ count($rows) }}</span>
            </button>
        @endforeach
    </div>

    <!-- TAB CONTENT -->
    @foreach($data as $tableName => $rows)
    <div class="db-tab-content" id="tab-{{ $tableName }}"
         @if(!$loop->first) style="display:none" @endif>

        @if(count($rows) > 0)
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach(array_keys((array)$rows[0]) as $col)
                            <th>{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        @foreach((array)$row as $val)
                            <td>{{ $val ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="empty-state">
                <i class="ti ti-database-off"></i>
                <p>No data found</p>
            </div>
        @endif
    </div>
    @endforeach
</div>

@endsection

@section('scripts')
<script>
function switchTab(tableName) {
    document.querySelectorAll('.db-tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    document.querySelectorAll('.db-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById('tab-' + tableName).style.display = 'block';
    event.target.classList.add('active');
}
</script>
@endsection