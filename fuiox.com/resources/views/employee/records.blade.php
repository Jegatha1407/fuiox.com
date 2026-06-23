@extends('employee.layout')
@section('title', $appInfo['name'] . ' ' . $config['record_label'] . 's')

@push('styles')
<style>
.aa-table { width:100%; border-collapse:separate; border-spacing:0 8px; }
.aa-row td { padding:14px 16px; border-top:1.5px solid #e5e9f0; border-bottom:1.5px solid #e5e9f0; font-size:13.5px; background:#fff; }
.aa-row td:first-child { border-left:1.5px solid #e5e9f0; border-radius:10px 0 0 10px; }
.aa-row td:last-child { border-right:1.5px solid #e5e9f0; border-radius:0 10px 10px 0; }
.aa-status { font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-block; }
.aa-status.new { background:#e8f5e9; color:#2e7d32; }
.aa-status.completed { background:#e3f2fd; color:#1565c0; }
.aa-status.cancelled { background:#fdecea; color:#c62828; }
.aa-name { font-weight:700; color:#1a1a2e; }
.aa-sub { font-size:11.5px; color:#888; margin-top:2px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

<div class="d-flex justify-content-between align-items-center mb-4">

</div>

@if($records->isEmpty())
<div class="text-center text-muted py-5">No {{ strtolower($config['record_label']) }}s yet.</div>
@else
<div style="overflow-x:auto;">
<table class="aa-table">
    <thead>
        <tr style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.4px;">
            <th class="text-start ps-3">#</th>
            <th class="text-start">Customer</th>
            <th class="text-start">{{ $config['resource_label'] }}</th>
            <th class="text-start">Address</th>
            <th class="text-start">Status</th>
            <th class="text-start">Actions</th>
            <th class="text-start">Date</th>
        </tr>
    </thead>
    <tbody>
    @foreach($records as $r)
        <tr class="aa-row">
            <td>
                <span style="font-weight:800;color:#888;">#{{ $r->booking_number ?? $r->id }}</span>
            </td>
            <td>
                <div class="aa-name">{{ $r->customer_name ?? $r->patient_name ?? '—' }}</div>
                <div class="aa-sub">{{ $r->customer_phone ?? $r->patient_phone ?? '—' }}</div>
            </td>
            <td>{{ $r->resource_name ?? '—' }}</td>
            <td>{{ $r->customer_address ?? $r->department ?? '—' }}</td>
            <td><span class="aa-status {{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
            <td>
                @if(($r->status ?? '') === 'confirmed')
                <button class="btn btn-sm rounded-3" style="background:#e3f2fd;color:#1565c0;" onclick="aaComplete({{ $r->id }})">Mark Done</button>
                <button class="btn btn-sm rounded-3" style="background:#fdecea;color:#c62828;" onclick="aaCancel({{ $r->id }})">Cancel</button>
                @endif
            </td>
            <td>{{ \Carbon\Carbon::parse($r->created_at ?? $r->appointment_date)->format('d M Y, h:i A') }}</td>
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
