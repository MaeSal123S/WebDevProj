@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">Maintenance</div>
        <h5 class="page-heading">Service Advisors</h5>
    </div>
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> Add Advisor
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Service advisor list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                   placeholder="Search advisors..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="advisorTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Last name</th>
                <th>First name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($advisors as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->last_name }}</td>
                <td>{{ $row->first_name }}</td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $row->advisor_id }}',
                        '{{ $row->last_name }}',
                        '{{ $row->first_name }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('admin.advisors.destroy', $row->advisor_id) }}"
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
                <td colspan="4" class="empty-row">No advisors found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Add service advisor</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.advisors.store') }}">
            @csrf
            <div class="form-group">
                <label>Last name</label>
                <input type="text" name="last_name" required placeholder="Enter last name">
            </div>
            <div class="form-group">
                <label>First name</label>
                <input type="text" name="first_name" required placeholder="Enter first name">
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
            <h6>Edit service advisor</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Last name</label>
                <input type="text" name="last_name" id="edit_last_name" required>
            </div>
            <div class="form-group">
                <label>First name</label>
                <input type="text" name="first_name" id="edit_first_name" required>
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

function openEditModal(id, lastName, firstName) {
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('editForm').action = `/admin/advisors/${id}`;
    openModal('editModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#advisorTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection