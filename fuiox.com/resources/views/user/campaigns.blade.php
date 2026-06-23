@extends('layouts.app')

@section('title', 'Campaigns')
@section('page_title', 'Campaigns')

@section('page_styles')
.camp-stat { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border-left:4px solid #25d366; display:flex; align-items:center; gap:14px; }
.camp-stat.blue   { border-left-color:#1976d2; }
.camp-stat.orange { border-left-color:#f57c00; }
.camp-stat.red    { border-left-color:#e53935; }
.camp-stat-icon   { font-size:26px; }
.camp-stat-label  { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px; }
.camp-stat-value  { font-size:24px; font-weight:800; color:#1a1a2e; }

.camp-table th { font-size:12px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.3px; background:#fafafa; border-bottom:2px solid #f0f0f0; padding:11px 16px; white-space:nowrap; }
.camp-table td { font-size:13px; color:#333; padding:13px 16px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.camp-table tbody tr:hover td { background:#fafafa; }

.camp-status { font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
.camp-status.sent      { background:#e8f5e9; color:#2e7d32; }
.camp-status.scheduled { background:#e3f2fd; color:#1565c0; }
.camp-status.draft     { background:#f5f5f5; color:#666; }
.camp-status.failed    { background:#fdecea; color:#c62828; }
.camp-status.running   { background:#fff3e0; color:#e65100; }

.progress-thin { height:6px; border-radius:3px; background:#f0f0f0; overflow:hidden; min-width:80px; }
.progress-thin .bar { height:100%; background:#25d366; border-radius:3px; transition:width 0.3s; }

.action-btn { width:30px; height:30px; border:none; background:transparent; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px; transition:0.15s; }
.action-btn.view:hover   { background:#e8f5e9; color:#2e7d32; }
.action-btn.delete:hover { background:#fdecea; color:#e53935; }

.empty-state { padding:60px 20px; text-align:center; }
.empty-state-icon { font-size:60px; opacity:0.2; margin-bottom:16px; }
.empty-state p { color:#aaa; font-size:14px; }

/* Template picker in modal */
.tpl-pick-item { border:1.5px solid #e9edef; border-radius:10px; padding:13px 15px; margin-bottom:10px; cursor:pointer; transition:0.15s; }
.tpl-pick-item:hover { border-color:#25d366; background:#f0fdf4; }
.tpl-pick-item.selected { border-color:#25d366; background:#e8f5e9; }
.tpl-pick-name { font-size:14px; font-weight:700; color:#1a1a2e; margin-bottom:3px; }
.tpl-pick-prev { font-size:12px; color:#667781; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Var field */
.var-field label { font-size:12px; font-weight:600; color:#555; margin-bottom:4px; display:block; }
.var-field input { width:100%; padding:9px 12px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:13px; outline:none; transition:0.15s; font-family:inherit; }
.var-field input:focus { border-color:#25d366; }

/* Step wizard */
.step-indicator { display:flex; gap:0; margin-bottom:24px; }
.step-dot { flex:1; text-align:center; position:relative; }
.step-dot::before { content:''; display:block; height:3px; background:#f0f0f0; position:absolute; top:14px; left:50%; right:-50%; }
.step-dot:last-child::before { display:none; }
.step-circle { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; border:2px solid #e0e0e0; background:#fff; color:#aaa; position:relative; z-index:1; }
.step-dot.active .step-circle { border-color:#25d366; background:#25d366; color:#fff; }
.step-dot.done .step-circle { border-color:#25d366; background:#e8f5e9; color:#25d366; }
.step-dot.done::before, .step-dot.active::before { background:#25d366; }
.step-label { font-size:11px; font-weight:600; color:#aaa; margin-top:5px; }
.step-dot.active .step-label, .step-dot.done .step-label { color:#25d366; }
@endsection

@section('content')

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="camp-stat">
            <div class="camp-stat-icon">📢</div>
            <div><div class="camp-stat-label">Total</div><div class="camp-stat-value" id="cStatTotal">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="camp-stat blue">
            <div class="camp-stat-icon">✅</div>
            <div><div class="camp-stat-label">Sent</div><div class="camp-stat-value" id="cStatSent">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="camp-stat orange">
            <div class="camp-stat-icon">📬</div>
            <div><div class="camp-stat-label">Delivered</div><div class="camp-stat-value" id="cStatDelivered">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="camp-stat red">
            <div class="camp-stat-icon">❌</div>
            <div><div class="camp-stat-label">Failed</div><div class="camp-stat-value" id="cStatFailed">—</div></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card fu-card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <span class="me-auto fw-bold">All Campaigns</span>
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
            <input type="text" id="campSearch" class="form-control form-control-sm" placeholder="Search campaigns…"
                style="padding-left:30px;width:200px;border-radius:8px;" oninput="campFilter()">
        </div>
        <select id="campStatusFilter" class="form-select form-select-sm" style="width:130px;border-radius:8px;" onchange="campFilter()">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="scheduled">Scheduled</option>
        </select>
        <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="campOpenCreate()">
            <i class="bi bi-plus-lg me-1"></i>New Campaign
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table camp-table mb-0">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Template</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="campTableBody">
                    <tr><td colspan="8"><div class="empty-state"><div class="empty-state-icon">📢</div><p>Loading campaigns…</p></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Create Campaign Modal (Multi-step) -->
<div class="modal fade" id="campModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">🚀 New Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="campResetWizard()"></button>
            </div>
            <div class="modal-body px-4 pb-0">
                <!-- Step Indicator -->
                <div class="step-indicator" id="campStepIndicator">
                    <div class="step-dot active" id="stepDot1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Details</div>
                    </div>
                    <div class="step-dot" id="stepDot2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Template</div>
                    </div>
                    <div class="step-dot" id="stepDot3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Audience</div>
                    </div>
                    <div class="step-dot" id="stepDot4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>

                <!-- Step 1: Details -->
                <div id="campStep1">
                    <div class="mb-3">
                        <label for="campName" class="form-label fw-semibold" style="font-size:12px;">Campaign Name *</label>
                        <input type="text" id="campName" class="form-control rounded-3" placeholder="e.g. Diwali Offer 2025">
                    </div>
                    <div class="mb-3">
                        <div class="form-label fw-semibold" style="font-size:12px;">Schedule</div>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="campSchedule" id="campNow" value="now" checked onchange="campToggleSchedule()">
                                <label class="form-check-label" for="campNow" style="font-size:13px;">Send Now</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="campSchedule" id="campLater" value="later" onchange="campToggleSchedule()">
                                <label class="form-check-label" for="campLater" style="font-size:13px;">Schedule Later</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="campScheduleAt">
                        <label for="campDateTime" class="form-label fw-semibold" style="font-size:12px;">Schedule Date & Time *</label>
                        <input type="datetime-local" id="campDateTime" class="form-control rounded-3">
                    </div>
                    <div id="campStep1Error" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
                </div>

                <!-- Step 2: Template -->
                <div id="campStep2" style="display:none;">
                    <div class="mb-3">
                        <label for="campTplSearch" class="form-label fw-semibold" style="font-size:12px;">Search Template</label>
                        <input type="text" id="campTplSearch" class="form-control form-control-sm rounded-3" placeholder="Search approved templates…" oninput="campFilterTpls()">
                    </div>
                    <div id="campTplList" style="max-height:300px;overflow-y:auto;">
                        <div class="text-center text-muted py-3">Loading templates…</div>
                    </div>
                    <div id="campVarFields" class="mt-3"></div>
                    <div id="campStep2Error" class="alert alert-danger d-none rounded-3 py-2 mt-2" style="font-size:13px;"></div>
                </div>

                <!-- Step 3: Audience -->
                <div id="campStep3" style="display:none;">
                    <div class="mb-3">
                        <label for="campAudAll" class="form-label fw-semibold" style="font-size:12px;">Send To</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="campAudience" id="campAudAll" value="all" checked onchange="campToggleAudience()">
                                <label class="form-check-label" for="campAudAll" style="font-size:13px;">All Contacts</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="campAudience" id="campAudGroup" value="group" onchange="campToggleAudience()">
                                <label class="form-check-label" for="campAudGroup" style="font-size:13px;">Specific Group</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="campAudience" id="campAudCustom" value="custom" onchange="campToggleAudience()">
                                <label class="form-check-label" for="campAudCustom" style="font-size:13px;">Custom Numbers</label>
                            </div>
                        </div>
                    </div>
                    <div id="campGroupWrap" class="mb-3 d-none">
                        <label for="campGroupSel" class="form-label fw-semibold" style="font-size:12px;">Select Group</label>
                        <select id="campGroupSel" class="form-select rounded-3">
                            <option value="">— Select group —</option>
                        </select>
                    </div>
                    <div id="campCustomWrap" class="mb-3 d-none">
                        <label for="campCustomNums" class="form-label fw-semibold" style="font-size:12px;">Phone Numbers <span class="text-muted fw-normal">(one per line or comma separated)</span></label>
                        <textarea id="campCustomNums" class="form-control rounded-3" rows="5" placeholder="919876543210&#10;919123456789"></textarea>
                    </div>
                    <div class="alert alert-info rounded-3 py-2" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Estimated recipients: <strong id="campRecipientCount">—</strong>
                    </div>
                    <div id="campStep3Error" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
                </div>

                <!-- Step 4: Review -->
                <div id="campStep4" style="display:none;">
                    <div class="alert alert-success rounded-3 py-2 mb-3" style="font-size:13px;">
                        <i class="bi bi-check-circle me-1"></i>Everything looks good! Review and launch your campaign.
                    </div>
                    <div class="card border rounded-3 mb-3">
                        <div class="card-body p-3">
                            <div class="row g-2">
                                <div class="col-6"><div class="text-muted" style="font-size:11px;text-transform:uppercase;font-weight:700;">Campaign</div><div id="revName" class="fw-semibold" style="font-size:14px;"></div></div>
                                <div class="col-6"><div class="text-muted" style="font-size:11px;text-transform:uppercase;font-weight:700;">Schedule</div><div id="revSchedule" class="fw-semibold" style="font-size:14px;"></div></div>
                                <div class="col-6"><div class="text-muted" style="font-size:11px;text-transform:uppercase;font-weight:700;">Template</div><div id="revTemplate" class="fw-semibold" style="font-size:14px;"></div></div>
                                <div class="col-6"><div class="text-muted" style="font-size:11px;text-transform:uppercase;font-weight:700;">Recipients</div><div id="revRecipients" class="fw-semibold" style="font-size:14px;"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 rounded-3 mb-2" style="background:#f0fdf4;border:1px solid #c8e6c9;">
                        <div class="text-muted mb-1" style="font-size:11px;font-weight:700;">TEMPLATE PREVIEW</div>
                        <div id="revPreview" style="font-size:13px;color:#1a1a2e;white-space:pre-wrap;"></div>
                    </div>
                    <div id="campStep4Error" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-3 gap-2">
                <button class="btn btn-light rounded-3 d-none" id="campBtnBack" onclick="campPrevStep()"><i class="bi bi-arrow-left me-1"></i>Back</button>
                <button class="btn btn-fu-primary rounded-3 ms-auto" id="campBtnNext" onclick="campNextStep()">
                    Next <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm -->
<div class="modal fade" id="campDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
                <h6 class="fw-bold mb-2">Delete Campaign?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger rounded-3 px-4" id="campConfirmDelBtn" onclick="campConfirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let campAll = [], campFiltered = [], campTpls = [], campSelTpl = null;
let campStep = 1, campDeleteId = null;
let campModal, campDeleteModal;

document.addEventListener('DOMContentLoaded', () => {
    campModal = new bootstrap.Modal(document.getElementById('campModal'));
    campDeleteModal = new bootstrap.Modal(document.getElementById('campDeleteModal'));
    campLoadStats();
    campLoadCampaigns();
});

/* ── STATS ── */
function campLoadStats() {
    fetch('/campaigns/stats').then(r => r.json()).then(d => {
        document.getElementById('cStatTotal').textContent     = d.total     ?? 0;
        document.getElementById('cStatSent').textContent      = d.sent      ?? 0;
        document.getElementById('cStatDelivered').textContent = d.delivered ?? 0;
        document.getElementById('cStatFailed').textContent    = d.failed    ?? 0;
    }).catch(() => {});
}

/* ── LOAD ── */
function campLoadCampaigns() {
    fetch('/campaigns/list').then(r => r.json()).then(data => {
        campAll = data.campaigns || data || [];
        campFiltered = [...campAll];
        campRenderTable();
    }).catch(() => {
        document.getElementById('campTableBody').innerHTML =
            `<tr><td colspan="8"><div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load campaigns.</p></div></td></tr>`;
    });
}

/* ── FILTER ── */
function campFilter() {
    const q = document.getElementById('campSearch').value.toLowerCase();
    const s = document.getElementById('campStatusFilter').value;
    campFiltered = campAll.filter(c => {
        const mQ = !q || (c.name||'').toLowerCase().includes(q) || (c.template_name||'').toLowerCase().includes(q);
        const mS = !s || c.status === s;
        return mQ && mS;
    });
    campRenderTable();
}

/* ── RENDER ── */
function campRenderTable() {
    const tbody = document.getElementById('campTableBody');
    if (!campFiltered.length) {
        const q = document.getElementById('campSearch').value;
        const s = document.getElementById('campStatusFilter').value;
        const isFiltered = q || s;
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="empty-state-icon">📢</div><p>${isFiltered ? 'No campaigns match your filter.' : 'No campaigns yet.'}</p>${!isFiltered ? '<button class="btn btn-fu-primary btn-sm rounded-pill mt-2" onclick="campOpenCreate()"><i class="bi bi-plus-lg me-1"></i>Create First Campaign</button>' : ''}</div></td></tr>`;
        return;
    }
    const statusMap = {
        sent:      '<span class="camp-status sent"><i class="bi bi-check-circle-fill"></i>Sent</span>',
        completed: '<span class="camp-status sent"><i class="bi bi-check-circle-fill"></i>Completed</span>',
        scheduled: '<span class="camp-status scheduled"><i class="bi bi-clock-fill"></i>Scheduled</span>',
        draft:     '<span class="camp-status draft"><i class="bi bi-pencil-fill"></i>Draft</span>',
        failed:    '<span class="camp-status failed"><i class="bi bi-x-circle-fill"></i>Failed</span>',
        running:   '<span class="camp-status running"><i class="bi bi-play-circle-fill"></i>Running</span>',
    };
    tbody.innerHTML = campFiltered.map(c => {
        const total = parseInt(c.total) || 0;
        const sent  = parseInt(c.sent)  || 0;
        const fail  = parseInt(c.failed)|| 0;
        const pct   = total ? Math.round(sent / total * 100) : 0;
        const statusBadge = statusMap[c.status] || `<span class="camp-status draft">${escHtml(c.status||'—')}</span>`;
        const date      = (c.created_at||'').substring(0, 10);
        return `<tr>
            <td>
                <div class="fw-semibold" style="font-size:14px;color:#1a1a2e;">${escHtml(c.name||'—')}</div>
                <div class="text-muted" style="font-size:11px;">#${c.id}</div>
            </td>
            <td><span class="badge rounded-pill bg-light text-dark border fw-normal" style="font-size:12px;">${escHtml(c.template_name||'—')}</span></td>
            <td>${total.toLocaleString()}</td>
            <td>${sent.toLocaleString()}</td>
            <td>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="progress-thin" style="width:60px;"><div class="bar" style="width:${pct}%;background:${fail>0?'#e53935':'#25d366'};"></div></div>
                        <span style="font-size:12px;color:#666;">${sent}/${total}</span>
                    </div>${fail>0?`<div style="font-size:11px;color:#e53935;font-weight:600;">❌ ${fail} failed</div>`:''}
                </div>
            </td>
            <td>${statusBadge}</td>
            <td class="text-muted" style="font-size:12px;">${date}</td>
            <td class="text-end">
                <button class="action-btn view" title="${c.failed_numbers?'Failed: '+escHtml(c.failed_numbers):''}" onclick="campView(${c.id},event)"><i class="bi bi-eye-fill"></i></button>
                <button class="action-btn delete ms-1" title="Delete" onclick="campAskDelete(${c.id})"><i class="bi bi-trash-fill"></i></button>
            </td>
        </tr>`;
    }).join('');
}

function campView(id, e) {
    const c = campAll.find(x => x.id == id);
    if (!c) return;
    if (c.failed_numbers && c.failed_numbers.trim()) {
        const lines = c.failed_numbers.trim().split('\n');
        let msg = `Campaign: ${c.name}\nSent: ${c.sent} | Failed: ${c.failed}\n\nFailed Numbers:\n${lines.join('\n')}`;
        alert(msg);
    } else {
        showToast(`${c.name} — Sent: ${c.sent}, Failed: ${c.failed}`, 'success');
    }
}

/* ── CREATE WIZARD ── */
function campOpenCreate() {
    campResetWizard();
    campLoadTpls();
    campLoadGroupsForModal();
    campModal.show();
}

function campResetWizard() {
    campStep = 1; campSelTpl = null;
    ['campStep1','campStep2','campStep3','campStep4'].forEach((id,i) => document.getElementById(id).style.display = i===0?'block':'none');
    [1,2,3,4].forEach(i => { const d=document.getElementById('stepDot'+i); d.classList.remove('active','done'); if(i===1) d.classList.add('active'); });
    document.getElementById('campBtnBack').classList.add('d-none');
    document.getElementById('campBtnNext').innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>';
    document.getElementById('campName').value = '';
    document.getElementById('campNow').checked = true;
    document.getElementById('campScheduleAt').classList.add('d-none');
    ['campStep1Error','campStep2Error','campStep3Error','campStep4Error'].forEach(id => document.getElementById(id).classList.add('d-none'));
}

function campGoStep(n) {
    document.getElementById('campStep'+campStep).style.display = 'none';
    document.getElementById('stepDot'+campStep).classList.remove('active');
    document.getElementById('stepDot'+campStep).classList.add('done');
    campStep = n;
    document.getElementById('campStep'+campStep).style.display = 'block';
    document.getElementById('stepDot'+campStep).classList.remove('done');
    document.getElementById('stepDot'+campStep).classList.add('active');
    document.getElementById('campBtnBack').classList.toggle('d-none', campStep === 1);
    if (campStep === 4) {
        document.getElementById('campBtnNext').innerHTML = '<i class="bi bi-send me-1"></i>Launch Campaign';
        campPopulateReview();
    } else if (campStep === 3) {
        document.getElementById('campBtnNext').innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>';
        campEstimateRecipients();
    } else {
        document.getElementById('campBtnNext').innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>';
    }
}

function campNextStep() {
    // Validate current step
    if (campStep === 1) {
        const name = document.getElementById('campName').value.trim();
        const err  = document.getElementById('campStep1Error');
        if (!name) { err.textContent='Campaign name is required.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        campGoStep(2);
    } else if (campStep === 2) {
        const err = document.getElementById('campStep2Error');
        if (!campSelTpl) { err.textContent='Please select a template.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        campGoStep(3);
    } else if (campStep === 3) {
        const err = document.getElementById('campStep3Error');
        const aud = document.querySelector('input[name="campAudience"]:checked').value;
        if (aud === 'group' && !document.getElementById('campGroupSel').value) { err.textContent='Please select a group.'; err.classList.remove('d-none'); return; }
        if (aud === 'custom' && !document.getElementById('campCustomNums').value.trim()) { err.textContent='Please enter phone numbers.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none');
        campGoStep(4);
    } else if (campStep === 4) {
        campLaunch();
    }
}

function campPrevStep() {
    if (campStep <= 1) return;
    document.getElementById('campStep'+campStep).style.display = 'none';
    document.getElementById('stepDot'+campStep).classList.remove('active','done');
    campStep--;
    document.getElementById('campStep'+campStep).style.display = 'block';
    document.getElementById('stepDot'+campStep).classList.remove('done');
    document.getElementById('stepDot'+campStep).classList.add('active');
    document.getElementById('campBtnBack').classList.toggle('d-none', campStep === 1);
    document.getElementById('campBtnNext').innerHTML = campStep===4?'<i class="bi bi-send me-1"></i>Launch Campaign':'Next <i class="bi bi-arrow-right ms-1"></i>';
}

/* ── TEMPLATES ── */
function campLoadTpls() {
    document.getElementById('campTplList').innerHTML = '<div class="text-center text-muted py-3">Loading…</div>';
    fetch('/templates/meta').then(r=>r.json()).then(data => {
        campTpls = (data.templates||[]).filter(t=>t.status==='APPROVED');
        campRenderTpls(campTpls);
    }).catch(() => { document.getElementById('campTplList').innerHTML='<div class="text-center text-danger py-3">Could not load templates.</div>'; });
}
function campFilterTpls() { const q=document.getElementById('campTplSearch').value.toLowerCase(); campRenderTpls(campTpls.filter(t=>`${t.name} ${t.preview}`.toLowerCase().includes(q))); }
function campRenderTpls(tpls) {
    const box = document.getElementById('campTplList');
    if (!tpls.length) { box.innerHTML='<div class="text-center text-muted py-3">No approved templates found.</div>'; return; }
    box.innerHTML = tpls.map(t => {
        const sel = campSelTpl && campSelTpl.name===t.name ? 'selected' : '';
        return `<div class="tpl-pick-item ${sel}" onclick="campSelectTpl('${t.name}')">
            <div class="tpl-pick-name">${escHtml(t.name)} <span class="text-muted fw-normal" style="font-size:11px;">(${escHtml(t.language)})</span></div>
            <div class="tpl-pick-prev">${escHtml(t.preview||'No preview')}</div>
        </div>`;
    }).join('');
}
function campSelectTpl(name) {
    campSelTpl = campTpls.find(t=>t.name===name);
    campRenderTpls(campTpls);
    const phs = [...new Set((campSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const hasImg = campSelTpl?.components && campSelTpl.components.some(c=>
        (c.type||'').toUpperCase()==='HEADER' && (c.format||'').toUpperCase()==='IMAGE'
    );
    let html = '';
    if(phs.length){
        html += `<div class="p-3 rounded-3 mb-2" style="background:#f9fafb;border:1px solid #e5e5e5;">
            <div class="fw-bold mb-2" style="font-size:12px;color:#555;text-transform:uppercase;">Template Variables</div>
            ${phs.map((ph,i)=>`<div class="var-field mb-2"><label for="campVar${i}">Variable ${i+1} (${escHtml(ph)})</label><input type="text" id="campVar${i}" placeholder="Enter value for ${escHtml(ph)}"></div>`).join('')}
           </div>`;
    }
    if(hasImg){
        html += `<div class="p-3 rounded-3 mb-2" style="background:#fff8e1;border:1px solid #ffe082;">
            <div class="fw-bold mb-2" style="font-size:12px;color:#e65100;text-transform:uppercase;"><i class="bi bi-image me-1"></i>Header Image Required</div>
            <div class="var-field"><label for="campHeaderImg">Image URL *</label><input type="text" id="campHeaderImg" placeholder="https://example.com/image.jpg" class="form-control form-control-sm rounded-3"></div>
            <div class="alert alert-warning py-2 mt-2 mb-0" style="font-size:11px;">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>Important:</strong> URL must be a direct image link (not ibb.co, imgur short links, or Google Drive).
                Must end in .jpg or .png and be publicly accessible. Example: <code>https://yoursite.com/image.jpg</code>
            </div>
           </div>`;
    }
    document.getElementById('campVarFields').innerHTML = html;
}

/* ── AUDIENCE ── */
function campToggleAudience() {
    const v = document.querySelector('input[name="campAudience"]:checked').value;
    document.getElementById('campGroupWrap').classList.toggle('d-none', v!=='group');
    document.getElementById('campCustomWrap').classList.toggle('d-none', v!=='custom');
    campEstimateRecipients();
}
function campEstimateRecipients() {
    fetch('/contacts/stats').then(r=>r.json()).then(d=>{
        const v = document.querySelector('input[name="campAudience"]:checked').value;
        if (v==='all') document.getElementById('campRecipientCount').textContent = (d.total||0) + ' contacts';
        else if (v==='group') document.getElementById('campRecipientCount').textContent = 'Loading…';
        else document.getElementById('campRecipientCount').textContent = 'Custom numbers';
    }).catch(()=>{});
}
function campLoadGroupsForModal() {
    fetch('/contacts/groups').then(r=>r.json()).then(data=>{
        const sel = document.getElementById('campGroupSel');
        sel.innerHTML = '<option value="">— Select group —</option>';
        (data.groups||[]).forEach(g=>{ const o=document.createElement('option'); o.value=g; o.textContent=g; sel.appendChild(o); });
    }).catch(()=>{});
}
function campToggleSchedule() { document.getElementById('campScheduleAt').classList.toggle('d-none', document.getElementById('campNow').checked); }

/* ── REVIEW ── */
function campPopulateReview() {
    document.getElementById('revName').textContent     = document.getElementById('campName').value;
    document.getElementById('revTemplate').textContent = campSelTpl?.name || '—';
    document.getElementById('revPreview').textContent  = campSelTpl?.preview || '—';
    const sch = document.getElementById('campNow').checked ? 'Send Now' : (document.getElementById('campDateTime').value||'Scheduled');
    document.getElementById('revSchedule').textContent = sch;
    const v   = document.querySelector('input[name="campAudience"]:checked').value;
    const grp = document.getElementById('campGroupSel').value;
    document.getElementById('revRecipients').textContent = v==='all'?'All Contacts':v==='group'?('Group: '+(grp||'—')):'Custom Numbers';
}

/* ── LAUNCH ── */
async function campLaunch() {
    const err = document.getElementById('campStep4Error');
    const btn = document.getElementById('campBtnNext');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Launching…';
    const phs   = [...new Set((campSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const params = phs.map((_,i)=>document.getElementById(`campVar${i}`)?.value.trim()||'');
    const aud   = document.querySelector('input[name="campAudience"]:checked').value;
    // Build phones array from audience selection
    let phones = [];
    if(aud === 'all') {
        // fetch all contacts phones
        try {
            const cr = await fetch('/contacts/list', {credentials:'same-origin'});
            const cd = await cr.json();
            phones = (cd.contacts||[]).map(c=>c.phone).filter(Boolean);
        } catch(e) { phones = []; }
    } else if(aud === 'group') {
        const grp = document.getElementById('campGroupSel').value;
        try {
            const cr = await fetch('/contacts/list', {credentials:'same-origin'});
            const cd = await cr.json();
            phones = (cd.contacts||[]).filter(c=>c.group_name===grp).map(c=>c.phone).filter(Boolean);
        } catch(e) { phones = []; }
    } else if(aud === 'custom') {
        const raw = document.getElementById('campCustomNums').value;
        phones = raw.split(/[\n,]+/).map(s=>s.trim().replace(/\D/g,'')).filter(s=>s.length>=8);
    }

    if(!phones.length) { err.textContent='No recipients found for selected audience.'; err.classList.remove('d-none'); btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Launch Campaign'; return; }

    const payload = {
        name:           document.getElementById('campName').value.trim(),
        template_name:  campSelTpl?.name,
        language_code:  campSelTpl?.language||'en_US',
        parameters:     params,
        header_image:   document.getElementById('campHeaderImg')?.value.trim()||'',
        phones:         phones,
        scheduled_at:   document.getElementById('campNow').checked ? null : document.getElementById('campDateTime').value,
    };
    fetch('/campaigns', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Launch Campaign';
        if (d.error) { err.textContent=d.error; err.classList.remove('d-none'); return; }
        campModal.hide(); campResetWizard();
        setTimeout(()=>{ campLoadCampaigns(); campLoadStats(); }, 500);
        const msg = d.failed > 0
            ? `🚀 Campaign launched! Sent: ${d.sent}, Failed: ${d.failed}`
            : `🚀 Campaign launched! All ${d.sent} messages sent successfully`;
        showToast(msg, 'success');
        if (d.failed > 0 && d.failed_numbers && d.failed_numbers.length) {
            setTimeout(()=>alert('Failed numbers:\n' + d.failed_numbers.join('\n')), 500);
        }
    }).catch(()=>{ btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Launch Campaign'; err.textContent='Something went wrong.'; err.classList.remove('d-none'); });
}

/* ── DELETE ── */
function campAskDelete(id) { campDeleteId=id; campDeleteModal.show(); }
function campConfirmDelete() {
    if (!campDeleteId) return;
    const btn=document.getElementById('campConfirmDelBtn'); btn.disabled=true; btn.textContent='Deleting…';
    fetch(`/campaigns/${campDeleteId}`,{
        method:'DELETE', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
    }).then(r=>r.json()).then(()=>{
        btn.disabled=false; btn.textContent='Delete';
        campDeleteModal.hide(); campDeleteId=null; campLoadCampaigns(); campLoadStats();
        showToast('✅ Campaign deleted', 'success');
    }).catch(()=>{ btn.disabled=false; btn.textContent='Delete'; showToast('❌ Failed', 'error'); });
}
</script>
@endpush