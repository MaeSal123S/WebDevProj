@extends('advisor.layouts.app')
@section('content')
@php $currentUser = App\Models\User::find(Auth::id()); @endphp

<div class="page-header">
    <div>
        <div class="section-title">Appointments</div>
        <h5 class="page-heading">Appointments</h5>
    </div>
    @if($currentUser->hasPermission('appointment', 'add'))
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> New Appointment
    </button>
    @endif
</div>

{{-- =============================================
     SECTION 1: PENDING BOOKINGS FROM CUSTOMERS
     ============================================= --}}
<div class="panel" style="margin-bottom:16px;">
    <div class="panel-header">
        <div class="panel-title" style="display:flex;align-items:center;gap:8px;">
            <i class="ti ti-inbox" style="color:var(--red-light);font-size:16px;"></i>
            Pending Bookings
            @if($pendingBookings->count() > 0)
            <span style="background:var(--red);color:#fff;font-size:10px;font-weight:700;
                         padding:2px 7px;border-radius:20px;line-height:1.4;">
                {{ $pendingBookings->count() }}
            </span>
            @endif
        </div>
        <span style="font-size:11px;color:#666;">
            Customer bookings waiting for an advisor to accept
        </span>
    </div>

    @if($pendingBookings->isEmpty())
    <div class="empty-state">
        <i class="ti ti-calendar-off"></i>
        <p>No pending bookings at the moment</p>
    </div>
    @else
    <table class="data-table" id="pendingTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Services</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingBookings as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->appointment_date)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($row->appointment_time)->format('h:i A') }}</td>
                <td>
                    @if($row->customer)
                        <span style="font-weight:600;color:#e0e0e0;">
                            {{ $row->customer->first_name }} {{ $row->customer->last_name }}
                        </span>
                    @else
                        <span style="color:#666;">—</span>
                    @endif
                </td>
                <td>
                    @if($row->vehicle)
                        {{ $row->vehicle->plate_number }}<br>
                        <small>{{ $row->vehicle->model }}</small>
                    @else
                        <span style="color:#666;">—</span>
                    @endif
                </td>
                <td class="service-col">
                    @forelse($row->serviceTypes as $st)
                        <span class="service-badge">{{ $st->service_type_name }}</span>
                    @empty
                        <span style="color:#666;">—</span>
                    @endforelse
                </td>
                <td style="font-size:12px;color:#bbb;max-width:160px;">
                    {{ $row->notes ?? '—' }}
                </td>
                <td style="white-space:nowrap;">
                    @if($currentUser->hasPermission('appointment', 'edit'))
                    {{-- ACCEPT --}}
                    <form method="POST"
                          action="{{ route('advisor.appointments.accept', $row->appointment_id) }}"
                          style="display:inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-accept"
                                title="Accept this appointment">
                            <i class="ti ti-check"></i> Accept
                        </button>
                    </form>
                    {{-- DECLINE --}}
                    <form method="POST"
                          action="{{ route('advisor.appointments.decline', $row->appointment_id) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Decline this appointment?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-decline"
                                title="Decline this appointment">
                            <i class="ti ti-x"></i> Decline
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- =============================================
     SECTION 2: MY ACCEPTED APPOINTMENTS
     ============================================= --}}
<div class="panel">
    <div class="panel-header">
        <div class="panel-title" style="display:flex;align-items:center;gap:8px;">
            <i class="ti ti-calendar-check" style="color:#4ddb8a;font-size:16px;"></i>
            My Appointments
        </div>
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
                <th>Services</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($myAppointments as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->appointment_date)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($row->appointment_time)->format('h:i A') }}</td>
                <td>
                    @if($row->customer)
                        {{ $row->customer->first_name }} {{ $row->customer->last_name }}
                    @else
                        <span style="color:#666;">—</span>
                    @endif
                </td>
                <td>
                    @if($row->vehicle)
                        {{ $row->vehicle->plate_number }}<br>
                        <small>{{ $row->vehicle->model }}</small>
                    @else
                        <span style="color:#666;">—</span>
                    @endif
                </td>
                <td class="service-col">
                    @forelse($row->serviceTypes as $st)
                        <span class="service-badge">{{ $st->service_type_name }}</span>
                    @empty
                        <span style="color:#666;">—</span>
                    @endforelse
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
                <td style="white-space:nowrap;">
                    @if($currentUser->hasPermission('appointment', 'status'))
                    <button class="btn-status" onclick="openStatusModal(
                        '{{ $row->appointment_id }}',
                        '{{ $row->status }}'
                    )">
                        <i class="ti ti-refresh"></i> Status
                    </button>
                    @endif
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
                    @endif
                    @if($currentUser->hasPermission('appointment', 'delete'))
                    <form method="POST"
                          action="{{ route('advisor.appointments.destroy', $row->appointment_id) }}"
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
                <td colspan="8" class="empty-row">No appointments yet</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ADD MODAL --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box" style="width:500px">
        <div class="modal-header">
            <h6>New appointment</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('advisor.appointments.store') }}">
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
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" required min="{{ date('Y-m-d') }}">
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
                <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
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
                <div class="checkbox-group" style="max-height:140px;">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                               value="{{ $st->service_type_id }}"
                               class="adv-edit-st-check">
                        {{ $st->service_type_name }}
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
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- STATUS MODAL --}}
<div class="modal-overlay" id="statusModal">
    <div class="modal-box" style="width:380px;">
        <div class="modal-header">
            <h6>Update Appointment Status</h6>
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
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
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
    fetch(`/advisor/vehicles-by-customer/${customerId}`)
        .then(r => r.json())
        .then(vehicles => {
            if (vehicles.length === 0) {
                display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle registered';
                hidden.value = '';
            } else {
                display.innerHTML = `<i class="ti ti-car"></i> ${vehicles[0].plate_number} — ${vehicles[0].model}`;
                hidden.value = vehicles[0].vehicle_id;
            }
        });
}

function openEditModal(id, customerId, vehicleId, serviceTypeIds, date, time, notes) {
    document.getElementById('edit_customer').value = customerId;
    document.getElementById('edit_date').value     = date;
    document.getElementById('edit_time').value     = time;
    document.getElementById('edit_notes').value    = notes;
    document.getElementById('editForm').action     = `/advisor/appointments/${id}`;

    document.querySelectorAll('.adv-edit-st-check').forEach(cb => {
        cb.checked = serviceTypeIds.includes(parseInt(cb.value));
    });

    const display = document.getElementById('edit_vehicle_display');
    const hidden  = document.getElementById('edit_vehicle_id');
    display.innerHTML = '<i class="ti ti-loader"></i> Loading...';
    fetch(`/advisor/vehicles-by-customer/${customerId}`)
        .then(r => r.json())
        .then(vehicles => {
            const v = vehicles.find(v => v.vehicle_id == vehicleId);
            if (v) {
                display.innerHTML = `<i class="ti ti-car"></i> ${v.plate_number} — ${v.model}`;
                hidden.value = v.vehicle_id;
            } else {
                display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle found';
                hidden.value = '';
            }
        });
    openModal('editModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#appointmentTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}

function openStatusModal(id, currentStatus) {
    document.getElementById('status_select').value = currentStatus;
    document.getElementById('statusForm').action   = `/advisor/appointments/${id}/status`;
    openModal('statusModal');
}
</script>
@endsection
