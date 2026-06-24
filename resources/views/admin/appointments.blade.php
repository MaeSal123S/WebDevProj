@extends('admin.layouts.app')
@section('content')
@php $currentUser = App\Models\User::find(Auth::id()); @endphp

<div class="page-header">
    <div>
        <div class="section-title">Appointments</div>
        <h5 class="page-heading">Manage Appointments</h5>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('admin.calendar') }}" class="btn-secondary">
            <i class="ti ti-calendar-event"></i> View Calendar
        </a>
        @if($currentUser->hasPermission('appointment', 'add'))
        <button class="btn-primary" onclick="openModal('addModal')">
            <i class="ti ti-plus"></i> New Appointment
        </button>
        @endif
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Appointment list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                   placeholder="Search appointments..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="appointmentTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Service</th>
                <th>Advisor</th>
                <th>Status</th>
                <th>Booked by</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->appointment_date }}</td>
                <td>{{ \Carbon\Carbon::parse($row->appointment_time)->format('h:i A') }}</td>
                <td>
                    @if($row->customer)
                        {{ $row->customer->first_name }} {{ $row->customer->last_name }}
                        @if($row->customer->deleted_at)
                            <span style="color:#aaa;font-size:10px;">(deleted)</span>
                        @endif
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td>
                    @if($row->vehicle)
                        {{ $row->vehicle->plate_number }}<br>
                        <small style="color:#888">{{ $row->vehicle->model }}</small>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td class="service-col">
                    @forelse($row->serviceTypes as $st)
                        <span class="service-badge">{{ $st->service_type_name }}</span>
                    @empty
                        {{ $row->serviceType->service_type_name ?? '—' }}
                    @endforelse
                </td>
                <td>
                    @if($row->advisor)
                        {{ $row->advisor->first_name }} {{ $row->advisor->last_name }}
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
                <td>
                    @php
                        $statusClass = match($row->status) {
                            'pending'   => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'cancelled' => 'status-cancelled',
                            'completed' => 'status-completed',
                            default     => ''
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ ucfirst($row->status) }}
                    </span>
                </td>
                <td>{{ $row->bookedBy->username ?? '—' }}</td>
                <td style="white-space:nowrap;">
                    @if($currentUser->hasPermission('appointment', 'edit'))
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $row->appointment_id }}',
                        '{{ $row->customer_id }}',
                        '{{ $row->vehicle_id }}',
                        {{ json_encode($row->serviceTypes->pluck('service_type_id')) }},
                        '{{ $row->appointment_date }}',
                        '{{ $row->appointment_time }}',
                        '{{ addslashes($row->notes ?? '') }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <button class="btn-status" onclick="openStatusModal(
                        '{{ $row->appointment_id }}',
                        '{{ $row->status }}'
                    )">
                        <i class="ti ti-refresh"></i> Status
                    </button>
                    @endif
                    @if($currentUser->hasPermission('appointment', 'delete'))
                    <form method="POST"
                          action="{{ route('admin.appointments.destroy', $row->appointment_id) }}"
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
                <td colspan="10" class="empty-row">No appointments found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box" style="width:500px">
        <div class="modal-header">
            <h6>New appointment</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.appointments.store') }}">
            @csrf
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" id="add_customer" required
                        onchange="showCustomerVehicle(this.value, 'add_vehicle_display', 'add_vehicle_id')">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Vehicle</label>
                <div id="add_vehicle_display" class="vehicle-display">
                    <i class="ti ti-car"></i> Select a customer first
                </div>
                <input type="hidden" name="vehicle_id" id="add_vehicle_id">
            </div>
            <div class="form-group">
                <label>Service types <span style="font-size:11px;color:#777;">(select one or more)</span></label>
                <div class="checkbox-group" style="max-height:140px;">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                               value="{{ $st->service_type_id }}">
                        {{ $st->service_type_name }}
                        <span style="color:#888;font-size:11px;">
                            ({{ $st->predetermined_hours }}h — ₱{{ number_format($st->book_rate,2) }})
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" required
                       min="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="appointment_time" required>
            </div>
            <div class="form-group">
                <label>Notes <span style="color:#777;font-size:11px;">(optional)</span></label>
                <input type="text" name="notes" placeholder="Additional notes">
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
            <h6>Edit appointment</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" id="edit_customer" required
                        onchange="showCustomerVehicle(this.value, 'edit_vehicle_display', 'edit_vehicle_id')">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->customer_id }}">
                            {{ $customer->first_name }} {{ $customer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Vehicle</label>
                <div id="edit_vehicle_display" class="vehicle-display">
                    <i class="ti ti-car"></i> Select a customer first
                </div>
                <input type="hidden" name="vehicle_id" id="edit_vehicle_id">
            </div>
            <div class="form-group">
                <label>Service types <span style="font-size:11px;color:#777;">(select one or more)</span></label>
                <div class="checkbox-group" id="edit_service_types" style="max-height:140px;">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                               value="{{ $st->service_type_id }}"
                               class="edit-st-check">
                        {{ $st->service_type_name }}
                        <span style="color:#888;font-size:11px;">
                            ({{ $st->predetermined_hours }}h — ₱{{ number_format($st->book_rate,2) }})
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" id="edit_date" required>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="appointment_time" id="edit_time" required>
            </div>
            <div class="form-group">
                <label>Notes <span style="color:#777;font-size:11px;">(optional)</span></label>
                <input type="text" name="notes" id="edit_notes" placeholder="Additional notes">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- STATUS MODAL -->
<div class="modal-overlay" id="statusModal">
    <div class="modal-box" style="width:400px">
        <div class="modal-header">
            <h6>Update appointment status</h6>
            <button class="modal-close" onclick="closeModal('statusModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="statusForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status_select" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('statusModal')">Cancel</button>
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

function showCustomerVehicle(customerId, displayId, hiddenId) {
    const display = document.getElementById(displayId);
    const hidden  = document.getElementById(hiddenId);

    if (!customerId) {
        display.innerHTML = '<i class="ti ti-car"></i> Select a customer first';
        hidden.value = '';
        return;
    }

    display.innerHTML = '<i class="ti ti-loader"></i> Loading...';

    fetch(`/admin/vehicles-by-customer/${customerId}`)
        .then(response => response.json())
        .then(vehicles => {
            if (vehicles.length === 0) {
                display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle registered for this customer';
                hidden.value = '';
            } else {
                const vehicle = vehicles[0];
                display.innerHTML = `<i class="ti ti-car"></i> ${vehicle.plate_number} — ${vehicle.model}`;
                hidden.value = vehicle.vehicle_id;
            }
        });
}

function openEditModal(id, customerId, vehicleId, serviceTypeIds, date, time, notes) {
    document.getElementById('edit_customer').value = customerId;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_time').value = time;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editForm').action = `/admin/appointments/${id}`;

    // Tick correct service type checkboxes
    document.querySelectorAll('.edit-st-check').forEach(cb => {
        cb.checked = serviceTypeIds.includes(parseInt(cb.value));
    });

    const display = document.getElementById('edit_vehicle_display');
    const hidden  = document.getElementById('edit_vehicle_id');
    display.innerHTML = '<i class="ti ti-loader"></i> Loading...';

    fetch(`/admin/vehicles-by-customer/${customerId}`)
        .then(response => response.json())
        .then(vehicles => {
            const vehicle = vehicles.find(v => v.vehicle_id == vehicleId);
            if (vehicle) {
                display.innerHTML = `<i class="ti ti-car"></i> ${vehicle.plate_number} — ${vehicle.model}`;
                hidden.value = vehicle.vehicle_id;
            } else {
                display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle found';
                hidden.value = '';
            }
        });

    openModal('editModal');
}

function openStatusModal(id, currentStatus) {
    document.getElementById('status_select').value = currentStatus;
    document.getElementById('statusForm').action = `/admin/appointments/${id}/status`;
    openModal('statusModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#appointmentTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection