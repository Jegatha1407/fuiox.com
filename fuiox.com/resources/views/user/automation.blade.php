@extends('layouts.app')

@section('title', 'Automation')
@section('page_title', 'Automation')

@section('page_styles')
.auto-stat { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border-left:4px solid #25d366; display:flex; align-items:center; gap:14px; }
.auto-stat.blue   { border-left-color:#1976d2; }
.auto-stat.orange { border-left-color:#f57c00; }
.auto-stat.purple { border-left-color:#7b1fa2; }
.auto-stat-icon  { font-size:26px; }
.auto-stat-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px; }
.auto-stat-value { font-size:24px; font-weight:800; color:#1a1a2e; }

.auto-table th { font-size:12px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.3px; background:#fafafa; border-bottom:2px solid #f0f0f0; padding:11px 16px; white-space:nowrap; }
.auto-table td { font-size:13px; color:#333; padding:13px 16px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.auto-table tbody tr:hover td { background:#fafafa; }

.trigger-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
.trigger-badge.keyword  { background:#e3f2fd; color:#1565c0; }
.trigger-badge.welcome  { background:#e8f5e9; color:#2e7d32; }
.trigger-badge.any      { background:#fff3e0; color:#e65100; }
.trigger-badge.schedule { background:#f3e5f5; color:#6a1b9a; }

.fu-switch-sm { position:relative; width:38px; height:21px; display:inline-block; flex-shrink:0; }
.fu-switch-sm input { display:none; }
.fu-switch-sm-sl { position:absolute; inset:0; background:#ddd; border-radius:21px; cursor:pointer; transition:0.3s; }
.fu-switch-sm-sl::before { content:''; position:absolute; width:15px; height:15px; left:3px; top:3px; background:#fff; border-radius:50%; transition:0.3s; }
.fu-switch-sm input:checked + .fu-switch-sm-sl { background:#25d366; }
.fu-switch-sm input:checked + .fu-switch-sm-sl::before { transform:translateX(17px); }

.action-btn { width:30px; height:30px; border:none; background:transparent; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:14px; transition:0.15s; }
.action-btn.edit:hover   { background:#e3f2fd; color:#1976d2; }
.action-btn.delete:hover { background:#fdecea; color:#e53935; }

.empty-state { padding:60px 20px; text-align:center; }
.empty-state-icon { font-size:60px; opacity:0.2; margin-bottom:16px; }
.empty-state p { color:#aaa; font-size:14px; }

/* Rule card for preview */
.rule-preview { background:#f9fafb; border-radius:10px; padding:14px 16px; border:1px solid #e5e5e5; margin-top:12px; }
.rule-preview-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; margin-bottom:6px; }
.rule-flow { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.rule-chip { font-size:12px; font-weight:600; padding:5px 12px; border-radius:20px; }
.rule-chip.trigger { background:#e3f2fd; color:#1565c0; }
.rule-chip.action  { background:#e8f5e9; color:#2e7d32; }
.rule-arrow { color:#aaa; font-size:16px; }
@endsection

@section('content')

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="auto-stat">
            <div class="auto-stat-icon">🤖</div>
            <div><div class="auto-stat-label">Total Rules</div><div class="auto-stat-value" id="aStatTotal">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="auto-stat blue">
            <div class="auto-stat-icon">✅</div>
            <div><div class="auto-stat-label">Active</div><div class="auto-stat-value" id="aStatActive">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="auto-stat orange">
            <div class="auto-stat-icon">⚡</div>
            <div><div class="auto-stat-label">Triggered Today</div><div class="auto-stat-value" id="aStatToday">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="auto-stat purple">
            <div class="auto-stat-icon">📊</div>
            <div><div class="auto-stat-label">Total Triggered</div><div class="auto-stat-value" id="aStatTriggered">—</div></div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card fu-card">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <span class="fw-bold me-auto">Automation Rules</span>
        <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
            <input type="text" id="aSearch" class="form-control form-control-sm" placeholder="Search rules…"
                style="padding-left:30px;width:200px;border-radius:8px;" oninput="aFilter()">
        </div>
        <select id="aTriggerFilter" class="form-select form-select-sm" style="width:140px;border-radius:8px;" onchange="aFilter()">
            <option value="">All Triggers</option>
            <option value="keyword">Keyword</option>
            <option value="welcome">Welcome</option>
            <option value="any">Any Message</option>
        </select>
        <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="aOpenCreate()">
            <i class="bi bi-plus-lg me-1"></i>New Rule
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table auto-table mb-0">
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Trigger</th>
                        <th>Keywords</th>
                        <th>Response</th>
                        <th>Triggered</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="aTableBody">
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">🤖</div><p>Loading automation rules…</p></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tips Card -->
<div class="card fu-card mt-4">
    <div class="card-header"><i class="bi bi-lightbulb me-2 text-warning"></i>Automation Tips</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #c8e6c9;">
                    <div class="fw-bold mb-1" style="font-size:13px;color:#2e7d32;"><i class="bi bi-key-fill me-1"></i>Keyword Trigger</div>
                    <div class="text-muted" style="font-size:12px;">Replies when a contact sends a specific keyword. Use commas to add multiple keywords (e.g. "hello, hi, hey").</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background:#e3f2fd;border:1px solid #90caf9;">
                    <div class="fw-bold mb-1" style="font-size:13px;color:#1565c0;"><i class="bi bi-door-open-fill me-1"></i>Welcome Message</div>
                    <div class="text-muted" style="font-size:12px;">Fires automatically when a contact messages you for the very first time. Great for onboarding.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3" style="background:#fff3e0;border:1px solid #ffcc80;">
                    <div class="fw-bold mb-1" style="font-size:13px;color:#e65100;"><i class="bi bi-infinity me-1"></i>Any Message</div>
                    <div class="text-muted" style="font-size:12px;">Responds to every incoming message. Use carefully — best for out-of-office or away messages.</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Create/Edit Rule Modal -->
<div class="modal fade" id="aModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="aModalTitle"><i class="bi bi-robot me-2 text-success"></i>Create Automation Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <input type="hidden" id="aEditId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="aName" class="form-label fw-semibold" style="font-size:12px;">Rule Name *</label>
                        <input type="text" id="aName" class="form-control rounded-3" placeholder="e.g. Welcome Auto-Reply">
                    </div>
                    <div class="col-md-6">
                        <label for="aTriggerType" class="form-label fw-semibold" style="font-size:12px;">Trigger Type *</label>
                        <select id="aTriggerType" class="form-select rounded-3" onchange="aToggleTrigger()">
                            <option value="keyword">Keyword — Reply on specific words</option>
                            <option value="welcome">Welcome — First message from contact</option>
                            <option value="any">Any Message — Reply to all messages</option>
                        </select>
                    </div>
                    <div class="col-12" id="aKeywordsWrap">
                        <label for="aKeywords" class="form-label fw-semibold" style="font-size:12px;">Keywords * <span class="text-muted fw-normal">(comma separated)</span></label>
                        <input type="text" id="aKeywords" class="form-control rounded-3" placeholder="e.g. hello, hi, hey, start">
                        <div class="form-text">The rule fires when any of these words are sent (case-insensitive).</div>
                    </div>
                    <div class="col-12">
                        <label for="aRespText" class="form-label fw-semibold" style="font-size:12px;">Response Type *</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="aResponseType" id="aRespText" value="text" checked onchange="aToggleResponse()">
                                <label class="form-check-label" for="aRespText" style="font-size:13px;">Text Message</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="aResponseType" id="aRespTpl" value="template" onchange="aToggleResponse()">
                                <label class="form-check-label" for="aRespTpl" style="font-size:13px;">Template</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="aRespTextWrap">
                        <label for="aReplyMsg" class="form-label fw-semibold" style="font-size:12px;">Reply Message *</label>
                        <textarea id="aReplyMsg" class="form-control rounded-3" rows="4"
                            placeholder="Type the auto-reply message here…&#10;&#10;Example: Hi! Thanks for reaching out. Our team will get back to you shortly 🙏"></textarea>
                        <div class="form-text">Tip: You can use emojis for a friendlier feel 😊</div>
                    </div>
                    <div class="col-12" id="aRespTplWrap" style="display:none;">
                        <label for="aTemplName" class="form-label fw-semibold" style="font-size:12px;">Template Name *</label>
                        <input type="text" id="aTemplName" class="form-control rounded-3" placeholder="e.g. welcome_message">
                        <div class="form-text">Enter the exact approved template name from Meta.</div>
                    </div>

                    <!-- Preview -->
                    <div class="col-12">
                        <div class="rule-preview" id="aRulePreview">
                            <div class="rule-preview-label">Rule Preview</div>
                            <div class="rule-flow" id="aRuleFlow">
                                <span class="rule-chip trigger">Keyword: —</span>
                                <span class="rule-arrow"><i class="bi bi-arrow-right"></i></span>
                                <span class="rule-chip action">Reply: —</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="aIsActive" checked>
                            <label class="form-check-label fw-semibold" for="aIsActive" style="font-size:13px;">Active immediately</label>
                        </div>
                    </div>
                </div>
                <div id="aModalErr" class="alert alert-danger d-none rounded-3 py-2 mt-3" style="font-size:13px;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-fu-primary rounded-3" id="aSaveBtn" onclick="aSave()">
                    <i class="bi bi-check-lg me-1"></i>Save Rule
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm -->
<div class="modal fade" id="aDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
                <h6 class="fw-bold mb-2">Delete Rule?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">This automation will stop working.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger rounded-3 px-4" id="aConfirmDelBtn" onclick="aConfirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let aAll=[], aFiltered=[], aDeleteId=null;
let aModal, aDeleteModal;

document.addEventListener('DOMContentLoaded',()=>{
    aModal       = new bootstrap.Modal(document.getElementById('aModal'));
    aDeleteModal = new bootstrap.Modal(document.getElementById('aDeleteModal'));
    aLoadStats(); aLoad();
    // Live preview
    ['aName','aKeywords','aReplyMsg','aTemplName'].forEach(id=>{
        document.getElementById(id)?.addEventListener('input', aUpdatePreview);
    });
    document.getElementById('aTriggerType')?.addEventListener('change', aUpdatePreview);
    document.querySelectorAll('input[name="aResponseType"]').forEach(r=>r.addEventListener('change', aUpdatePreview));
});

function aLoadStats(){
    fetch('/automations/stats').then(r=>r.json()).then(d=>{
        document.getElementById('aStatTotal').textContent     = d.total     ?? 0;
        document.getElementById('aStatActive').textContent    = d.active    ?? 0;
        document.getElementById('aStatToday').textContent     = d.today     ?? 0;
        document.getElementById('aStatTriggered').textContent = d.triggered ?? 0;
    }).catch(()=>{});
}

function aLoad(){
    fetch('/automations/list').then(r=>r.json()).then(data=>{
        aAll = data.automations||data||[];
        aFiltered=[...aAll]; aRender();
    }).catch(()=>{
        document.getElementById('aTableBody').innerHTML=`<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load automation rules.</p></div></td></tr>`;
    });
}

function aFilter(){
    const q=document.getElementById('aSearch').value.toLowerCase();
    const t=document.getElementById('aTriggerFilter').value;
    aFiltered=aAll.filter(r=>{
        const mQ=!q||(r.name||'').toLowerCase().includes(q)||(r.keywords||'').toLowerCase().includes(q);
        const mT=!t||r.trigger_type===t;return mQ&&mT;
    });
    aRender();
}

function aRender(){
    const tbody=document.getElementById('aTableBody');
    if(!aFiltered.length){
        tbody.innerHTML=`<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">🤖</div><p>No automation rules yet.</p><button class="btn btn-fu-primary btn-sm rounded-pill mt-2" onclick="aOpenCreate()"><i class="bi bi-plus-lg me-1"></i>Create First Rule</button></div></td></tr>`;
        return;
    }
    const tMap={keyword:'<i class="bi bi-keyboard me-1"></i>Keyword',welcome:'<i class="bi bi-door-open me-1"></i>Welcome',any:'<i class="bi bi-infinity me-1"></i>Any Message'};
    tbody.innerHTML=aFiltered.map(r=>`<tr>
        <td><div class="fw-semibold" style="font-size:14px;color:#1a1a2e;">${escHtml(r.name||'—')}</div><div class="text-muted" style="font-size:11px;">#${r.id}</div></td>
        <td><span class="trigger-badge ${r.trigger_type}">${tMap[r.trigger_type]||r.trigger_type}</span></td>
        <td><span class="text-muted" style="font-size:12px;max-width:150px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(r.keywords||'—')}</span></td>
        <td><span style="font-size:12px;max-width:200px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#555;">${escHtml(r.reply_message||r.template_name||'—')}</span></td>
        <td class="text-muted" style="font-size:12px;">${r.trigger_count||0}×</td>
        <td>
            <label class="fu-switch-sm">
                <input type="checkbox" ${r.is_active?'checked':''} onchange="aToggleActive(${r.id},this)">
                <span class="fu-switch-sm-sl"></span>
            </label>
        </td>
        <td class="text-end">
            <button class="action-btn edit" onclick="aOpenEdit(${r.id})"><i class="bi bi-pencil-fill"></i></button>
            <button class="action-btn delete ms-1" onclick="aAskDelete(${r.id})"><i class="bi bi-trash-fill"></i></button>
        </td>
    </tr>`).join('');
}

function aToggleActive(id,cb){
    fetch(`/automations/${id}/toggle`,{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
    .then(r=>r.json()).then(()=>{aLoad();aLoadStats();}).catch(()=>{cb.checked=!cb.checked;});
}

function aOpenCreate(){
    document.getElementById('aModalTitle').innerHTML='<i class="bi bi-robot me-2 text-success"></i>Create Automation Rule';
    document.getElementById('aEditId').value='';
    ['aName','aKeywords','aReplyMsg','aTemplName'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('aTriggerType').value='keyword';
    document.getElementById('aRespText').checked=true;
    document.getElementById('aIsActive').checked=true;
    document.getElementById('aModalErr').classList.add('d-none');
    document.getElementById('aSaveBtn').innerHTML='<i class="bi bi-check-lg me-1"></i>Save Rule';
    aToggleTrigger(); aToggleResponse(); aUpdatePreview();
    aModal.show();
}
function aOpenEdit(id){
    const r=aAll.find(x=>x.id==id);if(!r)return;
    document.getElementById('aModalTitle').innerHTML='<i class="bi bi-pencil me-2 text-success"></i>Edit Automation Rule';
    document.getElementById('aEditId').value=r.id;
    document.getElementById('aName').value=r.name||'';
    document.getElementById('aKeywords').value=r.keywords||'';
    document.getElementById('aReplyMsg').value=r.reply_message||'';
    document.getElementById('aTemplName').value=r.template_name||'';
    document.getElementById('aTriggerType').value=r.trigger_type||'keyword';
    document.getElementById('aIsActive').checked=!!r.is_active;
    if(r.template_name){document.getElementById('aRespTpl').checked=true;}
    else{document.getElementById('aRespText').checked=true;}
    document.getElementById('aModalErr').classList.add('d-none');
    document.getElementById('aSaveBtn').innerHTML='<i class="bi bi-check-lg me-1"></i>Update Rule';
    aToggleTrigger(); aToggleResponse(); aUpdatePreview();
    aModal.show();
}
function aToggleTrigger(){
    const v=document.getElementById('aTriggerType').value;
    document.getElementById('aKeywordsWrap').style.display=v==='keyword'?'block':'none';
}
function aToggleResponse(){
    const v=document.querySelector('input[name="aResponseType"]:checked').value;
    document.getElementById('aRespTextWrap').style.display=v==='text'?'block':'none';
    document.getElementById('aRespTplWrap').style.display=v==='template'?'block':'none';
}
function aUpdatePreview(){
    const trig=document.getElementById('aTriggerType').value;
    const kw=document.getElementById('aKeywords').value.trim()||'—';
    const resp=document.querySelector('input[name="aResponseType"]:checked').value;
    const msg=resp==='text'?document.getElementById('aReplyMsg').value.trim():document.getElementById('aTemplName').value.trim();
    const trigLabel=trig==='keyword'?'Keyword: '+kw:trig==='welcome'?'Welcome':'Any Message';
    document.getElementById('aRuleFlow').innerHTML=`
        <span class="rule-chip trigger">${escHtml(trigLabel)}</span>
        <span class="rule-arrow"><i class="bi bi-arrow-right"></i></span>
        <span class="rule-chip action">${resp==='template'?'Template: ':'Reply: '}${escHtml(msg||'—')}</span>`;
}

function aSave(){
    const id=document.getElementById('aEditId').value;
    const name=document.getElementById('aName').value.trim();
    const trigType=document.getElementById('aTriggerType').value;
    const keywords=document.getElementById('aKeywords').value.trim();
    const respType=document.querySelector('input[name="aResponseType"]:checked').value;
    const replyMsg=document.getElementById('aReplyMsg').value.trim();
    const templName=document.getElementById('aTemplName').value.trim();
    const isActive=document.getElementById('aIsActive').checked?1:0;
    const err=document.getElementById('aModalErr');
    if(!name){err.textContent='Rule name is required.';err.classList.remove('d-none');return;}
    if(trigType==='keyword'&&!keywords){err.textContent='Please enter at least one keyword.';err.classList.remove('d-none');return;}
    if(respType==='text'&&!replyMsg){err.textContent='Reply message is required.';err.classList.remove('d-none');return;}
    if(respType==='template'&&!templName){err.textContent='Template name is required.';err.classList.remove('d-none');return;}
    err.classList.add('d-none');
    const btn=document.getElementById('aSaveBtn');btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    const method=id?'PUT':'POST';const url=id?`/automations/${id}`:'/automations';
    fetch(url,{method,credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({name,trigger_type:trigType,keywords,reply_message:replyMsg,template_name:templName,is_active:isActive})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false;btn.innerHTML='<i class="bi bi-check-lg me-1"></i>'+(id?'Update':'Save')+' Rule';
        if(d.error){err.textContent=d.error;err.classList.remove('d-none');return;}
        aModal.hide();aLoad();aLoadStats();showToast(id?'✅ Rule updated':'✅ Rule created','success');
    }).catch(()=>{btn.disabled=false;btn.innerHTML='<i class="bi bi-check-lg me-1"></i>Save Rule';err.textContent='Something went wrong.';err.classList.remove('d-none');});
}

function aAskDelete(id){aDeleteId=id;aDeleteModal.show();}
function aConfirmDelete(){
    if(!aDeleteId)return;
    const btn=document.getElementById('aConfirmDelBtn');btn.disabled=true;btn.textContent='Deleting…';
    fetch(`/automations/${aDeleteId}`,{method:'DELETE',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
    .then(r=>r.json()).then(()=>{btn.disabled=false;btn.textContent='Delete';aDeleteModal.hide();aDeleteId=null;aLoad();aLoadStats();showToast('✅ Rule deleted','success');})
    .catch(()=>{btn.disabled=false;btn.textContent='Delete';showToast('❌ Failed','error');});
}
</script>
@endpush