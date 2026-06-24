@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">Maintenance</div>
        <h5 class="page-heading">Repair Orders</h5>
    </div>
    <button class="btn-primary" onclick="openModal('addModal')">
        <i class="ti ti-plus"></i> New Repair Order
    </button>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Repair order list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                placeholder="Search orders..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="repairOrderTable">
        <thead>
            <tr>
                <th>No.</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Service Advisor</th>
                <th>Services</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($repair_orders as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->date_of_service }}</td>
                <td>
                    @if($row->customer)
                    {{ $row->customer->first_name }} {{ $row->customer->last_name }}
                    @if($row->customer->deleted_at)
                    <span style="color:#aaa; font-size:10px;">(deleted)</span>
                    @endif
                    @else
                    <span style="color:#aaa">— deleted —</span>
                    @endif
                </td>
                <td>
                    @if($row->vehicle)
                    {{ $row->vehicle->plate_number }}<br>
                    <small style="color:#888">{{ $row->vehicle->model }}</small>
                    @if($row->vehicle->deleted_at)
                    <span style="color:#aaa; font-size:10px;">(deleted)</span>
                    @endif
                    @else
                    <span style="color:#aaa">— deleted —</span>
                    @endif
                </td>
                <td>
                    @if($row->advisor)
                    {{ $row->advisor->first_name }} {{ $row->advisor->last_name }}
                    @if($row->advisor->deleted_at)
                    <span style="color:#aaa; font-size:10px;">(deleted)</span>
                    @endif
                    @else
                    <span style="color:#aaa">— deleted —</span>
                    @endif
                </td>
                <td>
                    @foreach($row->serviceTypes as $st)
                    <span class="service-badge">
                        {{ $st->service_type_name }}
                        @if($st->deleted_at)
                        <span style="font-size:10px;">(deleted)</span>
                        @endif
                    </span>
                    @endforeach
                </td>
                <td>
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $row->order_no }}',
                        '{{ $row->date_of_service }}',
                        '{{ $row->customer_id ?? 0 }}',
                        '{{ $row->vehicle_id ?? 0 }}',
                        '{{ $row->advisor_id ?? 0 }}',
                        '{{ $row->serviceTypes->pluck('service_type_id')->join(',') }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    <form method="POST"
                        action="{{ route('admin.repair_orders.destroy', $row->order_no) }}"
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
                <td colspan="7" class="empty-row">No repair orders found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box" style="width:500px">
        <div class="modal-header">
            <h6>New repair order</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.repair_orders.store') }}">
            @csrf
            <div class="form-group">
                <label>Date of service</label>
                <input type="date" name="date_of_service" required>
            </div>
            <div class="form-group">
                <label>Customer</label>
                <select name="customer_id" id="add_customer" required
                    onchange="prefillFromAppointment(this.value)">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->customer_id }}">
                        {{ $customer->first_name }} {{ $customer->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Vehicle
                    <span style="font-size:11px;color:#777;"> — auto-filled from latest appointment</span>
                </label>
                <div id="add_vehicle_display" class="vehicle-display">
                    <i class="ti ti-car"></i> Select a customer first
                </div>
                <input type="hidden" name="vehicle_id" id="add_vehicle_id">
            </div>
            <div class="form-group">
                <label>Service advisor</label>
                <select name="advisor_id" required>
                    <option value="">Select advisor</option>
                    @foreach($advisors as $advisor)
                    <option value="{{ $advisor->advisor_id }}">
                        {{ $advisor->first_name }} {{ $advisor->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Service types</label>
                <div class="checkbox-group">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                            value="{{ $st->service_type_id }}">
                        {{ $st->service_type_name }}
                        <span style="color:#888;font-size:11px;">
                            ({{ $st->predetermined_hours }}hrs
                            ₱{{ number_format($st->book_rate, 2) }})
                        </span>
                    </label>
                    @endforeach
                </div>
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
            <h6>Edit repair order</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Date of service</label>
                <input type="date" name="date_of_service" id="edit_date" required>
            </div>
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
                <label>Service advisor</label>
                <select name="advisor_id" id="edit_advisor" required>
                    <option value="">Select advisor</option>
                    @foreach($advisors as $advisor)
                    <option value="{{ $advisor->advisor_id }}">
                        {{ $advisor->first_name }} {{ $advisor->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Service types</label>
                <div class="checkbox-group">
                    @foreach($service_types as $st)
                    <label class="checkbox-item">
                        <input type="checkbox" name="service_type_ids[]"
                            value="{{ $st->service_type_id }}"
                            class="edit-service-checkbox">
                        {{ $st->service_type_name }}
                        <span style="color:#888;font-size:11px;">
                            ({{ $st->predetermined_hours }}hrs
                            ₱{{ number_format($st->book_rate, 2) }})
                        </span>
                    </label>
                    @endforeach
                </div>
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

    // Called when customer changes in ADD modal —
    // fetches latest appointment and pre-fills vehicle + service types
    function prefillFromAppointment(customerId) {
        const display = document.getElementById('add_vehicle_display');
        const hidden  = document.getElementById('add_vehicle_id');

        // Reset checkboxes
        document.querySelectorAll('#addModal input[name="service_type_ids[]"]')
            .forEach(cb => cb.checked = false);

        if (!customerId) {
            display.innerHTML = '<i class="ti ti-car"></i> Select a customer first';
            hidden.value = '';
            return;
        }

        display.innerHTML = '<i class="ti ti-loader"></i> Loading...';

        fetch(`/admin/appointment-by-customer/${customerId}`)
            .then(r => r.json())
            .then(data => {
                if (data && data.vehicle_id) {
                    // Lock the vehicle — show as read-only display
                    display.innerHTML = `<i class="ti ti-car"></i> ${data.plate_number} — ${data.model}`;
                    hidden.value = data.vehicle_id;

                    // Pre-check service types from appointment (still editable)
                    if (data.service_type_ids && data.service_type_ids.length) {
                        document.querySelectorAll('#addModal input[name="service_type_ids[]"]')
                            .forEach(cb => {
                                cb.checked = data.service_type_ids
                                    .map(Number)
                                    .includes(parseInt(cb.value));
                            });
                    }
                } else {
                    // No appointment — fallback to vehicle list
                    fetch(`/admin/vehicles-by-customer/${customerId}`)
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
            });
    }

    function showCustomerVehicle(customerId, displayId, hiddenId) {
        const display = document.getElementById(displayId);
        const hidden = document.getElementById(hiddenId);

        if (!customerId) {
            display.innerHTML = '<i class="ti ti-car"></i> Select a customer first';
            display.className = 'vehicle-display';
            hidden.value = '';
            return;
        }

        display.innerHTML = '<i class="ti ti-loader"></i> Loading...';

        fetch(`/admin/vehicles-by-customer/${customerId}`)
            .then(response => response.json())
            .then(vehicles => {
                if (vehicles.length === 0) {
                    display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle registered for this customer';
                    display.className = 'vehicle-display';
                    hidden.value = '';
                } else {
                    const vehicle = vehicles[0];
                    display.innerHTML = `<i class="ti ti-car"></i> ${vehicle.plate_number} — ${vehicle.model}`;
                    display.className = 'vehicle-display';
                    hidden.value = vehicle.vehicle_id;
                }
            });
    }

    function openEditModal(id, date, customerId, vehicleId, advisorId, serviceTypeIds) {
        document.getElementById('edit_date').value = date;
        document.getElementById('edit_customer').value = customerId;
        document.getElementById('edit_advisor').value = advisorId;
        document.getElementById('editForm').action = `/admin/repair-orders/${id}`;

        const display = document.getElementById('edit_vehicle_display');
        const hidden = document.getElementById('edit_vehicle_id');

        display.innerHTML = '<i class="ti ti-loader"></i> Loading...';

        fetch(`/admin/vehicles-by-customer/${customerId}`)
            .then(response => response.json())
            .then(vehicles => {
                const vehicle = vehicles.find(v => v.vehicle_id == vehicleId);
                if (vehicle) {
                    display.innerHTML = `<i class="ti ti-car"></i> ${vehicle.plate_number} — ${vehicle.model}`;
                    display.className = 'vehicle-display';
                    hidden.value = vehicle.vehicle_id;
                } else {
                    display.innerHTML = '<i class="ti ti-alert-circle"></i> No vehicle found';
                    display.className = 'vehicle-display';
                    hidden.value = '';
                }
            });

        document.querySelectorAll('.edit-service-checkbox').forEach(cb => {
            cb.checked = serviceTypeIds.includes(parseInt(cb.value));
        });

        openModal('editModal');
    }

    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#repairOrderTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }
</script>
@endsection