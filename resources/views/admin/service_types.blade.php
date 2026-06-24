@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">Maintenance</div>
        <h5 class="page-heading">Service Types</h5>
    </div>
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> Add Service Type
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Service type list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                   placeholder="Search service types..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="serviceTypeTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Service type</th>
                <th>Predetermined hours</th>
                <th>Book rate/hr</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($service_types as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->service_type_name }}</td>
                <td>{{ $row->predetermined_hours }} hrs</td>
                <td>₱{{ number_format($row->book_rate, 2) }}</td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $row->service_type_id }}',
                        '{{ $row->service_type_name }}',
                        '{{ $row->predetermined_hours }}',
                        '{{ $row->book_rate }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('admin.service_types.destroy', $row->service_type_id) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            <i class="ti ti-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-row">No service types found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Add service type</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.service_types.store') }}">
            @csrf
            <div class="form-group">
                <label>Service type name</label>
                <input type="text" name="service_type_name" required placeholder="e.g. Oil Change">
            </div>
            <div class="form-group">
                <label>Predetermined hours</label>
                <input type="number" name="predetermined_hours" required
                       placeholder="e.g. 1.50" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Book rate (₱/hr)</label>
                <input type="number" name="book_rate" required
                       placeholder="e.g. 50.00" step="0.01" min="0">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Edit service type</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Service type name</label>
                <input type="text" name="service_type_name"
                       id="edit_service_type_name" required>
            </div>
            <div class="form-group">
                <label>Predetermined hours</label>
                <input type="number" name="predetermined_hours"
                       id="edit_predetermined_hours" required step="0.01" min="0">
            </div>
            <div class="form-group">
                <label>Book rate (₱/hr)</label>
                <input type="number" name="book_rate"
                       id="edit_book_rate" required step="0.01" min="0">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openEditModal(id, name, hours, rate) {
    document.getElementById('edit_service_type_name').value = name;
    document.getElementById('edit_predetermined_hours').value = hours;
    document.getElementById('edit_book_rate').value = rate;
    document.getElementById('editForm').action = `/admin/service-types/${id}`;
    openModal('editModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#serviceTypeTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection