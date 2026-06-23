@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .stat-card          { border-left: 4px solid #25d366; border-radius: 12px; }
    .stat-card.blue     { border-left-color: #1976d2; }
    .stat-card.orange   { border-left-color: #f57c00; }
    .stat-card.red      { border-left-color: #e53935; }
    .stat-card:hover    { box-shadow: 0 6px 20px rgba(0,0,0,.09) !important; transform: translateY(-2px); transition: .2s; }
    .stat-num           { font-size: 2rem; font-weight: 800; color: #1a1a2e; }

    .form-check-input:checked { background-color: #25d366; border-color: #25d366; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-lg-4 py-3">

    {{-- ══ STAT CARDS ══ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100 p-3">
                <div class="text-uppercase text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">Total Users</div>
                <div class="stat-num mt-1">{{ $users->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card blue shadow-sm h-100 p-3">
                <div class="text-uppercase text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">Online Now</div>
                <div class="stat-num mt-1">{{ $users->where('is_online', true)->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card orange shadow-sm h-100 p-3">
                <div class="text-uppercase text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">On Trial</div>
                <div class="stat-num mt-1">
                    {{ $users->filter(fn($u) => $u->trial_ends_at && \Carbon\Carbon::parse($u->trial_ends_at)->isFuture())->count() }}
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card red shadow-sm h-100 p-3">
                <div class="text-uppercase text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">Blocked</div>
                <div class="stat-num mt-1">{{ $users->where('is_blocked', 1)->count() }}</div>
            </div>
        </div>
    </div>

    {{-- ══ USERS TABLE ══ --}}
    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="fw-bold mb-3">👥 All Users</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="d-none d-md-table-cell">Organisation</th>
                            <th>Status</th>
                            <th class="d-none d-lg-table-cell">Trial</th>
                            <th>Free Trial</th>
                            <th class="d-none d-lg-table-cell">Last Seen</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr id="urow_{{ $u->id }}">
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td class="text-muted small">{{ $u->email }}</td>
                            <td class="d-none d-md-table-cell small">{{ $u->organisation ?? '—' }}</td>
                            <td>
                                @if($u->is_blocked)
                                    <span class="badge rounded-pill bg-danger">🚫 Blocked</span>
                                @elseif(!$u->is_active)
                                    <span class="badge rounded-pill bg-warning text-dark">⏸ Inactive</span>
                                @elseif($u->is_online)
                                    <span class="badge rounded-pill bg-success">● Online</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary">○ Offline</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell small">
                                @if($u->trial_ends_at && \Carbon\Carbon::parse($u->trial_ends_at)->isFuture())
                                    <span class="text-primary fw-semibold">
                                        ⏱ {{ \Carbon\Carbon::now()->diffInDays($u->trial_ends_at) }}d left
                                    </span>
                                @elseif($u->trial_ends_at)
                                    <span class="text-danger">Expired</span>
                                @else
                                    <span class="text-muted">No trial</span>
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch mb-0">
                                    @php
                                        $inTrial = $u->free_trial_enabled && $u->trial_ends_at && \Carbon\Carbon::parse($u->trial_ends_at)->isFuture();
                                    @endphp
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ $inTrial ? 'checked' : '' }}
                                        onchange="adminToggleTrial({{ $u->id }}, this)">
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell text-muted small">
                                {{ $u->last_seen ? \Carbon\Carbon::parse($u->last_seen)->diffForHumans() : 'Never' }}
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('admin.users.edit', $u->id) }}"
                                       class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($u->is_active)
                                        <button onclick="adminPost('/admin/users/{{ $u->id }}/active', {active: 0})"
                                                class="btn btn-sm btn-outline-warning py-1 px-2" title="Deactivate">
                                            <i class="bi bi-pause-fill"></i>
                                        </button>
                                    @else
                                        <button onclick="adminPost('/admin/users/{{ $u->id }}/active', {active: 1})"
                                                class="btn btn-sm btn-outline-success py-1 px-2" title="Activate">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    @endif
                                    @if($u->is_blocked)
                                        <button onclick="adminPost('/admin/users/{{ $u->id }}/block', {blocked: 0})"
                                                class="btn btn-sm btn-outline-success py-1 px-2" title="Unblock">
                                            <i class="bi bi-unlock-fill"></i>
                                        </button>
                                    @else
                                        <button onclick="confirmBlock({{ $u->id }})"
                                                class="btn btn-sm btn-outline-danger py-1 px-2" title="Block">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No users yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmBlock(id) {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div id="blockOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
            <div style="background:#fff;border-radius:16px;padding:28px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;">
                <div style="width:56px;height:56px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-slash-circle-fill" style="font-size:24px;color:#e53935;"></i>
                </div>
                <div style="font-size:18px;font-weight:700;color:#1a1a2e;margin-bottom:8px;">Block this user?</div>
                <div style="font-size:13px;color:#888;margin-bottom:24px;">The user will be immediately locked out and unable to access the platform.</div>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button onclick="document.getElementById('blockOverlay').remove()"
                            class="btn btn-light px-4">Cancel</button>
                    <button onclick="document.getElementById('blockOverlay').remove(); adminPost('/admin/users/'+${id}+'/block', {blocked: 1})"
                            class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-slash-circle-fill me-1"></i>Yes, Block
                    </button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal.firstElementChild);
}
</script>

{{-- Trial Confirm Modal --}}
<div id="trialConfirmModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:20px;padding:0;max-width:440px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.25);overflow:hidden;">
        <div style="background:linear-gradient(135deg,#fff3e0,#ffe0b2);padding:28px 32px 20px;text-align:center;border-bottom:1px solid #ffe0b2;">
            <div style="width:64px;height:64px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:28px;box-shadow:0 4px 16px rgba(245,124,0,0.2);">⚠️</div>
            <h5 style="font-weight:800;color:#e65100;margin:0 0 6px;font-size:18px;">Grant Free Trial?</h5>
            <p style="font-size:13px;color:#bf360c;margin:0;font-weight:500;">This action will override the current paid plan</p>
        </div>
        <div style="padding:24px 32px;">
            <p id="trialConfirmMsg" style="font-size:14px;color:#555;margin-bottom:20px;line-height:1.6;text-align:center;"></p>
            <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:10px;padding:12px 16px;margin-bottom:24px;font-size:13px;color:#f57c00;">
                <i class="bi bi-info-circle-fill me-2"></i>The free trial will be active for <strong>30 days</strong> from today.
            </div>
            <div style="display:flex;gap:12px;">
                <button onclick="adminCancelTrial()" style="flex:1;padding:13px;border:1.5px solid #e5e5e5;border-radius:12px;background:#f9f9f9;font-size:14px;font-weight:600;cursor:pointer;color:#555;transition:0.15s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#f9f9f9'">
                    ✕ Cancel
                </button>
                <button onclick="adminConfirmTrial()" style="flex:1;padding:13px;border:none;border-radius:12px;background:linear-gradient(135deg,#25d366,#1aaa50);color:#fff;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(37,211,102,0.3);transition:0.15s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    ✅ Yes, Grant Trial
                </button>
            </div>
        </div>
    </div>
</div>
<script>
let trialPendingId=null,trialPendingToggle=null;
function adminToggleTrial(userId,toggle){
    const enabled=toggle.checked?1:0;
    if(!enabled){
        adminPost('/admin/users/'+userId+'/trial',{enabled:0},d=>{
            if(d.success)showToast('✅ '+(d.message||'Done'),'success');
            else{showToast('❌ '+(d.error||'Failed'),'error');toggle.checked=!toggle.checked;}
        });return;
    }
    fetch('/admin/users/'+userId+'/trial',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({enabled:1,confirmed:false})
    }).then(r=>r.json()).then(d=>{
        if(d.already_trial){showToast('❌ '+d.error,'error');toggle.checked=false;return;}
        if(d.needs_confirm){
            trialPendingId=userId;trialPendingToggle=toggle;
            document.getElementById('trialConfirmMsg').textContent=d.warning;
            document.getElementById('trialConfirmModal').style.display='flex';
            return;
        }
        if(d.success){showToast('✅ '+(d.message||'Done'),'success');setTimeout(()=>location.reload(),1200);}
        else{showToast('❌ '+(d.error||'Failed'),'error');toggle.checked=false;}
    }).catch(()=>{showToast('❌ Network error','error');toggle.checked=false;});
}
function adminCancelTrial(){
    document.getElementById('trialConfirmModal').style.display='none';
    if(trialPendingToggle)trialPendingToggle.checked=false;
    trialPendingId=null;trialPendingToggle=null;
}
function adminConfirmTrial(){
    document.getElementById('trialConfirmModal').style.display='none';
    fetch('/admin/users/'+trialPendingId+'/trial',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({enabled:1,confirmed:true})
    }).then(r=>r.json()).then(d=>{
        if(d.success){showToast('✅ '+(d.message||'Done'),'success');setTimeout(()=>location.reload(),1200);}
        else{showToast('❌ '+(d.error||'Failed'),'error');if(trialPendingToggle)trialPendingToggle.checked=false;}
        trialPendingId=null;trialPendingToggle=null;
    }).catch(()=>showToast('❌ Network error','error'));
}
</script>
@endpush