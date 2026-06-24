@extends('admin.layouts.app')
@section('content')

@if(session('success'))
<div class="alert-success">
    <i class="ti ti-circle-check"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert-error">
    <i class="ti ti-circle-x"></i>
    {{ session('error') }}
</div>
@endif

<div class="page-header">
    <div>
        <div class="section-title">System</div>
        <h5 class="page-heading">User Management</h5>
    </div>
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> Add User
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">User list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                placeholder="Search users..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="userTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Role</th>
                <th>Linked advisor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <span class="user-badge">
                        <i class="ti ti-user"></i>
                        {{ $row->username }}
                    </span>
                </td>
                <td>
                    <span class="role-badge {{ $row->role == 'admin' ? 'role-admin' : ($row->role == 'service_advisor' ? 'role-advisor' : 'role-customer') }}">
                        {{ $row->role }}
                    </span>
                </td>
                <td>
                    @if($row->advisor)
                    {{ $row->advisor->first_name }} {{ $row->advisor->last_name }}
                    @else
                    <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td>
                    @if($row->role === 'admin')
                    @if($row->user_id === Auth::id())
                    <button class="btn-edit" onclick="openEditModal(
                                '{{ $row->user_id }}',
                                '{{ $row->username }}',
                                '{{ $row->role }}',
                                '{{ $row->advisor_id ?? 0 }}',
                                'true'
                            )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    @else
                    <span style="color:#aaa">—</span>
                    @endif
                    @else
                    <button class="btn-edit" onclick="openEditModal(
                            '{{ $row->user_id }}',
                            '{{ $row->username }}',
                            '{{ $row->role }}',
                            '{{ $row->advisor_id ?? 0 }}',
                            'false'
                        )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <button class="btn-permission" onclick="openPermissionModal('{{ $row->user_id }}', '{{ $row->username }}')">
                        <i class="ti ti-shield-check"></i> Permissions
                    </button>
                    <form method="POST"
                        action="{{ route('admin.users.destroy', $row->user_id) }}"
                        style="display:inline;"
                        onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            <i class="ti ti-trash"></i> Delete
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-row">No users found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box" style="width:500px">
        <div class="modal-header">
            <h6>Add user</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required
                    onchange="toggleAdvisorField(this, 'add_advisor_field')">
                    <option value="">Select role</option>
                    <option value="admin">Admin</option>
                    <option value="service_advisor">Service Advisor</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
            <div class="form-group" id="add_advisor_field" style="display:none">
                <label>Link to advisor</label>
                <select name="advisor_id">
                    <option value="">Select advisor</option>
                    @foreach($advisors as $advisor)
                    <option value="{{ $advisor->advisor_id }}">
                        {{ $advisor->first_name }} {{ $advisor->last_name }}
                    </option>
                    @endforeach
                </select>
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
    <div class="modal-box" style="width:500px">
        <div class="modal-header">
            <h6>Edit user</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group" id="field_username">
                <label>Username</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Password
                    <span style="color:#aaa;font-size:11px;">(leave blank to keep current)</span>
                </label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>
            <div class="form-group" id="field_role">
                <label>Role</label>
                <select name="role" id="edit_role"
                    onchange="toggleAdvisorField(this, 'edit_advisor_field')">
                    <option value="admin">Admin</option>
                    <option value="service_advisor">Service Advisor</option>
                    <option value="customer">Customer</option>
                </select>
            </div>
            <div class="form-group" id="edit_advisor_field">
                <label>Link to advisor</label>
                <select name="advisor_id" id="edit_advisor_id">
                    <option value="">Select advisor</option>
                    @foreach($advisors as $advisor)
                    <option value="{{ $advisor->advisor_id }}">
                        {{ $advisor->first_name }} {{ $advisor->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                    onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- PERMISSION MODAL -->
<div class="modal-overlay" id="permissionModal">
    <div class="modal-box" style="width:600px">
        <div class="modal-header">
            <h6>Manage permissions — <span id="permission_username"></span></h6>
            <button class="modal-close" onclick="closeModal('permissionModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="permissionForm" action="">
            @csrf
            @method('PUT')
            <div style="padding: 16px 20px; max-height: 400px; overflow-y: auto;">
                @foreach($permissions as $module => $modulePermissions)
                <div class="permission-module">
                    <div class="permission-module-title">
                        {{ ucwords(str_replace('_', ' ', $module)) }}
                    </div>
                    <div class="permission-checks">
                        @foreach($modulePermissions as $permission)
                        <label class="permission-item">
                            <input type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->permission_id }}"
                                class="perm-checkbox perm-{{ $permission->permission_id }}">
                            {{ ucfirst($permission->action) }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                    onclick="closeModal('permissionModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Permissions</button>
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

    function openEditModal(id, username, role, advisorId, isAdmin) {
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_advisor_id').value = advisorId;
        document.getElementById('editForm').action = `/admin/users/${id}`;

        if (isAdmin === 'true') {
            document.getElementById('field_username').style.display = 'none';
            document.getElementById('field_role').style.display = 'none';
            document.getElementById('edit_advisor_field').style.display = 'none';
        } else {
            document.getElementById('field_username').style.display = 'block';
            document.getElementById('field_role').style.display = 'block';
            toggleAdvisorField(document.getElementById('edit_role'), 'edit_advisor_field');
        }

        openModal('editModal');
    }

    function openPermissionModal(userId, username) {
        document.getElementById('permission_username').innerText = username;
        document.getElementById('permissionForm').action = `/admin/users/${userId}/permissions`;

        // uncheck all first
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

        // fetch current permissions
        fetch(`/admin/users/${userId}/permissions`)
            .then(response => response.json())
            .then(data => {
                data.forEach(permId => {
                    const cb = document.querySelector(`.perm-${permId}`);
                    if (cb) cb.checked = true;
                });
            });

        openModal('permissionModal');
    }

    function toggleAdvisorField(select, fieldId) {
        document.getElementById(fieldId).style.display =
            select.value == 'service_advisor' ? 'block' : 'none';
    }

    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#userTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }
</script>
@endsection