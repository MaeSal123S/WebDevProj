@extends('advisor.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">Maintenance</div>
        <h5 class="page-heading">Vehicles</h5>
    </div>
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> Add Vehicle
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Vehicle list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                   placeholder="Search vehicles..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="vehicleTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Plate number</th>
                <th>Model</th>
                <th>Owner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->plate_number }}</td>
                <td>{{ $row->model }}</td>
                <td>
                    @if($row->customer)
                        {{ $row->customer->first_name }} {{ $row->customer->last_name }}
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $row->vehicle_id }}',
                        '{{ $row->plate_number }}',
                        '{{ $row->model }}',
                        '{{ $row->customer_id ?? 0 }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('advisor.vehicles.destroy', $row->vehicle_id) }}"
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
                <td colspan="5" class="empty-row">No vehicles found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Add vehicle</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('advisor.vehicles.store') }}">
            @csrf
            <div class="form-group">
                <label>Owner (Customer)</label>
                <select name="customer_id" required>
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Plate number</label>
                <input type="text" name="plate_number" required placeholder="Enter plate number">
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" required placeholder="Enter vehicle model">
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
            <h6>Edit vehicle</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Owner (Customer)</label>
                <select name="customer_id" id="edit_customer_id" required>
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Plate number</label>
                <input type="text" name="plate_number" id="edit_plate_number" required>
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" id="edit_model" required>
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
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openEditModal(id, plateNumber, model, customerId) {
    document.getElementById('edit_plate_number').value = plateNumber;
    document.getElementById('edit_model').value = model;
    document.getElementById('edit_customer_id').value = customerId;
    document.getElementById('editForm').action = `/advisor/vehicles/${id}`;
    openModal('editModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#vehicleTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection