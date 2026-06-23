@extends('layouts.app')

@section('title', 'Templates')
@section('page_title', 'WhatsApp Templates')

@section('page_styles')
/* ── STATS ── */
.tpl-stat { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border-left:4px solid #25d366; display:flex; align-items:center; gap:14px; }
.tpl-stat.blue   { border-left-color:#1976d2; }
.tpl-stat.orange { border-left-color:#f57c00; }
.tpl-stat.red    { border-left-color:#e53935; }
.tpl-stat-icon   { font-size:26px; }
.tpl-stat-label  { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px; }
.tpl-stat-value  { font-size:24px; font-weight:800; color:#1a1a2e; }

/* ── FILTER TABS ── */
.tpl-filter-tab { padding:7px 16px; border-radius:20px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:transparent; color:#888; transition:0.15s; }
.tpl-filter-tab:hover { background:#f0f0f0; color:#1a1a2e; }
.tpl-filter-tab.active { background:#25d366; color:#fff; }

/* ── TEMPLATE CARDS ── */
.tpl-card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1.5px solid #f0f0f0; transition:0.2s; height:100%; display:flex; flex-direction:column; }
.tpl-card:hover { border-color:#25d366; box-shadow:0 4px 20px rgba(37,211,102,0.1); transform:translateY(-2px); }
.tpl-card.selected { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.15); }
.tpl-card-hdr { padding:14px 16px 10px; border-bottom:1px solid #f5f5f5; }
.tpl-card-name { font-size:14px; font-weight:700; color:#1a1a2e; margin-bottom:6px; word-break:break-word; }
.tpl-card-body { padding:12px 16px; flex:1; }
.tpl-card-preview { font-size:12px; color:#555; line-height:1.6; white-space:pre-wrap; word-break:break-word; display:-webkit-box; -webkit-line-clamp:4; -webkit-box-orient:vertical; overflow:hidden; }
.tpl-card-footer { padding:10px 14px; border-top:1px solid #f5f5f5; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:6px; }

/* ── STATUS ── */
.tpl-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
.tpl-badge.APPROVED { background:#e8f5e9; color:#2e7d32; }
.tpl-badge.PENDING  { background:#fff3e0; color:#e65100; }
.tpl-badge.REJECTED { background:#fdecea; color:#c62828; }
.tpl-badge.PAUSED   { background:#f5f5f5; color:#666; }
.tpl-badge.PENDING_DELETION { background:#f3e5f5; color:#6a1b9a; }

/* ── CATEGORY ── */
.cat-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.3px; }
.cat-badge.MARKETING    { background:#fff3e0; color:#e65100; }
.cat-badge.UTILITY      { background:#f3e5f5; color:#6a1b9a; }
.cat-badge.AUTHENTICATION { background:#e8f5e9; color:#2e7d32; }

/* ── PHONE PREVIEW ── */
.phone-frame { background:#fff; border-radius:24px; box-shadow:0 8px 32px rgba(0,0,0,0.12); overflow:hidden; max-width:280px; margin:0 auto; }
.phone-bar { background:#25d366; padding:10px 14px; display:flex; align-items:center; gap:10px; }
.phone-av { width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.3); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff; flex-shrink:0; }
.phone-body { background:#efeae2; padding:14px 12px; min-height:220px; }
.phone-bubble { background:#fff; border-radius:8px 8px 8px 0; padding:10px 12px; max-width:90%; box-shadow:0 1px 2px rgba(0,0,0,0.1); }
.phone-bubble-hdr-img { width:100%; height:120px; background:#f0f0f0; border-radius:6px 6px 0 0; display:flex; align-items:center; justify-content:center; font-size:32px; color:#ccc; overflow:hidden; margin:-10px -12px 10px; width:calc(100% + 24px); }
.phone-bubble-hdr-img img { width:100%; height:100%; object-fit:cover; }
.phone-bubble-hdr-doc { background:#f0f4ff; border-radius:6px; padding:10px 12px; display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.phone-bubble-hdr-text { font-weight:700; font-size:14px; color:#111b21; margin-bottom:8px; }
.phone-bubble-text { font-size:13px; color:#111b21; line-height:1.5; white-space:pre-wrap; word-break:break-word; }
.phone-bubble-footer { font-size:11px; color:#667781; margin-top:6px; }
.phone-bubble-time { font-size:10px; color:#667781; text-align:right; margin-top:4px; }
.phone-btn { background:#fff; border-top:1px solid #f0f0f0; padding:10px 12px; text-align:center; font-size:13px; font-weight:600; color:#00a884; cursor:default; display:flex; align-items:center; justify-content:center; gap:6px; }
.phone-btn + .phone-btn { border-top:1px solid #f0f0f0; }

/* ── CREATE FORM ── */
.create-panel { display:none; }
.create-panel.open { display:block; }
.form-section { background:#fff; border-radius:14px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section-title { font-size:13px; font-weight:700; color:#1a1a2e; margin-bottom:14px; display:flex; align-items:center; gap:8px; }

/* ── HEADER TYPE TABS ── */
.hdr-type-tab { padding:8px 14px; border:1.5px solid #e5e5e5; border-radius:8px; cursor:pointer; font-size:12px; font-weight:600; color:#666; background:#fff; transition:0.15s; display:flex; align-items:center; gap:6px; }
.hdr-type-tab:hover { border-color:#25d366; color:#25d366; }
.hdr-type-tab.active { border-color:#25d366; background:#e8f5e9; color:#1a7a40; }

/* ── BUTTON BUILDER ── */
.btn-item { background:#f9fafb; border:1.5px solid #e5e5e5; border-radius:10px; padding:12px 14px; margin-bottom:8px; }
.btn-type-sel { display:flex; gap:6px; margin-bottom:10px; flex-wrap:wrap; }
.btn-type-opt { padding:5px 12px; border:1.5px solid #e5e5e5; border-radius:20px; cursor:pointer; font-size:11px; font-weight:700; background:#fff; transition:0.15s; }
.btn-type-opt:hover { border-color:#25d366; color:#25d366; }
.btn-type-opt.active { border-color:#25d366; background:#25d366; color:#fff; }

/* ── VARIABLE HINT ── */
.var-hint { background:#f0fdf4; border:1px solid #c8e6c9; border-radius:8px; padding:10px 12px; font-size:12px; color:#2e7d32; margin-top:8px; }

/* ── EMPTY STATE ── */
.empty-state { padding:60px 20px; text-align:center; }
.empty-icon { font-size:56px; opacity:0.2; margin-bottom:16px; }
.empty-state p { color:#aaa; font-size:14px; }

/* ── ACTION BTNS ── */
.tpl-action-btn { width:28px; height:28px; border:none; background:transparent; border-radius:6px; cursor:pointer; font-size:14px; display:inline-flex; align-items:center; justify-content:center; transition:0.15s; }
.tpl-action-btn.send:hover   { background:#e8f5e9; color:#2e7d32; }
.tpl-action-btn.delete:hover { background:#fdecea; color:#e53935; }
@endsection

@section('content')

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="tpl-stat"><div class="tpl-stat-icon">📝</div><div><div class="tpl-stat-label">Total</div><div class="tpl-stat-value" id="tStatTotal">—</div></div></div></div>
    <div class="col-6 col-md-3"><div class="tpl-stat blue"><div class="tpl-stat-icon">✅</div><div><div class="tpl-stat-label">Approved</div><div class="tpl-stat-value" id="tStatApproved">—</div></div></div></div>
    <div class="col-6 col-md-3"><div class="tpl-stat orange"><div class="tpl-stat-icon">⏳</div><div><div class="tpl-stat-label">Pending</div><div class="tpl-stat-value" id="tStatPending">—</div></div></div></div>
    <div class="col-6 col-md-3"><div class="tpl-stat red"><div class="tpl-stat-icon">❌</div><div><div class="tpl-stat-label">Rejected</div><div class="tpl-stat-value" id="tStatRejected">—</div></div></div></div>
</div>

<!-- Toolbar -->
<div class="card fu-card mb-3">
    <div class="card-body py-3 d-flex flex-wrap align-items-center gap-2">
        <div class="d-flex gap-1 flex-wrap me-auto">
            <button class="tpl-filter-tab active" onclick="tplSetFilter('ALL',this)">All</button>
            <button class="tpl-filter-tab" onclick="tplSetFilter('APPROVED',this)">✅ Approved</button>
            <button class="tpl-filter-tab" onclick="tplSetFilter('PENDING',this)">⏳ Pending</button>
            <button class="tpl-filter-tab" onclick="tplSetFilter('REJECTED',this)">❌ Rejected</button>
        </div>
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
            <input type="text" id="tplSearchInput" class="form-control form-control-sm rounded-pill" placeholder="Search templates…" style="padding-left:30px;width:200px;" oninput="tplApplyFilter()">
        </div>
        <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="tplLoad()"><i class="bi bi-arrow-clockwise me-1"></i>Sync</button>
        <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="tplToggleCreate()"><i class="bi bi-plus-lg me-1"></i>Create Template</button>
    </div>
</div>

<!-- Create Panel -->
<div class="create-panel" id="tplCreatePanel">
    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-7">
            <!-- Basic Info -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-info-circle text-success"></i>Basic Information</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tcName" class="form-label fw-semibold" style="font-size:12px;">Template Name * <span class="text-muted fw-normal">(lowercase, underscores)</span></label>
                        <input type="text" id="tcName" class="form-control rounded-3" placeholder="e.g. welcome_message" oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9_]/g,'');tplUpdatePreview()">
                    </div>
                    <div class="col-md-3">
                        <label for="tcCategory" class="form-label fw-semibold" style="font-size:12px;">Category *</label>
                        <select id="tcCategory" class="form-select rounded-3" onchange="tplUpdatePreview()">
                            <option value="MARKETING">Marketing</option>
                            <option value="UTILITY">Utility</option>
                            <option value="AUTHENTICATION">Authentication</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tcLanguage" class="form-label fw-semibold" style="font-size:12px;">Language *</label>
                        <select id="tcLanguage" class="form-select rounded-3">
                            <option value="en_US">English (US)</option>
                            <option value="en">English</option>
                            <option value="en_IN">English (India)</option>
                            <option value="hi">Hindi</option>
                            <option value="ta">Tamil</option>
                            <option value="te">Telugu</option>
                            <option value="ar">Arabic</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-layout-text-window text-primary"></i>Header <span class="text-muted fw-normal ms-1" style="font-size:12px;">(optional)</span></div>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <button class="hdr-type-tab active" id="hdrTabNone" onclick="tplSetHeader('NONE')"><i class="bi bi-x-circle"></i>None</button>
                    <button class="hdr-type-tab" id="hdrTabText" onclick="tplSetHeader('TEXT')"><i class="bi bi-type"></i>Text</button>
                    <button class="hdr-type-tab" id="hdrTabImage" onclick="tplSetHeader('IMAGE')"><i class="bi bi-image"></i>Image</button>
                    <button class="hdr-type-tab" id="hdrTabDocument" onclick="tplSetHeader('DOCUMENT')"><i class="bi bi-file-earmark"></i>Document</button>
                    <button class="hdr-type-tab" id="hdrTabVideo" onclick="tplSetHeader('VIDEO')"><i class="bi bi-camera-video"></i>Video</button>
                </div>
                <div id="hdrInputNone" style="display:none;"><div class="text-muted" style="font-size:13px;">No header will be added.</div></div>
                <div id="hdrInputText" style="display:none;">
                    <input type="text" id="tcHeaderText" class="form-control rounded-3" placeholder="Header text (max 60 chars)" maxlength="60" oninput="tplUpdatePreview()">
                </div>
                <div id="hdrInputImage" style="display:none;">
                    <div class="alert alert-info rounded-3 py-2 mb-2" style="font-size:12px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Upload a sample image — Meta needs this to approve the template. You can send any image when actually sending the template.
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <label class="btn btn-outline-secondary btn-sm rounded-pill mb-0" style="cursor:pointer;">
                            <i class="bi bi-upload me-1"></i>Upload Sample Image
                            <input type="file" id="tcHeaderImageFile" accept="image/*" style="display:none;" onchange="tplUploadHeaderImage(this)">
                        </label>
                        <span id="tcHeaderImageStatus" class="text-muted" style="font-size:12px;">No image uploaded</span>
                    </div>
                    <input type="hidden" id="tcHeaderImage" value="">
                </div>
                <div id="hdrInputDocument" style="display:none;">
                    <label for="tcHeaderDoc" class="form-label fw-semibold" style="font-size:12px;">Document URL * <span class="text-muted fw-normal">(PDF recommended)</span></label>
                    <input type="text" id="tcHeaderDoc" class="form-control rounded-3" placeholder="https://example.com/file.pdf" oninput="tplUpdatePreview()">
                </div>
                <div id="hdrInputVideo" style="display:none;">
                    <label for="tcHeaderVideo" class="form-label fw-semibold" style="font-size:12px;">Video URL *</label>
                    <input type="text" id="tcHeaderVideo" class="form-control rounded-3" placeholder="https://example.com/video.mp4" oninput="tplUpdatePreview()">
                </div>
            </div>

            <!-- Body -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-card-text text-warning"></i>Body *</div>
                <textarea id="tcBody" class="form-control rounded-3" rows="5" maxlength="1024"
                    placeholder="Your message body. Use {{1}}, {{2}} for variables.&#10;&#10;Example: Hello {{1}}, your appointment is on {{2}}."
                    oninput="tplUpdatePreview();tplUpdateBodyCount()"></textarea>
                <div class="d-flex justify-content-between mt-1">
                    <div class="var-hint" style="margin-top:0;padding:6px 10px;font-size:11px;">Use <strong>{{1}}</strong>, <strong>{{2}}</strong> etc. for dynamic variables. Bold: <strong>*text*</strong>, Italic: <em>_text_</em></div>
                    <div class="text-muted ms-2 flex-shrink-0" id="tcBodyCount" style="font-size:11px;align-self:flex-end;">0/1024</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-chat-square-text text-secondary"></i>Footer <span class="text-muted fw-normal ms-1" style="font-size:12px;">(optional)</span></div>
                <input type="text" id="tcFooter" class="form-control rounded-3" placeholder="e.g. Reply STOP to unsubscribe" maxlength="60" oninput="tplUpdatePreview()">
            </div>

            <!-- Buttons -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-grid-1x2 text-info"></i>Buttons <span class="text-muted fw-normal ms-1" style="font-size:12px;">(optional, max 3)</span></div>
                <div id="tcBtnList"></div>
                <button class="btn btn-sm btn-outline-secondary rounded-pill mt-1" onclick="tplAddButton()" id="tcAddBtnBtn">
                    <i class="bi bi-plus-lg me-1"></i>Add Button
                </button>
                <div class="mt-2" style="font-size:12px;color:#888;">
                    <strong>Quick Reply</strong> — Reply with preset text &nbsp;|&nbsp;
                    <strong>Call Us</strong> — Dial a phone number &nbsp;|&nbsp;
                    <strong>Open Website</strong> — Open a URL
                </div>
            </div>

            <!-- Error & Submit -->
            <div id="tcVarSamples" class="mb-3"></div>
            <div id="tcError" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
            <div class="d-flex gap-2">
                <button class="btn btn-fu-primary rounded-3 px-4" id="tcSubmitBtn" onclick="tplSubmit()">
                    <i class="bi bi-send me-1"></i>Submit to Meta
                </button>
                <button class="btn btn-light rounded-3" onclick="tplToggleCreate()">Cancel</button>
            </div>
        </div>

        <!-- Preview -->
        <div class="col-lg-5">
            <div class="card fu-card" style="position:sticky;top:80px;">
                <div class="card-header"><i class="bi bi-phone me-2"></i>Live Preview</div>
                <div class="card-body" style="background:#f0f2f5;border-radius:0 0 14px 14px;padding:20px;">
                    <div class="phone-frame">
                        <div class="phone-bar">
                            
                             <div class="phone-av">{{ strtoupper(substr($user->organisation ?? $user->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#fff;">{{ $user->organisation ?? $user->name }}</div>
                                <div style="font-size:10px;color:rgba(255,255,255,0.7);">Business Account</div>
                            </div>

                        </div>
                        <div class="phone-body" id="tcPhoneBody">
                            <div class="phone-bubble">
                                <div id="pvHeader"></div>
                                <div class="phone-bubble-text" id="pvBody">Your message will appear here…</div>
                                <div class="phone-bubble-footer" id="pvFooter" style="display:none;"></div>
                                <div class="phone-bubble-time">12:00 ✓✓</div>
                            </div>
                            <div id="pvButtons"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="my-4">
</div>

<!-- Templates Grid -->
<div id="tplGrid">
    <div class="text-center text-muted py-5"><div class="spinner-border text-success mb-3" role="status"></div><div>Loading templates…</div></div>
</div>

@endsection

@push('modals')
<!-- Send Modal -->
<div class="modal fade" id="tplSendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-send me-2 text-success"></i>Send Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="tplSendPhone" class="form-label fw-semibold" style="font-size:12px;">Recipient Phone * <span class="text-muted fw-normal">(with country code)</span></label>
                    <input type="text" id="tplSendPhone" class="form-control rounded-3" placeholder="919876543210">
                </div>
                <div id="tplSendVarFields"></div>
                <div id="tplSendImgField" style="display:none;" class="mb-3">
                    <label for="tplSendImg" class="form-label fw-semibold" style="font-size:12px;">Header Image URL *</label>
                    <input type="text" id="tplSendImg" class="form-control rounded-3" placeholder="https://example.com/image.jpg">
                </div>
                <div class="mb-3">
                    <label for="tplSendLang" class="form-label fw-semibold" style="font-size:12px;">Language</label>
                    <input type="text" id="tplSendLang" class="form-control rounded-3" value="en_US">
                </div>
                <div id="tplSendErr" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-fu-primary rounded-3" id="tplSendConfirmBtn" onclick="tplConfirmSend()"><i class="bi bi-send me-1"></i>Send</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="tplDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
                <h6 class="fw-bold mb-2">Delete Template?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">This will remove it from Meta permanently.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger rounded-3 px-4" id="tplDelConfirmBtn" onclick="tplConfirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let tplAll=[], tplFiltered=[], tplFilter='ALL', tplSelTpl=null, tplDelName=null;
let tplBtnCount=0, tplHdrType='NONE';
let tplSendModal, tplDeleteModal;

document.addEventListener('DOMContentLoaded',()=>{
    tplSendModal   = new bootstrap.Modal(document.getElementById('tplSendModal'));
    tplDeleteModal = new bootstrap.Modal(document.getElementById('tplDeleteModal'));
    tplLoad();
    document.getElementById('tcBody').addEventListener('input', ()=>{ tplUpdatePreview(); tplRenderVarSamples(); });
});

/* ── LOAD ── */
function tplLoad(){
    document.getElementById('tplGrid').innerHTML=`<div class="text-center text-muted py-5"><div class="spinner-border text-success mb-3" role="status"></div><div>Loading templates…</div></div>`;
    fetch('/api/templates/list').then(r=>r.json()).then(data=>{
        tplAll = data.templates||[];
        tplUpdateStats();
        tplApplyFilter();
    }).catch(()=>{
        document.getElementById('tplGrid').innerHTML=`<div class="empty-state"><div class="empty-icon">⚠️</div><p>Could not load templates. Check your WhatsApp credentials.</p></div>`;
    });
}

function tplUpdateStats(){
    document.getElementById('tStatTotal').textContent    = tplAll.length;
    document.getElementById('tStatApproved').textContent = tplAll.filter(t=>t.status==='APPROVED').length;
    document.getElementById('tStatPending').textContent  = tplAll.filter(t=>t.status==='PENDING').length;
    document.getElementById('tStatRejected').textContent = tplAll.filter(t=>t.status==='REJECTED').length;
}

function tplSetFilter(f, el){
    tplFilter=f;
    document.querySelectorAll('.tpl-filter-tab').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    tplApplyFilter();
}

function tplApplyFilter(){
    const q=(document.getElementById('tplSearchInput').value||'').toLowerCase();
    tplFiltered=tplAll.filter(t=>{
        const ms = tplFilter==='ALL' || t.status===tplFilter;
        const mq = !q || (t.name||'').toLowerCase().includes(q) || (t.preview||'').toLowerCase().includes(q);
        return ms && mq;
    });
    tplRenderGrid();
}

/* ── RENDER GRID ── */
function tplRenderGrid(){
    const grid=document.getElementById('tplGrid');
    if(!tplFiltered.length){
        grid.innerHTML=`<div class="empty-state"><div class="empty-icon">📝</div><p>No templates found.</p><button class="btn btn-fu-primary btn-sm rounded-pill mt-2" onclick="tplToggleCreate()"><i class="bi bi-plus-lg me-1"></i>Create Template</button></div>`;
        return;
    }
    grid.innerHTML=`<div class="row g-3">${tplFiltered.map(t=>{
        const hdrComp = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='HEADER');
        const hdrIcon = !hdrComp?'':hdrComp.format==='IMAGE'?'🖼️':hdrComp.format==='DOCUMENT'?'📄':hdrComp.format==='VIDEO'?'🎥':'';
        const btns = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='BUTTONS');
        const btnCount = btns?.buttons?.length||0;
        const vars = [...new Set((t.preview||'').match(/\{\{\d+\}\}/g)||[])].length;
        const rejReason = t.components?.find(c=>c.type==='BODY')?.text||'';
        return `<div class="col-md-6 col-xl-4">
            <div class="tpl-card">
                <div class="tpl-card-hdr">
                    <div class="tpl-card-name">${escHtml(t.name)}</div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="tpl-badge ${t.status||'PENDING'}">${tplStatusIcon(t.status)} ${t.status||'UNKNOWN'}</span>
                        <span class="cat-badge ${t.category||''}">${escHtml(t.category||'—')}</span>
                        ${hdrIcon?`<span title="Has ${hdrComp?.format} header">${hdrIcon}</span>`:''}
                        <span class="ms-auto text-muted" style="font-size:11px;">${escHtml(t.language||'')}</span>
                    </div>
                </div>
                <div class="tpl-card-body">
                    <div class="tpl-card-preview">${escHtml(t.preview||'No preview available')}</div>
                    ${t.status==='REJECTED'?`<div class="alert alert-danger py-1 mt-2 mb-0 rounded-3" style="font-size:11px;"><i class="bi bi-x-circle me-1"></i>Rejected by Meta. Edit and resubmit.</div>`:''}
                </div>
                <div class="tpl-card-footer">
                    <div class="d-flex gap-1 flex-wrap" style="font-size:11px;color:#888;">
                        ${vars>0?`<span><i class="bi bi-braces me-1"></i>${vars} var${vars>1?'s':''}</span>`:''}
                        ${btnCount>0?`<span><i class="bi bi-grid-1x2 me-1"></i>${btnCount} btn${btnCount>1?'s':''}</span>`:''}
                    </div>
                    <div class="d-flex gap-1">
                        <button class="tpl-action-btn preview" onclick="tplOpenPreview('${escHtml(t.name)}')" title="Preview"><i class="bi bi-eye-fill"></i></button>
                        <button class="tpl-action-btn edit" onclick="tplOpenEdit('${escHtml(t.name)}')" title="Edit & Resubmit"><i class="bi bi-pencil-fill"></i></button>
                        ${t.status==='APPROVED'?`<button class="tpl-action-btn send" onclick="tplOpenSend('${escHtml(t.name)}')" title="Send"><i class="bi bi-send-fill"></i></button>`:''}
                        <button class="tpl-action-btn delete" onclick="tplAskDelete('${escHtml(t.name)}')" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('')}</div>`;
}

function tplStatusIcon(s){
    return {APPROVED:'✅',PENDING:'⏳',REJECTED:'❌',PAUSED:'⏸️',PENDING_DELETION:'🗑️'}[s]||'❓';
}

/* ── CREATE PANEL ── */
function tplToggleCreate(){
    const p=document.getElementById('tplCreatePanel');
    p.classList.toggle('open');
    if(p.classList.contains('open')){ p.scrollIntoView({behavior:'smooth'}); tplUpdatePreview(); }
}

/* ── HEADER TYPE ── */
function tplSetHeader(type){
    tplHdrType=type;
    ['NONE','TEXT','IMAGE','DOCUMENT','VIDEO'].forEach(t=>{
        document.getElementById('hdrTab'+t.charAt(0)+t.slice(1).toLowerCase())?.classList.toggle('active',t===type);
        const inp=document.getElementById('hdrInput'+t.charAt(0)+t.slice(1).toLowerCase());
        if(inp) inp.style.display=t===type?'block':'none';
    });
    tplUpdatePreview();
}

/* ── BUTTON BUILDER ── */
function tplAddButton(){
    if(tplBtnCount>=3){ showToast('Maximum 3 buttons allowed','error'); return; }
    const id='tcBtn'+tplBtnCount;
    tplBtnCount++;
    const div=document.createElement('div');
    div.className='btn-item'; div.id=id;
    div.innerHTML=`
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-bold" style="font-size:12px;">Button ${tplBtnCount}</span>
            <button class="btn btn-sm btn-outline-danger rounded-pill py-0 px-2" onclick="document.getElementById('${id}').remove();tplBtnCount--;tplUpdateBtnAddBtn();tplUpdatePreview();" style="font-size:11px;">Remove</button>
        </div>
        <div class="btn-type-sel">
            <button class="btn-type-opt active" data-type="QUICK_REPLY" onclick="tplSetBtnType('${id}','QUICK_REPLY',this)">↩️ Quick Reply</button>
            <button class="btn-type-opt" data-type="PHONE_NUMBER" onclick="tplSetBtnType('${id}','PHONE_NUMBER',this)">📞 Call Us</button>
            <button class="btn-type-opt" data-type="URL" onclick="tplSetBtnType('${id}','URL',this)">🌐 Open Website</button>
            <button class="btn-type-opt" data-type="PHONE_NUMBER" data-chat="1" onclick="tplSetBtnType('${id}','PHONE_NUMBER',this);this.closest('.btn-item').querySelector('.btn-label').value='Chat Now';">💬 Chat Now</button>

        </div>
        <div class="mb-2">
            <label for="btnLabel" style="font-size:11px;font-weight:700;color:#666;">Button Label *</label>
            <input type="text" class="form-control form-control-sm rounded-3 btn-label" placeholder="e.g. Learn More" maxlength="25" oninput="tplUpdatePreview()">
        </div>
        <div class="btn-extra" style="display:none;">
            <label class="btn-extra-label" style="font-size:11px;font-weight:700;color:#666;">Value *</label>
            <input type="text" class="form-control form-control-sm rounded-3 btn-value" placeholder="" oninput="tplUpdatePreview()">
        </div>`;
    document.getElementById('tcBtnList').appendChild(div);
    tplUpdateBtnAddBtn();
    tplUpdatePreview();
}

function tplSetBtnType(id, type, el){
    const item=document.getElementById(id);
    item.querySelectorAll('.btn-type-opt').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    item.dataset.btnType = type;
    const extra=item.querySelector('.btn-extra');
    const label=item.querySelector('.btn-extra-label');
    const val=item.querySelector('.btn-value');
    if(type==='QUICK_REPLY'){ extra.style.display='none'; }
    else if(type==='PHONE_NUMBER'){ extra.style.display='block'; label.textContent='Phone Number *'; val.placeholder='+919876543210'; }
    else if(type==='URL'){ extra.style.display='block'; label.textContent='URL *'; val.placeholder='https://example.com'; }
    else if(type==='CHAT'){ extra.style.display='block'; label.textContent='WhatsApp Number *'; val.value=''; val.placeholder='919876543210 (number only, will add wa.me/ prefix)'; }
    tplUpdatePreview();
}

function tplUpdateBtnAddBtn(){
    const count=document.querySelectorAll('#tcBtnList .btn-item').length;
    tplBtnCount=count;
    document.getElementById('tcAddBtnBtn').style.display=count>=3?'none':'inline-flex';
}

/* ── PREVIEW ── */
function tplUploadHeaderImage(input){
    const file = input.files[0]; if(!file) return;
    const status = document.getElementById('tcHeaderImageStatus');
    status.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading…';
    const fd = new FormData(); fd.append('media', file);
    fetch('/api/templates/upload-media',{
        method:'POST', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:fd
    }).then(r=>r.json()).then(d=>{
        if(d.error){ status.innerHTML=`<span class="text-danger">❌ ${escHtml(d.error)}</span>`; return; }
        document.getElementById('tcHeaderImage').value = d.handle;
        status.innerHTML=`<span class="text-success">✅ ${escHtml(file.name)} uploaded</span>`;
        // Update preview with local URL
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('pvHeaderImg')?.setAttribute('src', e.target.result); };
        reader.readAsDataURL(file);
        tplUpdatePreview();
    }).catch(()=>{ status.innerHTML='<span class="text-danger">❌ Upload failed</span>'; });
}
function tplUpdatePreview(){
    const body=document.getElementById('tcBody').value;
    const footer=document.getElementById('tcFooter').value;
    const hdrText=document.getElementById('tcHeaderText')?.value;
    const hdrImg=document.getElementById('tcHeaderImage')?.value;
    const hdrDoc=document.getElementById('tcHeaderDoc')?.value;

    // Header
    const pvHdr=document.getElementById('pvHeader');
    pvHdr.innerHTML='';
    if(tplHdrType==='TEXT'&&hdrText){ pvHdr.innerHTML=`<div class="phone-bubble-hdr-text">${escHtml(hdrText)}</div>`; }
    else if(tplHdrType==='IMAGE'){
        pvHdr.innerHTML=`<div class="phone-bubble-hdr-img">${hdrImg?`<img src="${escHtml(hdrImg)}" onerror="this.parentElement.innerHTML='🖼️'">`:' 🖼️'}</div>`;
    }
    else if(tplHdrType==='DOCUMENT'){ pvHdr.innerHTML=`<div class="phone-bubble-hdr-doc"><i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:24px;"></i><div><div style="font-weight:700;font-size:12px;">Document</div><div style="font-size:10px;color:#667781;">${hdrDoc?escHtml(hdrDoc.split('/').pop()):'file.pdf'}</div></div></div>`; }
    else if(tplHdrType==='VIDEO'){ pvHdr.innerHTML=`<div class="phone-bubble-hdr-img">🎥</div>`; }

    // Body - render bold and italic
    const bodyHtml=(body||'Your message will appear here…').replace(/\*([^*]+)\*/g,'<strong>$1</strong>').replace(/_([^_]+)_/g,'<em>$1</em>').replace(/\{\{(\d+)\}\}/g,(m,n)=>'<span style="background:#fef3c7;padding:0 2px;border-radius:2px;">&#123;&#123;'+n+'&#125;&#125;</span>');
    document.getElementById('pvBody').innerHTML=bodyHtml;

    // Footer
    const pvFtr=document.getElementById('pvFooter');
    if(footer){ pvFtr.textContent=footer; pvFtr.style.display='block'; }
    else pvFtr.style.display='none';

    // Buttons
    const btns=document.querySelectorAll('#tcBtnList .btn-item');
    const pvBtns=document.getElementById('pvButtons');
    if(btns.length){
        pvBtns.innerHTML=[...btns].map(b=>{
            const label=b.querySelector('.btn-label')?.value||'Button';
            const type=b.querySelector('.btn-type-opt.active')?.dataset.type||'QUICK_REPLY';
            const icon=type==='PHONE_NUMBER'?'📞 ':type==='URL'?'🌐 ':'↩️ ';
            return `<div class="phone-btn">${icon}${escHtml(label)}</div>`;
        }).join('');
    } else pvBtns.innerHTML='';
}

function tplRenderVarSamples(){
    const body = document.getElementById('tcBody')?.value||'';
    const vars = [...new Set((body.match(/\{\{(\d+)\}\}/g)||[]))];
    const sec = document.getElementById('tcVarSamples'); if(!sec) return;
    if(!vars.length){ sec.innerHTML=''; return; }
    const existing = {};
    vars.forEach((_,i)=>{ const el=document.getElementById('tcVarSample'+i); if(el) existing[i]=el.value; });
    sec.innerHTML=`<div class="form-section" style="background:#f0fdf4;border:1px solid #c8e6c9;padding:14px 16px;border-radius:12px;margin-bottom:16px;">
        <div class="form-section-title" style="color:#2e7d32;margin-bottom:10px;"><i class="bi bi-braces me-1"></i>Variable Samples <span class="text-muted fw-normal ms-1" style="font-size:11px;">(required by Meta for approval)</span></div>
        ${vars.map((v,i)=>`<div class="mb-2"><label for="tcVarSample${i}" style="font-size:12px;font-weight:600;color:#555;">${escHtml(v)}</label><input type="text" id="tcVarSample${i}" class="form-control form-control-sm rounded-3" placeholder="${i===0?'e.g. John Smith':i===1?'e.g. ORD-12345':'e.g. sample value'}" value="${existing[i]||''}"></div>`).join('')}
    </div>`;
}
function tplUpdateBodyCount(){
    const len=document.getElementById('tcBody').value.length;
    document.getElementById('tcBodyCount').textContent=len+'/1024';
}

/* ── SUBMIT ── */
function extractTemplateVars(text){
    return [...new Set((text.match(/\{\{\d+\}\}/g)||[]))];
}
function tplSubmit(){
    const name=document.getElementById('tcName').value.trim();
    const category=document.getElementById('tcCategory').value;
    const language=document.getElementById('tcLanguage').value;
    const body=document.getElementById('tcBody').value.trim();
    const footer=document.getElementById('tcFooter').value.trim();
    const err=document.getElementById('tcError');
    if(!name){ err.textContent='Template name is required.'; err.classList.remove('d-none'); return; }
    if(!body){ err.textContent='Body is required.'; err.classList.remove('d-none'); return; }
    err.classList.add('d-none');
    const components=[];
    // Header
    if(tplHdrType!=='NONE'){
        const hdrComp={type:'HEADER',format:tplHdrType};
        if(tplHdrType==='TEXT'){ hdrComp.text=document.getElementById('tcHeaderText')?.value.trim()||''; }
        else if(tplHdrType==='IMAGE'){
            const handle=document.getElementById('tcHeaderImage')?.value;
            if(!handle){ err.textContent='Please upload a sample image.'; err.classList.remove('d-none'); return; }
            hdrComp.example={header_handle:[handle]};
        }
        else if(tplHdrType==='DOCUMENT'){
            const url=document.getElementById('tcHeaderDoc')?.value.trim();
            if(!url){ err.textContent='Document URL is required.'; err.classList.remove('d-none'); return; }
            hdrComp.example={header_handle:[url]};
        }
        else if(tplHdrType==='VIDEO'){
            const url=document.getElementById('tcHeaderVideo')?.value.trim();
            if(!url){ err.textContent='Video URL is required.'; err.classList.remove('d-none'); return; }
            hdrComp.example={header_handle:[url]};
        }
        components.push(hdrComp);
    }
    // Body
    const vars=extractTemplateVars(body);
    const bodyComp={type:'BODY',text:body};
    if(vars.length>0){
        const samples=vars.map((_,i)=>{
            const el=document.getElementById('tcVarSample'+i);
            return el?.value.trim()||('sample_value_'+(i+1));
        });
        bodyComp.example={body_text:[samples]};
    }
    components.push(bodyComp);
    if(footer) components.push({type:'FOOTER',text:footer});
    // Buttons
    const btnEls=document.querySelectorAll('#tcBtnList .btn-item');
    if(btnEls.length){
        const btnTypes=[...btnEls].map(b=>b.dataset.btnType||b.querySelector('.btn-type-opt.active')?.dataset.type||'QUICK_REPLY');
        const phoneCount=btnTypes.filter(t=>t==='PHONE_NUMBER').length;
        const urlCount=btnTypes.filter(t=>t==='URL').length;
        if(phoneCount>1){ err.textContent='Only 1 phone number button allowed.'; err.classList.remove('d-none'); return; }
        if(urlCount>1){ err.textContent='Only 1 URL button allowed.'; err.classList.remove('d-none'); return; }
        const buttons=[...btnEls].map(b=>{
            const type=b.dataset.btnType||b.querySelector('.btn-type-opt.active')?.dataset.type||'QUICK_REPLY';
            const text=b.querySelector('.btn-label')?.value.trim()||'Button';
            const value=b.querySelector('.btn-value')?.value.trim()||'';
            if(type==='PHONE_NUMBER') return {type:'PHONE_NUMBER',text,phone_number:value};
            if(type==='URL') return {type:'URL',text,url:value};
            return {type:'QUICK_REPLY',text};
        });
        components.push({type:'BUTTONS',buttons});
    }
    const btn=document.getElementById('tcSubmitBtn');
    btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
    fetch('/api/templates/create',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({name,category,language,body,components})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Submit to Meta';
        if(d.error){ err.textContent=d.error; err.classList.remove('d-none'); return; }
        showToast('Template submitted! Awaiting Meta review.','success');
        tplToggleCreate();
        setTimeout(()=>tplLoad(), 2000);
    }).catch(()=>{btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Submit to Meta'; err.textContent='Network error.'; err.classList.remove('d-none');});
}


/* ── SEND ── */
function tplOpenSend(name){
    tplSelTpl=tplAll.find(t=>t.name===name);
    if(!tplSelTpl) return;
    const phs=[...new Set((tplSelTpl.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const hasImg=tplSelTpl.components&&tplSelTpl.components.some(c=>(c.type||'').toUpperCase()==='HEADER'&&(c.format||'').toUpperCase()==='IMAGE');
    document.getElementById('tplSendPhone').value='';
    document.getElementById('tplSendLang').value=tplSelTpl.language||'en_US';
    document.getElementById('tplSendVarFields').innerHTML=phs.map((ph,i)=>`
        <div class="mb-3">
            <label for="tplVar${i}" class="form-label fw-semibold" style="font-size:12px;">Variable ${i+1} (${escHtml(ph)})</label>
            <input type="text" id="tplSendVar${i}" class="form-control rounded-3" placeholder="Value for ${escHtml(ph)}">
        </div>`).join('');
    document.getElementById('tplSendImgField').style.display=hasImg?'block':'none';
    document.getElementById('tplSendErr').classList.add('d-none');
    tplSendModal.show();
}

function tplConfirmSend(){
    const phone=document.getElementById('tplSendPhone').value.trim().replace(/\D/g,'');
    const lang=document.getElementById('tplSendLang').value.trim()||'en_US';
    const err=document.getElementById('tplSendErr');
    if(!phone||phone.length<8){err.textContent='Enter a valid phone number.';err.classList.remove('d-none');return;}
    const phs=[...new Set((tplSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const params=[];
    for(let i=0;i<phs.length;i++){
        const v=document.getElementById(`tplSendVar${i}`)?.value.trim();
        if(!v){err.textContent=`Fill variable ${i+1}.`;err.classList.remove('d-none');return;}
        params.push(v);
    }
    const headerImage=document.getElementById('tplSendImg')?.value.trim()||'';
    err.classList.add('d-none');
    const btn=document.getElementById('tplSendConfirmBtn'); btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Sending…';
    fetch('/chat/send-template',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({recipient_phone:phone,template_name:tplSelTpl.name,language_code:lang,parameters:params,header_image:headerImage})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Send';
        if(d.error){err.textContent=d.error;err.classList.remove('d-none');return;}
        tplSendModal.hide(); showToast('✅ Template sent!','success');
    }).catch(()=>{btn.disabled=false; btn.innerHTML='<i class="bi bi-send me-1"></i>Send'; err.textContent='Network error.'; err.classList.remove('d-none');});
}

/* ── DELETE ── */
function tplOpenPreview(name){
    const t = tplAll.find(x=>x.name===name); if(!t) return;
    const body = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='BODY')?.text||'';
    const footer = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='FOOTER')?.text||'';
    const hdr = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='HEADER');
    const btns = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='BUTTONS')?.buttons||[];

    let hdrHtml = '';
    if(hdr){
        if(hdr.format==='TEXT') hdrHtml=`<div style="font-weight:800;font-size:15px;margin-bottom:8px;">${escHtml(hdr.text||'')}</div>`;
        else if(hdr.format==='IMAGE') hdrHtml=`<div style="background:#e0e0e0;height:140px;border-radius:8px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;font-size:32px;">🖼️</div>`;
        else if(hdr.format==='DOCUMENT') hdrHtml=`<div style="background:#f5f5f5;padding:12px;border-radius:8px;margin-bottom:8px;display:flex;align-items:center;gap:8px;"><i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size:24px;"></i><span style="font-size:13px;font-weight:600;">Document</span></div>`;
        else if(hdr.format==='VIDEO') hdrHtml=`<div style="background:#e0e0e0;height:140px;border-radius:8px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;font-size:32px;">🎥</div>`;
    }

    const bodyHtml = escHtml(body).replace(/\n/g,'<br>').replace(/\*([^*]+)\*/g,'<strong>$1</strong>').replace(/_([^_]+)_/g,'<em>$1</em>');
    const btnsHtml = btns.map(b=>`<div style="border-top:1px solid #e5e5e5;padding:10px;text-align:center;color:#1976d2;font-size:14px;font-weight:600;">${b.type==='PHONE_NUMBER'?'📞 ':b.type==='URL'?'🔗 ':b.type==='QUICK_REPLY'?'↩️ ':''}${escHtml(b.text)}</div>`).join('');

    const modal = document.createElement('div');
    modal.style.cssText='position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);';
    modal.innerHTML=`<div style="background:#fff;border-radius:20px;padding:28px;max-width:400px;width:90%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div>
                <div style="font-size:16px;font-weight:800;color:#1a1a2e;">${escHtml(t.name)}</div>
                <div style="font-size:12px;color:#888;margin-top:2px;">${t.category||''} · ${t.language||''}</div>
            </div>
            <button onclick="this.closest('div[style*=fixed]').remove()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#aaa;">✕</button>
        </div>
        <div style="background:#e5ddd5;border-radius:16px;padding:16px;">
            <div style="background:#fff;border-radius:12px;padding:14px;max-width:280px;margin:0 auto;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                ${hdrHtml}
                <div style="font-size:14px;line-height:1.6;color:#1a1a2e;">${bodyHtml}</div>
                ${footer?`<div style="font-size:12px;color:#888;margin-top:8px;">${escHtml(footer)}</div>`:''}
            </div>
            ${btnsHtml?`<div style="background:#fff;border-radius:12px;margin-top:8px;overflow:hidden;max-width:280px;margin:8px auto 0;">${btnsHtml}</div>`:''}
        </div>
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button onclick="this.closest('div[style*=fixed]').remove()" style="flex:1;padding:12px;border:1.5px solid #e5e5e5;border-radius:10px;background:#f9f9f9;font-size:14px;font-weight:600;cursor:pointer;">Close</button>
            <button onclick="this.closest('div[style*=fixed]').remove();tplOpenEdit('${escHtml(t.name)}')" style="flex:1;padding:12px;border:none;border-radius:10px;background:#f57c00;color:#fff;font-size:14px;font-weight:600;cursor:pointer;">✏️ Edit</button>
            ${t.status==='APPROVED'?`<button onclick="this.closest('div[style*=fixed]').remove();tplOpenSend('${escHtml(t.name)}')" style="flex:1;padding:12px;border:none;border-radius:10px;background:#25d366;color:#fff;font-size:14px;font-weight:600;cursor:pointer;">📤 Send</button>`:''}
        </div>
    </div>`;
    document.body.appendChild(modal);
    modal.addEventListener('click', e=>{ if(e.target===modal) modal.remove(); });
}

function tplOpenEdit(name){
    const t = tplAll.find(x=>x.name===name); if(!t) return;
    // Open create panel and pre-fill
    const panel = document.getElementById('tplCreatePanel');
    if(!panel.classList.contains('open')) tplToggleCreate();
    setTimeout(()=>{
        // Fill name
        const nameEl = document.getElementById('tcName'); if(nameEl) nameEl.value = t.name;
        // Fill category
        const catEl = document.getElementById('tcCategory'); if(catEl) catEl.value = t.category||'MARKETING';
        // Fill language
        const langEl = document.getElementById('tcLanguage'); if(langEl) langEl.value = t.language||'en_US';
        // Fill body
        const body = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='BODY')?.text||'';
        const bodyEl = document.getElementById('tcBody'); if(bodyEl){ bodyEl.value=body; tplUpdatePreview(); tplRenderVarSamples(); }
        // Fill footer
        const footer = (t.components||[]).find(c=>(c.type||'').toUpperCase()==='FOOTER')?.text||'';
        const footerEl = document.getElementById('tcFooter'); if(footerEl) footerEl.value=footer;
        panel.scrollIntoView({behavior:'smooth'});
        showToast('📝 Template loaded for editing. Change name to create new version.','success');
    }, 300);
}

function tplAskDelete(name){ tplDelName=name; tplDeleteModal.show(); }
function tplConfirmDelete(){
    if(!tplDelName) return;
    const btn=document.getElementById('tplDelConfirmBtn'); btn.disabled=true; btn.textContent='Deleting…';
    fetch(`/api/templates/${encodeURIComponent(tplDelName)}`,{
        method:'DELETE',credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.textContent='Delete';
        if(d.error){ showToast('❌ '+d.error,'error'); return; }
        tplDeleteModal.hide(); tplDelName=null;
        showToast('✅ Deleted! Refreshing in 3s…','success');
        setTimeout(()=>tplLoad(), 3000);
    }).catch(()=>{btn.disabled=false; btn.textContent='Delete'; showToast('❌ Failed','error');});
}
</script>
@endpush