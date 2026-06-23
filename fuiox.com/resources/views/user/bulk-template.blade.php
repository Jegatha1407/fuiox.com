@extends('layouts.app')

@section('title', 'Bulk Send')
@section('page_title', 'Bulk Template Send')

@section('page_styles')
/* Step indicator */
.bulk-steps { display:flex; gap:0; margin-bottom:28px; }
.bulk-step { flex:1; text-align:center; position:relative; }
.bulk-step::before { content:''; display:block; height:3px; background:#f0f0f0; position:absolute; top:15px; left:50%; right:-50%; z-index:0; }
.bulk-step:last-child::before { display:none; }
.bulk-step-circle { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; border:2px solid #e0e0e0; background:#fff; color:#aaa; position:relative; z-index:1; }
.bulk-step.active .bulk-step-circle { border-color:#25d366; background:#25d366; color:#fff; }
.bulk-step.done .bulk-step-circle   { border-color:#25d366; background:#e8f5e9; color:#25d366; }
.bulk-step.done::before, .bulk-step.active::before { background:#25d366; }
.bulk-step-label { font-size:11px; font-weight:600; color:#aaa; margin-top:5px; }
.bulk-step.active .bulk-step-label, .bulk-step.done .bulk-step-label { color:#25d366; }

/* Template picker */
.tpl-pick { border:1.5px solid #e9edef; border-radius:12px; padding:14px 16px; cursor:pointer; transition:0.15s; }
.tpl-pick:hover { border-color:#25d366; background:#f0fdf4; }
.tpl-pick.selected { border-color:#25d366; background:#e8f5e9; }
.tpl-pick-name { font-size:14px; font-weight:700; color:#1a1a2e; }
.tpl-pick-prev { font-size:12px; color:#667781; margin-top:3px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.tpl-pick-lang { font-size:11px; font-weight:600; background:#f0f0f0; color:#666; padding:2px 8px; border-radius:20px; display:inline-block; margin-top:5px; }
.tpl-pick.selected .tpl-pick-lang { background:#c8e6c9; color:#2e7d32; }

/* Contact source tabs */
.source-tab { padding:10px 20px; border:1.5px solid #e5e5e5; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; color:#666; transition:0.15s; background:#fff; display:flex; align-items:center; gap:8px; }
.source-tab:hover { border-color:#25d366; color:#25d366; }
.source-tab.active { border-color:#25d366; background:#e8f5e9; color:#25d366; }

/* Recipient list */
.recipient-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:8px; border:1px solid #f0f0f0; background:#fafafa; margin-bottom:6px; }
.recipient-av { width:32px; height:32px; border-radius:50%; background:#e8f5e9; color:#2e7d32; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.recipient-name { font-size:13px; font-weight:600; color:#1a1a2e; }
.recipient-phone { font-size:11px; color:#888; }
.recipient-remove { margin-left:auto; background:none; border:none; color:#aaa; cursor:pointer; font-size:16px; padding:2px 6px; border-radius:4px; }
.recipient-remove:hover { color:#e53935; background:#fdecea; }

/* Variable fields */
.var-field-wrap { background:#f9fafb; border-radius:10px; padding:14px 16px; border:1px solid #e5e5e5; }
.var-field-label { font-size:12px; font-weight:700; color:#555; margin-bottom:4px; display:block; text-transform:uppercase; letter-spacing:0.3px; }

/* Preview card */
.preview-card { background:#e9fbe5; border-radius:16px; padding:18px; position:relative; max-width:320px; }
.preview-card::before { content:''; position:absolute; top:0; right:-8px; border:8px solid transparent; border-top-color:#e9fbe5; border-right:0; }
.preview-card-text { font-size:14px; color:#111b21; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
.preview-meta { font-size:11px; color:#667781; text-align:right; margin-top:6px; }

/* Progress ring / send result */
.send-result-item { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:8px; margin-bottom:6px; font-size:13px; }
.send-result-item.success { background:#e8f5e9; color:#2e7d32; }
.send-result-item.fail    { background:#fdecea; color:#c62828; }
.send-result-item.pending { background:#f5f5f5; color:#666; }
.send-progress-bar { height:8px; border-radius:4px; background:#f0f0f0; overflow:hidden; margin-bottom:8px; }
.send-progress-fill { height:100%; background:#25d366; border-radius:4px; transition:width 0.4s; }

/* Upload zone */
.upload-zone { border:2px dashed #e5e5e5; border-radius:12px; padding:32px; text-align:center; cursor:pointer; transition:0.15s; }
.upload-zone:hover, .upload-zone.dragover { border-color:#25d366; background:#f0fdf4; }
.upload-zone-icon { font-size:40px; opacity:0.4; margin-bottom:10px; }

/* Empty state */
.empty-box { padding:40px 20px; text-align:center; }
.empty-box-icon { font-size:48px; opacity:0.2; margin-bottom:12px; }
@endsection

@section('content')

<div class="row g-4">

    <!-- ── LEFT: STEPS ── -->
    <div class="col-lg-8">
        <div class="card fu-card">
            <div class="card-body p-4">

                <!-- Step indicator -->
                <div class="bulk-steps" id="bulkSteps">
                    <div class="bulk-step active" id="bsDot1"><div class="bulk-step-circle">1</div><div class="bulk-step-label">Template</div></div>
                    <div class="bulk-step" id="bsDot2"><div class="bulk-step-circle">2</div><div class="bulk-step-label">Recipients</div></div>
                    <div class="bulk-step" id="bsDot3"><div class="bulk-step-circle">3</div><div class="bulk-step-label">Variables</div></div>
                    <div class="bulk-step" id="bsDot4"><div class="bulk-step-circle">4</div><div class="bulk-step-label">Send</div></div>
                </div>

                <!-- ── STEP 1: Template ── -->
                <div id="bsStep1">
                    <h6 class="fw-bold mb-1">Select Template</h6>
                    <p class="text-muted mb-3" style="font-size:13px;">Choose an approved WhatsApp template to send.</p>

                    <div class="mb-3 position-relative">
                        <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
                        <input type="text" id="bsTplSearch" class="form-control rounded-3" placeholder="Search templates…"
                            style="padding-left:34px;" oninput="bsFilterTpls()">
                    </div>

                    <div id="bsTplList" style="max-height:380px;overflow-y:auto;">
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border text-success spinner-border-sm me-2"></div>Loading templates…
                        </div>
                    </div>
                    <div id="bsStep1Err" class="alert alert-danger d-none rounded-3 py-2 mt-3" style="font-size:13px;"></div>
                </div>

                <!-- ── STEP 2: Recipients ── -->
                <div id="bsStep2" style="display:none;">
                    <h6 class="fw-bold mb-1">Choose Recipients</h6>
                    <p class="text-muted mb-3" style="font-size:13px;">Select how you want to add phone numbers.</p>

                    <!-- Source tabs -->
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <button class="source-tab active" id="srcTabContacts" onclick="bsSwitchSource('contacts')">
                            <i class="bi bi-people-fill"></i>From Contacts
                        </button>
                        <button class="source-tab" id="srcTabGroup" onclick="bsSwitchSource('group')">
                            <i class="bi bi-collection-fill"></i>By Group
                        </button>
                        <button class="source-tab" id="srcTabExcel" onclick="bsSwitchSource('excel')">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i>Upload Excel/CSV
                        </button>
                        <button class="source-tab" id="srcTabManual" onclick="bsSwitchSource('manual')">
                            <i class="bi bi-keyboard"></i>Enter Manually
                        </button>
                    </div>

                    <!-- From Contacts -->
                    <div id="srcContacts">
                        <div class="mb-2 position-relative">
                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
                            <input type="text" id="bsConSearch" class="form-control form-control-sm rounded-3" placeholder="Search contacts…" style="padding-left:30px;" oninput="bsFilterContacts()">
                        </div>
                        <div id="bsConList" style="max-height:260px;overflow-y:auto;border:1px solid #f0f0f0;border-radius:10px;"></div>
                    </div>

                    <!-- By Group -->
                    <div id="srcGroup" style="display:none;">
                        <select id="bsGroupSel" class="form-select rounded-3 mb-2" onchange="bsSelectGroup()">
                            <option value="">— Select group —</option>
                        </select>
                        <div id="bsGroupInfo" class="text-muted" style="font-size:13px;"></div>
                    </div>

                    <!-- Upload Excel/CSV -->
                    <div id="srcExcel" style="display:none;">
                        <div class="upload-zone" id="bsUploadZone" onclick="document.getElementById('bsFileInput').click()"
                            ondragover="event.preventDefault();this.classList.add('dragover')"
                            ondragleave="this.classList.remove('dragover')"
                            ondrop="bsHandleDrop(event)">
                            <input type="file" id="bsFileInput" accept=".csv,.xlsx,.xls" style="display:none;" onchange="bsHandleFile(this)">
                            <div class="upload-zone-icon">📊</div>
                            <div class="fw-semibold mb-1" style="font-size:14px;">Drop your CSV or Excel file here</div>
                            <div class="text-muted" style="font-size:12px;">or click to browse. Columns: name, phone (required)</div>
                        </div>
                        <div id="bsFileInfo" class="mt-2"></div>
                    </div>

                    <!-- Manual -->
                    <div id="srcManual" style="display:none;">
                        <label for="bsManualNums" class="form-label fw-semibold" style="font-size:12px;">Phone Numbers <span class="text-muted fw-normal">(with country code, one per line or comma separated)</span></label>
                        <textarea id="bsManualNums" class="form-control rounded-3" rows="6"
                            placeholder="919876543210&#10;919123456789&#10;919087654321"
                            oninput="bsParseManual()"></textarea>
                        <div id="bsManualInfo" class="text-muted mt-1" style="font-size:12px;"></div>
                    </div>

                    <!-- Selected recipients -->
                    <div class="mt-3" id="bsSelectedWrap" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold" style="font-size:13px;">Selected: <span id="bsSelectedCount" class="text-success">0</span></span>
                            <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="bsClearRecipients()"><i class="bi bi-x me-1"></i>Clear All</button>
                        </div>
                        <div id="bsSelectedList" style="max-height:200px;overflow-y:auto;"></div>
                    </div>

                    <div id="bsStep2Err" class="alert alert-danger d-none rounded-3 py-2 mt-3" style="font-size:13px;"></div>
                </div>

                <!-- ── STEP 3: Variables ── -->
                <div id="bsStep3" style="display:none;">
                    <h6 class="fw-bold mb-1">Template Variables</h6>
                    <p class="text-muted mb-3" style="font-size:13px;">Fill in the variables for your template message.</p>

                    <div id="bsVarSection">
                        <div class="alert alert-success rounded-3 py-2" style="font-size:13px;">
                            <i class="bi bi-check-circle me-1"></i>This template has no variables. You can proceed directly.
                        </div>
                    </div>

                    <div id="bsStep3Err" class="alert alert-danger d-none rounded-3 py-2 mt-3" style="font-size:13px;"></div>
                </div>

                <!-- ── STEP 4: Send ── -->
                <div id="bsStep4" style="display:none;">
                    <h6 class="fw-bold mb-1">Ready to Send</h6>
                    <p class="text-muted mb-3" style="font-size:13px;">Review and send your bulk template messages.</p>

                    <!-- Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded-3 border" style="background:#f9fafb;">
                                <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Template</div>
                                <div class="fw-bold mt-1" style="font-size:14px;" id="bsRevTpl">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border" style="background:#f9fafb;">
                                <div class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;">Recipients</div>
                                <div class="fw-bold mt-1" style="font-size:14px;" id="bsRevCount">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Send button -->
                    <div id="bsSendSection">
                        <div class="d-flex gap-2">
                            <button class="btn btn-fu-primary rounded-3 flex-fill py-3" id="bsSendBtn" onclick="bsSend(false)" style="font-size:15px;"><i class="bi bi-send-fill me-2"></i>Send to <span id="bsSendBtnCount">0</span></button>
                            <button class="btn btn-success rounded-3 flex-fill py-3" onclick="bsSend(true)" style="font-size:15px;"><i class="bi bi-person-check-fill me-2"></i>Send &amp; Save</button>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div id="bsProgress" style="display:none;">
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                            <span>Sending…</span>
                            <span id="bsProgressText">0 / 0</span>
                        </div>
                        <div class="send-progress-bar"><div class="send-progress-fill" id="bsProgressFill" style="width:0%"></div></div>
                    </div>

                    <!-- Results -->
                    <div id="bsResults" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                            <h6 class="fw-bold mb-0">Send Results</h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success rounded-pill px-3" id="bsResSuccess">0 sent</span>
                                <span class="badge bg-danger rounded-pill px-3" id="bsResFailed">0 failed</span>
                            </div>
                        </div>
                        <div id="bsResultList" style="max-height:300px;overflow-y:auto;"></div>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill mt-3" onclick="bsReset()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Send Another
                        </button>
                    </div>

                    <div id="bsStep4Err" class="alert alert-danger d-none rounded-3 py-2 mt-3" style="font-size:13px;"></div>
                </div>

                <!-- Nav buttons -->
                <div class="d-flex gap-2 mt-4 pt-3 border-top" id="bsNavBtns">
                    <button class="btn btn-light rounded-3 d-none" id="bsBtnBack" onclick="bsPrev()"><i class="bi bi-arrow-left me-1"></i>Back</button>
                    <button class="btn btn-fu-primary rounded-3 ms-auto" id="bsBtnNext" onclick="bsNext()">Next <i class="bi bi-arrow-right ms-1"></i></button>
                </div>

            </div>
        </div>
    </div>

    <!-- ── RIGHT: PREVIEW ── -->
    <div class="col-lg-4">
        <!-- Template Preview -->
        <div class="card fu-card mb-3" id="bsPreviewCard">
            <div class="card-header"><i class="bi bi-phone me-2"></i>Live Preview</div>
            <div class="card-body" style="background:#f0f2f5;min-height:200px;border-radius:0 0 14px 14px;">
                <div id="bsPreviewEmpty" class="empty-box">
                    <div class="empty-box-icon">📱</div>
                    <p class="text-muted" style="font-size:13px;">Select a template to see a live preview</p>
                </div>
                <div id="bsPreviewContent" style="display:none;padding:12px 0;">
                    <div class="preview-card">
                        <div class="preview-card-text" id="bsPreviewText"></div>
                        <div class="preview-meta">12:00 ✓✓</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Tips -->
        <div class="card fu-card">
            <div class="card-header"><i class="bi bi-lightbulb me-2 text-warning"></i>Tips</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0" style="font-size:13px;color:#555;">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Only APPROVED templates can be sent</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Phone numbers must include country code</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>CSV/Excel: columns must be <code>name</code> and <code>phone</code></li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Messages are sent one by one to avoid spam</li>
                    <li class="mb-0"><i class="bi bi-info-circle-fill text-primary me-2"></i>Meta charges per conversation — check your wallet</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let bsStep = 1;
let bsAllTpls = [], bsSelTpl = null;
let bsAllContacts = [], bsFilteredContacts = [];
let bsRecipients = []; // [{name, phone}]
let bsSource = 'contacts';
let bsGroups = [];

document.addEventListener('DOMContentLoaded', () => {
    bsLoadTpls();
    bsLoadContacts();
    bsLoadGroups();
});

/* ── STEP NAVIGATION ── */
function bsGoStep(n) {
    document.getElementById('bsStep'+bsStep).style.display = 'none';
    document.getElementById('bsDot'+bsStep).classList.remove('active');
    if (n > bsStep) document.getElementById('bsDot'+bsStep).classList.add('done');
    else document.getElementById('bsDot'+bsStep).classList.remove('done');
    bsStep = n;
    document.getElementById('bsStep'+bsStep).style.display = 'block';
    document.getElementById('bsDot'+bsStep).classList.add('active');
    document.getElementById('bsBtnBack').classList.toggle('d-none', bsStep === 1);
    const nextBtn = document.getElementById('bsBtnNext');
    if (bsStep === 4) { nextBtn.style.display='none'; document.getElementById('bsNavBtns').style.display='none'; }
    else { nextBtn.style.display=''; document.getElementById('bsNavBtns').style.display='flex'; nextBtn.innerHTML = 'Next <i class="bi bi-arrow-right ms-1"></i>'; }
    if (bsStep === 3) bsBuildVarFields();
    if (bsStep === 4) bsBuildReview();
}

function bsNext() {
    if (bsStep === 1) {
        const err = document.getElementById('bsStep1Err');
        if (!bsSelTpl) { err.textContent='Please select a template.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none'); bsGoStep(2);
    } else if (bsStep === 2) {
        const err = document.getElementById('bsStep2Err');
        if (!bsRecipients.length) { err.textContent='Please add at least one recipient.'; err.classList.remove('d-none'); return; }
        err.classList.add('d-none'); bsGoStep(3);
    } else if (bsStep === 3) {
        const err = document.getElementById('bsStep3Err');
        const phs = [...new Set((bsSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
        for (let i=0; i<phs.length; i++) {
            const v = document.getElementById('bsVar'+i)?.value.trim();
            if (!v) { err.textContent=`Please fill variable ${i+1}.`; err.classList.remove('d-none'); return; }
        }
        err.classList.add('d-none'); bsGoStep(4);
    }
}
function bsPrev() { if (bsStep > 1) bsGoStep(bsStep-1); }

/* ── TEMPLATES ── */
function bsLoadTpls() {
    fetch('/templates/meta').then(r=>r.json()).then(data => {
        bsAllTpls = (data.templates||[]).filter(t=>t.status==='APPROVED');
        bsRenderTpls(bsAllTpls);
    }).catch(() => { document.getElementById('bsTplList').innerHTML='<div class="text-center text-danger py-3">Could not load templates.</div>'; });
}
function bsFilterTpls() { const q=document.getElementById('bsTplSearch').value.toLowerCase(); bsRenderTpls(bsAllTpls.filter(t=>(`${t.name} ${t.preview}`).toLowerCase().includes(q))); }
function bsRenderTpls(tpls) {
    const box = document.getElementById('bsTplList');
    if (!tpls.length) { box.innerHTML='<div class="text-center text-muted py-4">No approved templates found.</div>'; return; }
    box.innerHTML = `<div class="row g-2">${tpls.map(t => {
        const sel = bsSelTpl?.name===t.name ? 'selected' : '';
        return `<div class="col-md-6">
            <div class="tpl-pick ${sel}" onclick="bsSelectTpl('${t.name.replace(/'/g,"\\'")}')">
                <div class="tpl-pick-name">${escHtml(t.name)}</div>
                <div class="tpl-pick-prev">${escHtml(t.preview||'No preview')}</div>
                <span class="tpl-pick-lang">${escHtml(t.language)}</span>
                ${bsSelTpl?.name===t.name?'<span class="ms-1 badge bg-success rounded-pill" style="font-size:10px;">Selected ✓</span>':''}
            </div>
        </div>`;
    }).join('')}</div>`;
}
function bsSelectTpl(name) {
    bsSelTpl = bsAllTpls.find(t=>t.name===name);
    bsRenderTpls(bsAllTpls);
    bsUpdatePreview();
}
function bsUpdatePreview() {
    if (!bsSelTpl) return;
    const phs = [...new Set((bsSelTpl.preview||'').match(/\{\{\d+\}\}/g)||[])];
    let text = bsSelTpl.preview||'';
    phs.forEach((ph,i) => {
        const v = document.getElementById('bsVar'+i)?.value || ph;
        text = text.replace(ph, `*${v}*`);
    });
    document.getElementById('bsPreviewText').textContent = text;
    document.getElementById('bsPreviewEmpty').style.display='none';
    document.getElementById('bsPreviewContent').style.display='block';
}

/* ── VAR FIELDS ── */
function bsBuildVarFields() {
    const phs = [...new Set((bsSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const hasImg = bsSelTpl?.components && bsSelTpl.components.some(c=>
        (c.type||'').toUpperCase()==='HEADER' && (c.format||'').toUpperCase()==='IMAGE'
    );
    const sec = document.getElementById('bsVarSection');
    let html = '';
    if (hasImg) {
        html += `<div class="p-3 rounded-3 mb-3" style="background:#fff8e1;border:1px solid #ffe082;">
            <div class="fw-bold mb-2" style="font-size:12px;color:#e65100;text-transform:uppercase;"><i class="bi bi-image me-1"></i>Header Image Required</div>
            <input type="text" id="bsHeaderImg" class="form-control rounded-3" placeholder="https://example.com/image.jpg" oninput="bsUpdatePreview()">
            <div class="form-text mt-1">Must be a direct public HTTPS image URL ending in .jpg or .png</div>
        </div>`;
    }
    if (phs.length) {
        html += `<div class="var-field-wrap">
            <div class="fw-bold mb-3" style="font-size:13px;color:#1a1a2e;">Fill in template variables:</div>
            ${phs.map((ph,i)=>`<div class="mb-3">
                <label for="btVar${i}" class="var-field-label">Variable ${i+1} <span class="text-muted fw-normal text-lowercase" style="letter-spacing:0;">(${escHtml(ph)})</span></label>
                <input type="text" id="bsVar${i}" class="form-control rounded-3" placeholder="Value for ${escHtml(ph)}" oninput="bsUpdatePreview()">
            </div>`).join('')}
        </div>`;
    }
    if (!html) {
        html = `<div class="alert alert-success rounded-3 py-2" style="font-size:13px;"><i class="bi bi-check-circle me-1"></i>This template has no variables. You can proceed directly.</div>`;
    }
    sec.innerHTML = html;
}

/* ── REVIEW ── */
function bsBuildReview() {
    document.getElementById('bsRevTpl').textContent   = bsSelTpl?.name || '—';
    document.getElementById('bsRevCount').textContent = bsRecipients.length + ' recipients';
    document.getElementById('bsSendBtnCount').textContent = bsRecipients.length;
    bsUpdatePreview();
}

/* ── CONTACTS ── */
function bsLoadContacts() {
    fetch('/contacts/list').then(r=>r.json()).then(data => {
        bsAllContacts = data.contacts||data||[];
        bsRenderConList(bsAllContacts);
    }).catch(()=>{});
}
function bsFilterContacts() { const q=document.getElementById('bsConSearch').value.toLowerCase(); bsRenderConList(bsAllContacts.filter(c=>(c.name||'').toLowerCase().includes(q)||(c.phone||'').includes(q))); }
function bsRenderConList(list) {
    const box = document.getElementById('bsConList');
    if (!list.length) { box.innerHTML='<div class="text-center text-muted py-3" style="font-size:13px;">No contacts found.</div>'; return; }
    box.innerHTML = list.map(c => {
        const sel = bsRecipients.some(r=>r.phone===c.phone);
        return `<div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="cursor:pointer;background:${sel?'#f0fdf4':'#fff'};" onclick="bsToggleContact('${c.phone}','${escHtml(c.name||c.phone).replace(/'/g,"\\'")}')">
            <input type="checkbox" ${sel?'checked':''} readonly onclick="event.stopPropagation();">
            <div class="recipient-av" style="width:28px;height:28px;font-size:11px;">${escHtml((c.name||c.phone).slice(0,2).toUpperCase())}</div>
            <div style="flex:1;overflow:hidden;">
                <div style="font-size:13px;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(c.name||'—')}</div>
                <div style="font-size:11px;color:#888;">${escHtml(c.phone)}</div>
            </div>
        </div>`;
    }).join('');
}
function bsToggleContact(phone, name) {
    const idx = bsRecipients.findIndex(r=>r.phone===phone);
    if (idx >= 0) bsRecipients.splice(idx,1);
    else bsRecipients.push({phone, name});
    bsRenderConList(bsFilteredContacts.length ? bsFilteredContacts : bsAllContacts);
    bsRenderSelected();
}

/* ── GROUPS ── */
function bsLoadGroups() {
    fetch('/contacts/groups').then(r=>r.json()).then(data => {
        bsGroups = data.groups||[];
        const sel = document.getElementById('bsGroupSel');
        bsGroups.forEach(g => { const o=document.createElement('option'); o.value=g; o.textContent=g; sel.appendChild(o); });
    }).catch(()=>{});
}
function bsSelectGroup() {
    const g = document.getElementById('bsGroupSel').value;
    if (!g) return;
    const grouped = bsAllContacts.filter(c=>c.group===g);
    bsRecipients = grouped.map(c=>({phone:c.phone, name:c.name||c.phone}));
    document.getElementById('bsGroupInfo').textContent = `${grouped.length} contacts in group "${g}" selected.`;
    bsRenderSelected();
}

/* ── SOURCE SWITCH ── */
function bsSwitchSource(src) {
    bsSource = src;
    ['contacts','group','excel','manual'].forEach(s => {
        document.getElementById('src'+s.charAt(0).toUpperCase()+s.slice(1)).style.display = s===src?'block':'none';
        document.getElementById('srcTab'+s.charAt(0).toUpperCase()+s.slice(1)).classList.toggle('active', s===src);
    });
}

/* ── FILE UPLOAD ── */
function bsHandleDrop(e) { e.preventDefault(); document.getElementById('bsUploadZone').classList.remove('dragover'); const file=e.dataTransfer.files[0]; if(file) bsProcessFile(file); }
function bsHandleFile(input) { if(input.files[0]) bsProcessFile(input.files[0]); }
function bsProcessFile(file) {
    const info = document.getElementById('bsFileInfo');
    info.innerHTML = `<div class="d-flex align-items-center gap-2 text-muted" style="font-size:13px;"><div class="spinner-border spinner-border-sm text-success"></div>Processing ${escHtml(file.name)}…</div>`;
    const fd = new FormData(); fd.append('excel_file', file);
    fetch('/bulk-template/preview', {
        method:'POST', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:fd
    }).then(r=>r.json()).then(data => {
        if(data.error){ info.innerHTML=`<div class="alert alert-danger rounded-3 py-2 mt-2" style="font-size:13px;">${escHtml(data.error)}</div>`; return; }
        const rows = data.rows||data.preview||[];  // all rows
        const headers = data.headers||[];
        const total = data.total||0;
        window.bsFileRows = rows;
        window.bsFileHeaders = headers;
        window.bsFileTotal = total;
        console.log('File rows loaded:', rows.length, 'total from server:', total);
        // Show column selector
        const phoneOpts = headers.map(h=>`<option value="${escHtml(String(h))}" ${String(h).toLowerCase().includes('phone')||String(h).toLowerCase().includes('mobile')?'selected':''}>${escHtml(String(h))}</option>`).join('');
        const nameOpts  = `<option value="">— None —</option>`+headers.map(h=>`<option value="${escHtml(String(h))}" ${String(h).toLowerCase().includes('name')?'selected':''}>${escHtml(String(h))}</option>`).join('');
        info.innerHTML = `
        <div class="p-3 rounded-3 mt-2" style="background:#f9fafb;border:1px solid #e5e5e5;">
            <div class="fw-bold mb-2" style="font-size:13px;">📊 ${total} rows found — Map columns:</div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label for="bsPhoneCol" style="font-size:11px;font-weight:700;color:#666;">Phone Column *</label>
                    <select id="bsPhoneCol" class="form-select form-select-sm rounded-3">${phoneOpts}</select>
                </div>
                <div class="col-6">
                    <label for="bsNameCol" style="font-size:11px;font-weight:700;color:#666;">Name Column</label>
                    <select id="bsNameCol" class="form-select form-select-sm rounded-3">${nameOpts}</select>
                </div>
            </div>
            <div class="mb-2" style="font-size:11px;color:#888;">Preview (first 3 rows):</div>
            <div style="overflow-x:auto;font-size:11px;">
                <table class="table table-sm mb-2" style="font-size:11px;">
                    <thead><tr>${headers.map(h=>`<th>${escHtml(String(h))}</th>`).join('')}</tr></thead>
                    <tbody>${rows.slice(0,3).map(r=>`<tr>${headers.map(h=>`<td>${escHtml(String(r[h]??''))}</td>`).join('')}</tr>`).join('')}</tbody>
                </table>
            </div>
            <button class="btn btn-fu-primary btn-sm rounded-3 w-100" onclick="bsApplyColumns()"><i class="bi bi-check-lg me-1"></i>Confirm Columns & Load Recipients</button>
        </div>`;
    }).catch((e) => { console.error(e); info.innerHTML='<div class="alert alert-danger rounded-3 py-2 mt-2" style="font-size:13px;">Could not parse file.</div>'; });
}

/* ── APPLY COLUMNS ── */
function bsApplyColumns() {
    const phoneCol = document.getElementById('bsPhoneCol')?.value;
    const nameCol  = document.getElementById('bsNameCol')?.value;
    const rows = window.bsFileRows||[];
    if(!phoneCol){ showToast('Please select phone column','error'); return; }
    const allMapped = rows.map(r=>{
        const obj = typeof r==='object'?r:{};
        const phone = String(obj[phoneCol]||'').replace(/\D/g,'');
        const name  = nameCol ? String(obj[nameCol]||'') : '';
        return {phone, name};
    });
    console.log('Total mapped:', allMapped.length, 'Sample:', allMapped.slice(0,3));
    console.log('Filtered out:', allMapped.filter(r=>r.phone.length<8).slice(0,5));
    bsRecipients = allMapped.filter(r=>r.phone.length>=8);
    console.log('Final recipients:', bsRecipients.length);
    const info = document.getElementById('bsFileInfo');
    info.innerHTML = `<div class="alert alert-success rounded-3 py-2 mt-2" style="font-size:13px;"><i class="bi bi-check-circle me-1"></i>Loaded <strong>${bsRecipients.length}</strong> valid recipients. Phone: <strong>${escHtml(phoneCol)}</strong>${nameCol?' | Name: <strong>'+escHtml(nameCol)+'</strong>':''}</div>`;
    bsRenderSelected();
}
/* ── MANUAL PARSE ── */
function bsParseManual() {
    const raw = document.getElementById('bsManualNums').value;
    const nums = raw.split(/[\n,]+/).map(s=>s.trim().replace(/\D/g,'')).filter(s=>s.length>=8);
    bsRecipients = [...new Set(nums)].map(p=>({phone:p,name:''}));
    document.getElementById('bsManualInfo').textContent = `${bsRecipients.length} valid number${bsRecipients.length!==1?'s':''} detected.`;
    bsRenderSelected();
}

/* ── SELECTED LIST ── */
function bsRenderSelected() {
    const wrap = document.getElementById('bsSelectedWrap');
    const list = document.getElementById('bsSelectedList');
    const count = document.getElementById('bsSelectedCount');
    if (!bsRecipients.length) { wrap.style.display='none'; return; }
    wrap.style.display='block';
    count.textContent = bsRecipients.length;
    list.innerHTML = bsRecipients.slice(0,50).map((r,i) => `
        <div class="recipient-item">
            <div class="recipient-av">${(r.name||r.phone).slice(0,2).toUpperCase()}</div>
            <div style="flex:1;overflow:hidden;">
                <div class="recipient-name">${escHtml(r.name||'Unknown')}</div>
                <div class="recipient-phone">${escHtml(r.phone)}</div>
            </div>
            <button class="recipient-remove" onclick="bsRemoveRecipient(${i})"><i class="bi bi-x"></i></button>
        </div>`).join('')
        + (bsRecipients.length > 50 ? `<div class="text-center text-muted py-2" style="font-size:12px;">+${bsRecipients.length-50} more…</div>` : '');
}
function bsRemoveRecipient(i) { bsRecipients.splice(i,1); bsRenderSelected(); }
function bsClearRecipients() { bsRecipients=[]; bsRenderSelected(); bsRenderConList(bsAllContacts); }

/* ── SEND ── */
async function bsSend(saveContacts=false) {
    const phs = [...new Set((bsSelTpl?.preview||'').match(/\{\{\d+\}\}/g)||[])];
    const params = phs.map((_,i)=>document.getElementById('bsVar'+i)?.value.trim()||'');
    const headerImg = document.getElementById('bsHeaderImg')?.value.trim()||'';
    const err = document.getElementById('bsStep4Err'); err.classList.add('d-none');
    const sendBtn = document.getElementById('bsSendSection'); sendBtn.style.display='none';
    const prog = document.getElementById('bsProgress'); prog.style.display='block';
    const results = document.getElementById('bsResults');
    let sent=0, failed=0, done=0, total=bsRecipients.length;
    const resultItems = [];
    const fill  = document.getElementById('bsProgressFill');
    const progTxt = document.getElementById('bsProgressText');

    function updateProgress() { fill.style.width=Math.round(done/total*100)+'%'; progTxt.textContent=done+' / '+total; }
    async function sendNext(idx) {
        if (idx >= total) {
            prog.style.display='none';
            results.style.display='block';
            document.getElementById('bsResSuccess').textContent = sent+' sent';
            document.getElementById('bsResFailed').textContent  = failed+' failed';
            // Save contacts if requested
            if (saveContacts && sent > 0) {
                const toSave = bsRecipients.filter(r => !resultItems.find(x=>x.phone===r.phone&&!x.ok));
                for (const r of toSave) {
                    if (!r.phone) continue;
                    try {
                        await fetch('/contacts', {
                            method:'POST', credentials:'same-origin',
                            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                            body: JSON.stringify({name:r.name||r.phone, phone:r.phone})
                        });
                    } catch(e) {}
                }
                showToast('✅ Contacts saved!', 'success');
            }
            document.getElementById('bsResultList').innerHTML = resultItems.map(r=>
                `<div class="send-result-item ${r.ok?'success':'fail'}">
                    <i class="bi bi-${r.ok?'check-circle-fill':'x-circle-fill'}"></i>
                    <div style="flex:1;overflow:hidden;">
                        <div style="font-weight:600;">${escHtml(r.name||r.phone)}</div>
                        <div style="font-size:11px;opacity:0.8;">${escHtml(r.phone)}</div>
                    </div>
                    <span style="font-size:11px;">${r.ok?'✅ Sent':'❌ '+escHtml(r.error||'Failed')}</span>
                </div>`).join('');
            return;
        }
        const rec = bsRecipients[idx];
        fetch('/chat/send-template', {
            method:'POST', credentials:'same-origin',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({recipient_phone:rec.phone, template_name:bsSelTpl.name, language_code:bsSelTpl.language||'en_US', parameters:params, header_image:headerImg})
        }).then(r=>r.json()).then(d => {
            if (d.error) { failed++; resultItems.push({...rec, ok:false, error:d.error}); }
            else { sent++; resultItems.push({...rec, ok:true}); }
            done++; updateProgress();
            setTimeout(()=>sendNext(idx+1), 400);
        }).catch((e) => { console.error('Send error at idx',idx,e); failed++; resultItems.push({...rec, ok:false, error:'Network error'}); done++; updateProgress(); setTimeout(()=>sendNext(idx+1),400); });
    }
    updateProgress();
    sendNext(0);
}

/* ── RESET ── */
function bsReset() {
    bsStep=1; bsSelTpl=null; bsRecipients=[];
    [1,2,3,4].forEach(i=>{ const d=document.getElementById('bsDot'+i); d.classList.remove('active','done'); if(i===1)d.classList.add('active'); document.getElementById('bsStep'+i).style.display=i===1?'block':'none'; });
    document.getElementById('bsBtnBack').classList.add('d-none');
    document.getElementById('bsBtnNext').style.display='';
    document.getElementById('bsNavBtns').style.display='flex';
    document.getElementById('bsSendSection').style.display='block';
    document.getElementById('bsProgress').style.display='none';
    document.getElementById('bsResults').style.display='none';
    document.getElementById('bsPreviewEmpty').style.display='block';
    document.getElementById('bsPreviewContent').style.display='none';
    bsRenderTpls(bsAllTpls);
    bsRenderSelected();
}
</script>
@endpush