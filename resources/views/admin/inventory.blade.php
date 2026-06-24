@extends('admin.layouts.app')
@section('content')
@php $currentUser = App\Models\User::find(Auth::id()); @endphp

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
        <div class="section-title">Inventory</div>
        <h5 class="page-heading">Auto Supplies</h5>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="btn-secondary" onclick="openModal('usageModal')">
            <i class="ti ti-minus"></i> Record Usage
        </button>
        @if($currentUser->hasPermission('inventory', 'add'))
        <button class="btn-primary" onclick="openModal('addModal')">
            <i class="ti ti-plus"></i> Add Supply
        </button>
        @endif
    </div>
</div>

{{-- Low stock alert --}}
@if($lowStock->count() > 0)
<div class="low-stock-alert">
    <i class="ti ti-alert-triangle"></i>
    <strong>Low Stock Alert!</strong>
    {{ $lowStock->count() }} item(s) are running low:
    {{ $lowStock->pluck('supply_name')->join(', ') }}
</div>
@endif

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Supply list</div>
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput"
                   placeholder="Search supplies..." onkeyup="searchTable()">
        </div>
    </div>
    <table class="data-table" id="inventoryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Supply name</th>
                <th>Unit</th>
                <th>Current stock</th>
                <th>Minimum stock</th>
                <th>Price per unit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplies as $index => $supply)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $supply->supply_name }}</td>
                <td>{{ $supply->unit }}</td>
                <td>{{ $supply->current_stock }}</td>
                <td>{{ $supply->minimum_stock }}</td>
                <td>₱{{ number_format($supply->price_per_unit, 2) }}</td>
                <td>
                    @if($supply->isLowStock())
                        <span class="status-badge status-cancelled">Low Stock</span>
                    @else
                        <span class="status-badge status-confirmed">In Stock</span>
                    @endif
                </td>
                <td>
                    <button class="btn-status" onclick="openRestockModal(
                        '{{ $supply->supply_id }}',
                        '{{ $supply->supply_name }}',
                        '{{ $supply->unit }}'
                    )">
                        <i class="ti ti-plus"></i> Restock
                    </button>
                    @if($currentUser->hasPermission('inventory', 'edit'))
                    <button class="btn-edit" onclick="openEditModal(
                        '{{ $supply->supply_id }}',
                        '{{ $supply->supply_name }}',
                        '{{ $supply->unit }}',
                        '{{ $supply->current_stock }}',
                        '{{ $supply->minimum_stock }}',
                        '{{ $supply->price_per_unit }}'
                    )">
                        <i class="ti ti-edit"></i> Edit
                    </button>
                    @endif
                    @if($currentUser->hasPermission('inventory', 'delete'))
                    <form method="POST"
                          action="{{ route('admin.inventory.destroy', $supply->supply_id) }}"
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
                <td colspan="8" class="empty-row">No supplies found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Add supply</h6>
            <button class="modal-close" onclick="closeModal('addModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.inventory.store') }}">
            @csrf
            <div class="form-group">
                <label>Supply name</label>
                <input type="text" name="supply_name" required
                       placeholder="e.g. Engine Oil">
            </div>
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" required
                       placeholder="e.g. liters, pcs, kg">
            </div>
            <div class="form-group">
                <label>Current stock</label>
                <input type="number" name="current_stock" required
                       min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Minimum stock
                    <span style="color:#aaa;font-size:11px;">(low stock alert threshold)</span>
                </label>
                <input type="number" name="minimum_stock" required
                       min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Price per unit (₱)</label>
                <input type="number" name="price_per_unit" required
                       min="0" step="0.01" placeholder="0.00">
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
            <h6>Edit supply</h6>
            <button class="modal-close" onclick="closeModal('editModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="editForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Supply name</label>
                <input type="text" name="supply_name" id="edit_supply_name" required>
            </div>
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" id="edit_unit" required>
            </div>
            <div class="form-group">
                <label>Current stock</label>
                <input type="number" name="current_stock" id="edit_current_stock"
                       required min="0" step="0.01">
            </div>
            <div class="form-group">
                <label>Minimum stock</label>
                <input type="number" name="minimum_stock" id="edit_minimum_stock"
                       required min="0" step="0.01">
            </div>
            <div class="form-group">
                <label>Price per unit (₱)</label>
                <input type="number" name="price_per_unit" id="edit_price_per_unit"
                       required min="0" step="0.01">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal-overlay" id="restockModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Restock — <span id="restock_name"></span></h6>
            <button class="modal-close" onclick="closeModal('restockModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" id="restockForm" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Quantity to add (<span id="restock_unit"></span>)</label>
                <input type="number" name="quantity" required
                       min="0.01" step="0.01" placeholder="0.00">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('restockModal')">Cancel</button>
                <button type="submit" class="btn-primary">Restock</button>
            </div>
        </form>
    </div>
</div>

<!-- RECORD USAGE MODAL -->
<div class="modal-overlay" id="usageModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6>Record supply usage</h6>
            <button class="modal-close" onclick="closeModal('usageModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.inventory.usage') }}">
            @csrf
            <div class="form-group">
                <label>Supply</label>
                <select name="supply_id" required>
                    <option value="">Select supply</option>
                    @foreach($supplies as $supply)
                        <option value="{{ $supply->supply_id }}">
                            {{ $supply->supply_name }}
                            ({{ $supply->current_stock }} {{ $supply->unit }} available)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Quantity used</label>
                <input type="number" name="quantity_used" required
                       min="0.01" step="0.01" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Linked repair order
                    <span style="color:#aaa;font-size:11px;">(optional)</span>
                </label>
                <select name="order_no">
                    <option value="">None</option>
                    @foreach($repairOrders as $order)
                        <option value="{{ $order->order_no }}">
                            #ORD-{{ str_pad($order->order_no, 3, '0', STR_PAD_LEFT) }}
                            — {{ $order->date_of_service }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Notes
                    <span style="color:#aaa;font-size:11px;">(optional)</span>
                </label>
                <input type="text" name="notes" placeholder="Additional notes">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary"
                        onclick="closeModal('usageModal')">Cancel</button>
                <button type="submit" class="btn-primary">Record</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openEditModal(id, name, unit, currentStock, minStock, price) {
    document.getElementById('edit_supply_name').value    = name;
    document.getElementById('edit_unit').value           = unit;
    document.getElementById('edit_current_stock').value  = currentStock;
    document.getElementById('edit_minimum_stock').value  = minStock;
    document.getElementById('edit_price_per_unit').value = price;
    document.getElementById('editForm').action           = `/admin/inventory/${id}`;
    openModal('editModal');
}

function openRestockModal(id, name, unit) {
    document.getElementById('restock_name').innerText = name;
    document.getElementById('restock_unit').innerText = unit;
    document.getElementById('restockForm').action     = `/admin/inventory/${id}/restock`;
    openModal('restockModal');
}

function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>
@endsection