@extends('customer.layouts.app')
@php $pageTitle = 'My Appointments'; @endphp

@section('content')
<div class="page-header">
    <div>
        <div class="section-title">Appointments</div>
        <h5 class="page-heading">My Appointments</h5>
    </div>
    <button class="btn-primary" onclick="openModal('bookModal')">
        <i class="ti ti-plus"></i> Book Appointment
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Appointment history</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput" placeholder="Search..." onkeyup="searchTable()">
        </div>
    </div>

    <table class="data-table" id="apptTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Time</th>
                <th>Vehicle</th>
                <th>Service</th>
                <th>Advisor</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $index => $apt)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</td>
                <td>
                    @if($apt->vehicle)
                        {{ $apt->vehicle->plate_number }}<br>
                        <small style="color:#888;">{{ $apt->vehicle->model }}</small>
                    @else
                        <span style="color:#aaa;">—</span>
                    @endif
                </td>
                <td>
                    @forelse($apt->serviceTypes as $st)
                        <span class="service-badge">{{ $st->service_type_name }}</span>
                    @empty
                        {{ $apt->serviceType->service_type_name ?? '—' }}
                    @endforelse
                </td>
                <td>
                    @if($apt->advisor)
                        {{ $apt->advisor->first_name }} {{ $apt->advisor->last_name }}
                    @else
                        <span style="color:#aaa;">Not assigned yet</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge status-{{ $apt->status }}">
                        {{ ucfirst($apt->status) }}
                    </span>
                </td>
                <td>
                    @if($apt->status === 'pending')
                    <form method="POST"
                          action="{{ route('customer.appointments.destroy', $apt->appointment_id) }}"
                          style="display:inline;"
                          onsubmit="return confirm('Cancel this appointment?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            <i class="ti ti-x"></i> Cancel
                        </button>
                    </form>
                    @else
                        <span style="color:#aaa; font-size:12px;">—</span>
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

{{-- Book appointment modal --}}
<div class="modal-overlay" id="bookModal">
    <div class="modal-box" style="width:480px;">
        <div class="modal-header">
            <h6>Book an Appointment</h6>
            <button class="modal-close" onclick="closeModal('bookModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('customer.appointments.store') }}">
            @csrf
            <div class="form-group">
                <label>Vehicle</label>
                @if($vehicles->isEmpty())
                    <div class="vehicle-display">
                        <i class="ti ti-alert-circle"></i>
                        No vehicles registered. Please contact the shop to register your vehicle.
                    </div>
                    <input type="hidden" name="vehicle_id" value="">
                @else
                    <select name="vehicle_id" required>
                        <option value="">Select your vehicle</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->vehicle_id }}">
                                {{ $v->plate_number }} — {{ $v->model }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="form-group">
                <label>Service Types <span style="font-size:11px;color:#777;">(select one or more)</span></label>
                <div class="checkbox-group" style="max-height:150px;">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                               value="{{ $st->service_type_id }}">
                        {{ $st->service_type_name }}
                        <span style="color:#888;font-size:11px;">
                            ({{ $st->predetermined_hours }}h &mdash; ₱{{ number_format($st->book_rate, 2) }})
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
                <label>Preferred Time</label>
                <input type="time" name="appointment_time" required>
            </div>
            <div class="form-group">
                <label>Notes <span style="color:#aaa;font-size:11px;">(optional)</span></label>
                <input type="text" name="notes" placeholder="Any special requests or concerns">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('bookModal')">Cancel</button>
                @if($vehicles->isEmpty())
                    <button type="submit" class="btn-primary" disabled style="opacity:0.5;cursor:not-allowed;">
                        Book Appointment
                    </button>
                @else
                    <button type="submit" class="btn-primary">Book Appointment</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#apptTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection
