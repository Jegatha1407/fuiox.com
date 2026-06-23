@extends('layouts.app')
@section('title', $appInfo['name'] . ' Access')
@section('page_title', $appInfo['icon'] . ' Manage Access')

@push('styles')
<style>
.aac-card { background:#fff; border:1.5px solid #e5e9f0; border-radius:14px; padding:18px 20px; margin-bottom:12px; }
.aac-name { font-size:14.5px; font-weight:800; color:#1a1a2e; }
.aac-email { font-size:12px; color:#888; margin-top:1px; }
.aac-perm-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-top:1px solid #f0f0f5; }
.aac-perm-row:first-of-type { border-top:1px solid #f0f0f5; margin-top:12px; }
.aac-perm-label { font-size:13px; color:#444; display:flex; align-items:center; gap:8px; }
.aac-switch { position:relative; width:42px; height:24px; flex-shrink:0; }
.aac-switch input { opacity:0; width:0; height:0; }
.aac-slider { position:absolute; cursor:pointer; inset:0; background:#ddd; border-radius:24px; transition:.2s; }
.aac-slider:before { content:''; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
.aac-switch input:checked + .aac-slider { background:#25d366; }
.aac-switch input:checked + .aac-slider:before { transform:translateX(18px); }
.aac-switch input:disabled + .aac-slider { opacity:.4; cursor:not-allowed; }
.aac-master { display:flex; align-items:center; gap:12px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('apps.builder', $appType) }}" class="text-decoration-none" style="font-size:13px;color:#888;"><i class="bi bi-arrow-left me-1"></i> Back to Flow Builder</a>
    <button class="btn btn-fu-primary rounded-3 px-4" onclick="document.getElementById('aacAddModal').style.display='flex';"><i class="bi bi-person-plus me-1"></i> Add Employee</button>
</div>

<div id="aacAddModal" style="display:none;position:fixed;inset:0;background:rgba(20,20,30,0.55);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:26px;width:420px;max-width:92vw;">
        <div style="font-size:17px;font-weight:800;margin-bottom:16px;">Add Employee</div>
        <div class="mb-3">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">Name</label>
            <input type="text" id="aacName" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">Email</label>
            <input type="email" id="aacEmail" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="off">
        </div>
        <div class="mb-4">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">Password</label>
            <input type="password" id="aacPassword" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="new-password">
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="document.getElementById('aacAddModal').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:#f5f5f5;cursor:pointer;font-family:inherit;">Cancel</button>
            <button onclick="aacAddEmployee()" style="flex:1;padding:11px;border:none;border-radius:9px;background:#25d366;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;">Add</button>
        </div>
    </div>
</div>

<div id="aacEditModal" style="display:none;position:fixed;inset:0;background:rgba(20,20,30,0.55);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:26px;width:420px;max-width:92vw;">
        <div style="font-size:17px;font-weight:800;margin-bottom:16px;">Edit Employee</div>
        <input type="hidden" id="aacEditId">
        <div class="mb-3">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">Name</label>
            <input type="text" id="aacEditName" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">Email</label>
            <input type="email" id="aacEditEmail" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="off">
        </div>
        <div class="mb-4">
            <label class="fu-label" style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;display:block;">New Password <span style="font-weight:400;text-transform:none;">(leave blank to keep current)</span></label>
            <input type="password" id="aacEditPassword" class="fu-inp" style="width:100%;padding:10px 13px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;" autocomplete="new-password">
        </div>
        <div style="display:flex;gap:10px;">
            <button onclick="document.getElementById('aacEditModal').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:#f5f5f5;cursor:pointer;font-family:inherit;">Cancel</button>
            <button onclick="aacSaveEdit()" style="flex:1;padding:11px;border:none;border-radius:9px;background:#25d366;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;">Save</button>
        </div>
    </div>
</div>

@forelse($teamMembers as $member)
    @php
        $assignment = $assignments[$member->id] ?? null;
        $hasAccess = $assignment !== null;
    @endphp
    <div class="aac-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="aac-name">{{ $member->name ?? $member->email }}</div>
                <div class="aac-email">{{ $member->email }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm rounded-3" style="background:#f5f5f5;" onclick='aacOpenEdit({{ $member->id }}, @json($member->name), @json($member->email))'><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm rounded-3" style="background:#fdecea;color:#c62828;" onclick="aacDeleteEmployee({{ $member->id }})"><i class="bi bi-trash"></i></button>
                <label class="aac-switch aac-master">
                    <input type="checkbox" {{ $hasAccess ? 'checked' : '' }} onchange="aacToggleMaster({{ $member->id }}, this.checked)">
                    <span class="aac-slider"></span>
                </label>
            </div>
        </div>

        <div id="aac-perms-{{ $member->id }}" style="{{ $hasAccess ? '' : 'display:none;' }}">
            <div class="aac-perm-row">
                <span class="aac-perm-label"><i class="bi bi-diagram-3"></i> Flow Builder</span>
                <label class="aac-switch">
                    <input type="checkbox" {{ ($assignment->can_flow_builder ?? 1) ? 'checked' : '' }} onchange="aacTogglePerm({{ $member->id }}, 'flow_builder', this.checked)">
                    <span class="aac-slider"></span>
                </label>
            </div>
            <div class="aac-perm-row">
                <span class="aac-perm-label"><i class="bi bi-people"></i> Manage {{ $config['resource_label'] ?? 'Resource' }}s</span>
                <label class="aac-switch">
                    <input type="checkbox" {{ ($assignment->can_resources ?? 1) ? 'checked' : '' }} onchange="aacTogglePerm({{ $member->id }}, 'resources', this.checked)">
                    <span class="aac-slider"></span>
                </label>
            </div>
            <div class="aac-perm-row">
                <span class="aac-perm-label"><i class="bi bi-calendar-check"></i> Appointments / Records</span>
                <label class="aac-switch">
                    <input type="checkbox" {{ ($assignment->can_records ?? 1) ? 'checked' : '' }} onchange="aacTogglePerm({{ $member->id }}, 'records', this.checked)">
                    <span class="aac-slider"></span>
                </label>
            </div>
        </div>
    </div>
@empty
    <div class="text-center text-muted py-5">No team members yet. Add staff under Team Settings to grant them access here.</div>
@endforelse

</div>
@endsection

@push('scripts')
<script>
const AACT = document.querySelector('meta[name=csrf-token]').content;
const AACT_APP = '{{ $appType }}';

function aacToggleMaster(staffId, enabled){
    fetch(`/apps/${AACT_APP}/access/toggle`, {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':AACT},
        body: JSON.stringify({staff_user_id: staffId, enabled: enabled})
    }).then(r=>r.json()).then(d=>{
        if(d.success){
            const perms = document.getElementById('aac-perms-'+staffId);
            if(perms) perms.style.display = enabled ? 'block' : 'none';
        }
    });
}

function aacTogglePerm(staffId, page, enabled){
    fetch(`/apps/${AACT_APP}/access/permission`, {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':AACT},
        body: JSON.stringify({staff_user_id: staffId, page: page, enabled: enabled})
    });
}

async function aacAddEmployee(){
    const name = document.getElementById('aacName').value.trim();
    const email = document.getElementById('aacEmail').value.trim();
    const password = document.getElementById('aacPassword').value;
    if(!name || !email || !password){ await fuAlert('Please fill in all fields', {danger:true}); return; }

    const res = await fetch(`/apps/${AACT_APP}/access/add-employee`, {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':AACT},
        body: JSON.stringify({name, email, password})
    });
    const d = await res.json();
    if(d.success){
        location.reload();
    } else {
        await fuAlert(d.error || 'Failed to add employee', {danger:true});
    }
}

function aacOpenEdit(id, name, email){
    document.getElementById('aacEditId').value = id;
    document.getElementById('aacEditName').value = name;
    document.getElementById('aacEditEmail').value = email;
    document.getElementById('aacEditPassword').value = '';
    document.getElementById('aacEditModal').style.display = 'flex';
}

async function aacSaveEdit(){
    const id = document.getElementById('aacEditId').value;
    const name = document.getElementById('aacEditName').value.trim();
    const email = document.getElementById('aacEditEmail').value.trim();
    const password = document.getElementById('aacEditPassword').value;
    if(!name || !email){ await fuAlert('Name and email are required', {danger:true}); return; }

    const payload = {name, email};
    if(password) payload.password = password;

    const res = await fetch(`/apps/${AACT_APP}/access/employee/${id}`, {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':AACT},
        body: JSON.stringify(payload)
    });
    const d = await res.json();
    if(d.success){
        location.reload();
    } else {
        await fuAlert(d.error || 'Failed to update employee', {danger:true});
    }
}

async function aacDeleteEmployee(id){
    const ok = await fuConfirm('Remove this employee? They will lose access to all pages in this app.', {confirmLabel:'Remove', danger:true});
    if(!ok) return;
    const res = await fetch(`/apps/${AACT_APP}/access/employee/${id}`, {
        method:'DELETE', credentials:'same-origin',
        headers:{'X-CSRF-TOKEN':AACT}
    });
    const d = await res.json();
    if(d.success){
        location.reload();
    } else {
        await fuAlert(d.error || 'Failed to remove employee', {danger:true});
    }
}
</script>
@endpush
