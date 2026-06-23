@extends('layouts.app')
@section('title', $appInfo['name'] . ' Appointments')
@section('page_title', $appInfo['icon'] . ' Appointments')

@push('styles')
<style>
.aa-table { width:100%; border-collapse:separate; border-spacing:0 8px; }
.aa-row { background:#fff; }
.aa-row td { padding:14px 16px; border-top:1.5px solid #e5e9f0; border-bottom:1.5px solid #e5e9f0; font-size:13.5px; }
.aa-row td:first-child { border-left:1.5px solid #e5e9f0; border-radius:10px 0 0 10px; }
.aa-row td:last-child { border-right:1.5px solid #e5e9f0; border-radius:0 10px 10px 0; }
.aa-status { font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-block; }
.aa-status.confirmed { background:#e8f5e9; color:#2e7d32; }
.aa-status.completed { background:#e3f2fd; color:#1565c0; }
.aa-status.cancelled { background:#fdecea; color:#c62828; }
.aa-name { font-weight:700; color:#1a1a2e; }
.aa-sub { font-size:11.5px; color:#888; margin-top:2px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

<div class="mb-4">
    <a href="{{ route('apps.builder', $appType) }}" class="text-decoration-none" style="font-size:13px;color:#888;"><i class="bi bi-arrow-left me-1"></i> Back to Flow Builder</a>
</div>

@if($appointments->isEmpty())
<div class="text-center text-muted py-5">No appointments booked yet.</div>
@else
<div style="overflow-x:auto;">
<table class="aa-table">
    <thead>
        <tr style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.4px;">
            <th class="text-start ps-3">#</th>
            <th class="text-start">Patient</th>
            <th class="text-start">{{ $label }}</th>
            <th class="text-start">Department</th>
            <th class="text-start">Date & Time</th>
            <th class="text-start">Status</th>
            <th class="text-start">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($appointments as $a)
        <tr class="aa-row">
            <td>
                <span style="font-weight:800;color:#888;">#{{ $a->booking_number ?? $a->id }}</span>
            </td>
            <td>
                <div class="aa-name">{{ $a->patient_name }}</div>
                <div class="aa-sub">{{ $a->patient_phone }}</div>
            </td>
            <td>{{ $a->resource_name ?? '—' }}</td>
            <td>{{ $a->department ?? '—' }}</td>
            <td>
                <div>{{ \Carbon\Carbon::parse($a->appointment_date)->format('d M Y') }}</div>
                <div class="aa-sub">{{ $a->appointment_time }}</div>
            </td>
            <td><span class="aa-status {{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
            <td>
                @if($a->status === 'confirmed')
                <button class="btn btn-sm rounded-3" style="background:#e3f2fd;color:#1565c0;" onclick="aaComplete({{ $a->id }})">Mark Done</button>
                <button class="btn btn-sm rounded-3" style="background:#fdecea;color:#c62828;" onclick="aaCancel({{ $a->id }})">Cancel</button>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
const AAT = document.querySelector('meta[name=csrf-token]').content;
const AAT_APP = '{{ $appType }}';

async function aaCancel(id){
    const ok = await fuConfirm('Cancel this appointment? The time slot will become available again.', {confirmLabel:'Cancel Appointment', danger:true});
    if(!ok) return;
    const res = await fetch(`/apps/${AAT_APP}/appointments/${id}/cancel`, {method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN':AAT}});
    const d = await res.json();
    if(d.success) location.reload();
}
function aaComplete(id){
    fetch(`/apps/${AAT_APP}/appointments/${id}/complete`, {method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN':AAT}})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>
@endpush
