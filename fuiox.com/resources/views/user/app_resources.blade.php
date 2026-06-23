@extends('layouts.app')
@section('title', $appInfo['name'] . ' ' . $label . 's')
@section('page_title', $appInfo['icon'] . ' ' . $label . ' Management')

@push('styles')
<style>
.ar-card { background:#fff; border:1.5px solid #e5e9f0; border-radius:14px; padding:18px 20px; margin-bottom:12px; display:flex; align-items:center; gap:16px; }
.ar-card.inactive { opacity:.55; }
.ar-avatar { width:46px; height:46px; border-radius:12px; background:{{ $appInfo['color'] }}; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.ar-name { font-size:15px; font-weight:800; color:#1a1a2e; }
.ar-cat { font-size:12px; color:#888; margin-top:1px; }
.ar-meta { font-size:11.5px; color:#555; margin-top:4px; }
.fu-label { font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; display:block; }
.fu-inp { width:100%; padding:10px 13px; border:1.5px solid #e5e9f0; border-radius:9px; font-size:14px; outline:none; font-family:inherit; }
.fu-inp:focus { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.1); }
.ar-date-chip { display:inline-flex; align-items:center; gap:6px; background:#f0f9f4; border:1.5px solid #25d366; color:#1a7a3d; padding:5px 10px; border-radius:20px; font-size:12px; margin:3px 4px 0 0; }
.ar-date-chip button { background:none; border:none; color:#1a7a3d; cursor:pointer; font-size:13px; padding:0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('apps.builder', $appType) }}" class="text-decoration-none" style="font-size:13px;color:#888;"><i class="bi bi-arrow-left me-1"></i> Back to Flow Builder</a>
    <button class="btn btn-fu-primary rounded-3 px-4" onclick="arOpenAdd()"><i class="bi bi-plus-lg me-1"></i> Add {{ $label }}</button>
</div>

<div id="arList">
@forelse($items as $item)
    <div class="ar-card {{ $item->is_active ? '' : 'inactive' }}" id="ar-{{ $item->id }}">
        <div class="ar-avatar">{{ $config['resource_icon'] }}</div>
        <div style="flex:1;">
            <div class="ar-name">{{ $item->name }}</div>
            @if($item->category)<div class="ar-cat">{{ $item->category }}</div>@endif
            @if($config['is_time_based'])
                @if($item->slots)<div class="ar-meta"><i class="bi bi-clock me-1"></i>{{ $item->slots }}</div>@endif
                @if($item->available_dates)<div class="ar-meta"><i class="bi bi-calendar3 me-1"></i>{{ count(json_decode($item->available_dates, true) ?: []) }} dates configured</div>@endif
            @else
                @if($item->price)<div class="ar-meta"><i class="bi bi-tag me-1"></i>₹{{ $item->price }}</div>@endif
                @if($item->description)<div class="ar-meta">{{ \Illuminate\Support\Str::limit($item->description, 60) }}</div>@endif
            @endif
        </div>
        <button class="btn btn-sm rounded-3" style="background:#f5f5f5;"
            onclick='arOpenEdit(@json($item))'><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm rounded-3" style="background:{{ $item->is_active ? '#fdecea' : '#e8f5e9' }};color:{{ $item->is_active ? '#c62828' : '#2e7d32' }};" onclick="arToggle({{ $item->id }})">{{ $item->is_active ? 'Pause' : 'Activate' }}</button>
        <button class="btn btn-sm rounded-3" style="background:#fdecea;color:#c62828;" onclick="arDelete({{ $item->id }})"><i class="bi bi-trash"></i></button>
    </div>
@empty
    <div class="text-center text-muted py-5">No {{ strtolower($label) }}s added yet. Click "Add {{ $label }}" to get started.</div>
@endforelse
</div>

</div>

<div id="arModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;padding:20px 0;">
    <div style="background:#fff;border-radius:18px;padding:26px;width:460px;max-width:92vw;max-height:90vh;overflow-y:auto;">
        <div style="font-size:17px;font-weight:800;margin-bottom:16px;" id="arModalTitle">Add {{ $label }}</div>
        <input type="hidden" id="arId">
        <div class="mb-3">
            <label class="fu-label">{{ $label }} Name *</label>
            <input type="text" id="arName" class="fu-inp" placeholder="e.g. Dr. Kumar" autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="fu-label">Category{{ $appType === 'hospital' ? ' / Department' : '' }}</label>
            <input type="text" id="arCategory" class="fu-inp" placeholder="e.g. Cardiology" autocomplete="off">
        </div>

        @if($config['is_time_based'])
        <div class="mb-3">
            <label class="fu-label">Available Dates</label>
            <input type="date" id="arDateInput" class="fu-inp" style="margin-bottom:8px;">
            <button type="button" onclick="arAddDate()" style="padding:8px 14px;border:1px dashed #25d366;background:none;color:#25d366;border-radius:8px;cursor:pointer;font-size:12px;font-family:inherit;">+ Add Date</button>
            <div id="arDateChips" style="margin-top:8px;"></div>
        </div>
        <div class="mb-4">
            <label class="fu-label">Available Time Slots — comma separated (applies to all selected dates)</label>
            <input type="text" id="arSlots" class="fu-inp" placeholder="10:00 AM, 11:30 AM, 3:00 PM" autocomplete="off">
        </div>
        @else
        <div class="mb-3">
            <label class="fu-label">Price</label>
            <input type="text" id="arPrice" class="fu-inp" placeholder="e.g. 299" autocomplete="off">
        </div>
        <div class="mb-4">
            <label class="fu-label">Description</label>
            <textarea id="arDescription" class="fu-inp" rows="3" placeholder="Short description shown to customers"></textarea>
        </div>
        @endif

        <div style="display:flex;gap:10px;">
            <button onclick="document.getElementById('arModalOverlay').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:#f5f5f5;cursor:pointer;font-family:inherit;">Cancel</button>
            <button onclick="arSave()" style="flex:1;padding:11px;border:none;border-radius:9px;background:#25d366;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;">Save</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ART = document.querySelector('meta[name=csrf-token]').content;
const ART_APP = '{{ $appType }}';
const ART_IS_TIME_BASED = @json($config['is_time_based']);
let arSelectedDates = [];

function arRenderDateChips(){
    const c = document.getElementById('arDateChips');
    if(!c) return;
    c.innerHTML = arSelectedDates.map((d,i) => `<span class="ar-date-chip">${d}<button type="button" onclick="arRemoveDate(${i})">✕</button></span>`).join('');
}
function arAddDate(){
    const val = document.getElementById('arDateInput').value;
    if(!val) return;
    if(!arSelectedDates.includes(val)) arSelectedDates.push(val);
    arSelectedDates.sort();
    arRenderDateChips();
    document.getElementById('arDateInput').value = '';
}
function arRemoveDate(i){
    arSelectedDates.splice(i, 1);
    arRenderDateChips();
}

function arOpenAdd(){
    document.getElementById('arModalTitle').textContent = 'Add {{ $label }}';
    document.getElementById('arId').value = '';
    document.getElementById('arName').value = '';
    document.getElementById('arCategory').value = '';
    if(ART_IS_TIME_BASED){
        document.getElementById('arSlots').value = '';
        arSelectedDates = [];
        arRenderDateChips();
    } else {
        document.getElementById('arPrice').value = '';
        document.getElementById('arDescription').value = '';
    }
    document.getElementById('arModalOverlay').style.display = 'flex';
}

function arOpenEdit(item){
    document.getElementById('arModalTitle').textContent = 'Edit {{ $label }}';
    document.getElementById('arId').value = item.id;
    document.getElementById('arName').value = item.name || '';
    document.getElementById('arCategory').value = item.category || '';
    if(ART_IS_TIME_BASED){
        document.getElementById('arSlots').value = item.slots || '';
        try { arSelectedDates = item.available_dates ? JSON.parse(item.available_dates) : []; } catch(e){ arSelectedDates = []; }
        arRenderDateChips();
    } else {
        document.getElementById('arPrice').value = item.price || '';
        document.getElementById('arDescription').value = item.description || '';
    }
    document.getElementById('arModalOverlay').style.display = 'flex';
}

async function arSave(){
    const id = document.getElementById('arId').value;
    const payload = {
        name: document.getElementById('arName').value.trim(),
        category: document.getElementById('arCategory').value.trim(),
    };
    if(ART_IS_TIME_BASED){
        payload.slots = document.getElementById('arSlots').value.trim();
        payload.available_dates = JSON.stringify(arSelectedDates);
    } else {
        payload.price = document.getElementById('arPrice').value.trim();
        payload.description = document.getElementById('arDescription').value.trim();
    }
    if(!payload.name){ await fuAlert('Name is required', {danger:true}); return; }
    const url = id ? `/apps/${ART_APP}/resources/${id}` : `/apps/${ART_APP}/resources`;
    fetch(url, {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':ART},
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(d=>{
        if(d.success) location.reload();
    });
}
function arToggle(id){
    fetch(`/apps/${ART_APP}/resources/${id}/toggle`, {method:'POST', credentials:'same-origin', headers:{'X-CSRF-TOKEN':ART}})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
async function arDelete(id){
    const ok = await fuConfirm('Delete this {{ strtolower($label) }}?', {confirmLabel:'Delete', danger:true});
    if(!ok) return;
    const res = await fetch(`/apps/${ART_APP}/resources/${id}`, {method:'DELETE', credentials:'same-origin', headers:{'X-CSRF-TOKEN':ART}});
    const d = await res.json();
    if(d.success) location.reload();
}
</script>
@endpush
