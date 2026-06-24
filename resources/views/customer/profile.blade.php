@extends('customer.layouts.app')
@php $pageTitle = 'My Profile'; @endphp

@section('content')

{{-- Profile info --}}
<div class="page-header">
    <div>
        <div class="section-title">Account</div>
        <h5 class="page-heading">My Profile & Vehicles</h5>
    </div>
</div>

<div class="bottom-grid" style="margin-bottom:20px;">

    {{-- Profile card --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Personal Information</div>
        </div>
        <form method="POST" action="{{ route('customer.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="{{ $customer->first_name }}" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="{{ $customer->last_name }}" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="{{ Auth::user()->username }}" disabled
                       style="background:#f8f8fc; color:#aaa; cursor:not-allowed;">
            </div>
            <div style="padding:12px 20px 16px;">
                <button type="submit" class="btn-primary">
                    <i class="ti ti-device-floppy"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Vehicles card --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">My Vehicles</div>
            <button class="btn-primary" onclick="openModal('addVehicleModal')">
                <i class="ti ti-plus"></i> Add Vehicle
            </button>
        </div>

        @forelse($vehicles as $v)
        <div class="order-row">
            <div class="order-dot" style="background:#4f46e5;"></div>
            <div class="order-info">
                <div class="order-id">{{ $v->plate_number }}</div>
                <div class="order-meta">{{ $v->model }}</div>
            </div>
            <div style="display:flex; gap:6px;">
                <button class="btn-edit" onclick="openEditVehicle(
                    '{{ $v->vehicle_id }}',
                    '{{ $v->plate_number }}',
                    '{{ $v->model }}'
                )">
                    <i class="ti ti-edit"></i> Edit
                </button>
                <form method="POST"
                      action="{{ route('customer.vehicles.destroy', $v->vehicle_id) }}"
                      onsubmit="return confirm('Remove this vehicle?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete"><i class="ti ti-trash"></i> Remove</button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="ti ti-car"></i>
            <p>No vehicles added yet</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Add Vehicle Modal --}}
<div class="modal-overlay" id="addVehicleModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Add Vehicle</h6>
            <button class="modal-close" onclick="closeModal('addVehicleModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('customer.vehicles.store') }}">
            @csrf
            <div class="form-group">
                <label>Plate Number</label>
                <input type="text" name="plate_number" required placeholder="e.g. ABC 1234"
                       style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" required placeholder="e.g. Toyota Vios 2020">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addVehicleModal')">Cancel</button>
                <button type="submit" class="btn-primary">Add Vehicle</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Vehicle Modal --}}
<div class="modal-overlay" id="editVehicleModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Edit Vehicle</h6>
            <button class="modal-close" onclick="closeModal('editVehicleModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editVehicleForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Plate Number</label>
                <input type="text" name="plate_number" id="edit_plate" required
                       style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" id="edit_model" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editVehicleModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openEditVehicle(id, plate, model) {
    document.getElementById('edit_plate').value = plate;
    document.getElementById('edit_model').value = model;
    document.getElementById('editVehicleForm').action = `/customer/vehicles/${id}`;
    openModal('editVehicleModal');
}
</script>
@endsection
