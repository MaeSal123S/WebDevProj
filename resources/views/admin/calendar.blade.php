@extends('admin.layouts.app')
@section('content')

<div class="page-header">
    <div>
        <div class="section-title">Appointments</div>
        <h5 class="page-heading">Calendar</h5>
    </div>
    <a href="{{ route('admin.appointments.index') }}" class="btn-secondary">
        <i class="ti ti-list"></i> View List
    </a>
</div>

<div class="panel" style="height: calc(100vh - 160px); display:flex; flex-direction:column;">
    <div id="calendar" style="flex:1; min-height:0;"></div>
</div>

<!-- Appointment Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box" style="width:400px">
        <div class="modal-header">
            <h6>Appointment Details</h6>
            <button class="modal-close" onclick="closeModal('detailModal')">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div style="padding:16px 20px;">
            <div class="detail-row">
                <span class="detail-label">Customer</span>
                <span id="detail_customer" class="detail-value"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Service</span>
                <span id="detail_service" class="detail-value"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Advisor</span>
                <span id="detail_advisor" class="detail-value"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span id="detail_status" class="detail-value"></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span id="detail_notes" class="detail-value"></span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary"
                    onclick="closeModal('detailModal')">Close</button>
            <a href="{{ route('admin.appointments.index') }}"
               class="btn-primary">Manage</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: '100%',
        dayMaxEvents: true,           // show "+X more" link instead of clipping
        eventMaxStack: 3,             // max visible events per day before collapsing
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: {
            hour:   'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        events: "{{ route('admin.appointments.data') }}",
        eventClick: function(info) {
            const props = info.event.extendedProps;
            document.getElementById('detail_customer').innerText = info.event.title;
            document.getElementById('detail_service').innerText  = props.service;
            document.getElementById('detail_advisor').innerText  = props.advisor;
            document.getElementById('detail_status').innerText   = props.status;
            document.getElementById('detail_notes').innerText    = props.notes;
            openModal('detailModal');
        },
        eventDidMount: function(info) {
            // Ensure event text is never clipped — allow wrapping
            info.el.style.whiteSpace = 'normal';
            info.el.style.overflow   = 'visible';
        },
    });

    calendar.render();
});
</script>
@endsection