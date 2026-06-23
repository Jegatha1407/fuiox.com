@extends('layouts.app')

@section('title', 'Flow Builder')
@section('page_title', 'Flow Builder')
@section('page_content_style', 'padding:0;overflow:hidden;height:calc(100vh - 60px);')

@section('page_styles')
/* ── FLOW LAYOUT ── */
.fb-wrap { display:flex; width:100%; height:calc(100vh - 60px); overflow:hidden; font-family:'Plus Jakarta Sans',sans-serif; }

/* ── LEFT SIDEBAR ── */
.fb-sidebar {
    width:240px; min-width:220px; background:#1a1d27;
    display:flex; flex-direction:column;
    border-right:1px solid rgba(255,255,255,0.08);
    height:100%; overflow:hidden; flex-shrink:0;
}
.fb-sidebar-top {
    padding:14px 16px 10px;
    border-bottom:1px solid rgba(255,255,255,0.08);
    flex-shrink:0;
}
.fb-sidebar-top h6 { font-size:13px; font-weight:700; color:#fff; margin:0 0 2px; }
.fb-sidebar-top small { font-size:11px; color:rgba(255,255,255,0.35); }
.fb-node-list { flex:1; overflow-y:auto; padding:10px 10px; }
.fb-node-list::-webkit-scrollbar { width:3px; }
.fb-node-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.15); border-radius:2px; }
.fb-section-label { font-size:10px; font-weight:700; color:rgba(255,255,255,0.25); text-transform:uppercase; letter-spacing:1px; padding:10px 6px 4px; }
.fb-node-card {
    display:flex; align-items:center; gap:10px;
    padding:9px 10px; border-radius:10px;
    border:1px solid rgba(255,255,255,0.08);
    background:rgba(255,255,255,0.04);
    cursor:grab; margin-bottom:6px;
    transition:0.15s; user-select:none;
}
.fb-node-card:hover { background:rgba(255,255,255,0.09); border-color:rgba(255,255,255,0.18); }
.fb-node-card:active { cursor:grabbing; }
.fb-node-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.fb-node-info .fb-node-title { font-size:12px; font-weight:600; color:#fff; }
.fb-node-info .fb-node-desc  { font-size:10px; color:rgba(255,255,255,0.4); margin-top:1px; }

/* ── MAIN AREA ── */
.fb-main { flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0; }

/* ── TOPBAR ── */
.fb-topbar {
    background:#1a1d27; border-bottom:1px solid rgba(255,255,255,0.08);
    padding:0 16px; height:52px;
    display:flex; align-items:center; gap:10px;
    flex-shrink:0;
}
.fb-flow-name {
    background:transparent; border:none; color:#fff;
    font-size:15px; font-weight:600; font-family:inherit;
    outline:none; min-width:160px;
}
.fb-flow-name::placeholder { color:rgba(255,255,255,0.3); }
.fb-status { font-size:11px; color:rgba(255,255,255,0.35); }
.fb-topbar select {
    background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12);
    color:#fff; padding:6px 10px; border-radius:8px;
    font-size:12px; font-family:inherit; outline:none;
}
.fb-topbar select option { background:#1a1d27; }
.fb-topbar input[type="text"] {
    background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12);
    color:#fff; padding:6px 10px; border-radius:8px;
    font-size:12px; font-family:inherit; outline:none; width:140px;
}
.fb-topbar input::placeholder { color:rgba(255,255,255,0.3); }
.fb-btn-test { background:rgba(255,255,255,0.08); color:#fff; border:1px solid rgba(255,255,255,0.15); padding:7px 14px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:600; font-family:inherit; }
.fb-btn-test:hover { background:rgba(255,255,255,0.15); }
.fb-btn-save { background:#25d366; color:#fff; border:none; padding:7px 16px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:700; font-family:inherit; display:flex; align-items:center; gap:6px; }
.fb-btn-save:hover { background:#1fba58; }
.fb-btn-flows { background:rgba(255,255,255,0.08); color:#aaa; border:1px solid rgba(255,255,255,0.1); padding:7px 12px; border-radius:8px; cursor:pointer; font-size:12px; font-family:inherit; }
.fb-btn-flows:hover { color:#fff; }

/* ── CANVAS ── */
.fb-canvas-wrap {
    flex:1; position:relative; overflow:hidden;
    background:#0f1117;
    background-image:radial-gradient(circle,rgba(255,255,255,0.05) 1px,transparent 1px);
    background-size:24px 24px;
}
.fb-canvas-wrap svg.fb-svg {
    position:absolute; top:0; left:0; width:100%; height:100%;
    pointer-events:none; z-index:1;
}
#fb-canvas { position:absolute; top:0; left:0; width:100%; height:100%; }

/* ── NODES ── */
.fb-flow-node { position:absolute; z-index:10; cursor:move; user-select:none; min-width:210px; }
.fb-flow-node .fb-node-inner {
    background:#1e2130; border:1.5px solid rgba(255,255,255,0.1);
    border-radius:12px; overflow:hidden;
    box-shadow:0 4px 20px rgba(0,0,0,0.4);
    transition:border-color 0.15s, box-shadow 0.15s;
}
.fb-flow-node.selected .fb-node-inner { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.15),0 4px 20px rgba(0,0,0,0.4); }
.fb-node-header { padding:10px 12px; display:flex; align-items:center; gap:8px; }
.fb-node-title  { font-size:12px; font-weight:700; color:#fff; flex:1; }
.fb-node-del    { background:none; border:none; color:#555; cursor:pointer; padding:2px; border-radius:4px; display:flex; align-items:center; justify-content:center; transition:0.15s; }
.fb-node-del:hover { color:#e53935; background:rgba(229,57,53,0.1); }
.fb-node-body   { padding:0 12px 12px; }
.fb-field       { margin-top:8px; }
.fb-field label { font-size:10px; font-weight:700; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:4px; }
.fb-field input, .fb-field select, .fb-field textarea {
    width:100%; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1);
    color:#fff; padding:7px 10px; border-radius:6px;
    font-size:12px; font-family:inherit; outline:none; transition:0.15s;
}
.fb-field input:focus, .fb-field select:focus, .fb-field textarea:focus { border-color:#25d366; background:rgba(37,211,102,0.05); }
.fb-field textarea { resize:vertical; min-height:60px; }
.fb-field select option { background:#1e2130; }

/* ── CONNECTORS ── */
.fb-dot {
    position:absolute; width:12px; height:12px; border-radius:50%;
    background:#25d366; border:2px solid #0f1117;
    cursor:crosshair; z-index:20; transition:transform 0.15s;
}
.fb-dot:hover { transform:scale(1.4); }
.fb-dot.top    { top:-6px;    left:50%; transform:translateX(-50%); }
.fb-dot.bottom { bottom:-6px; left:50%; transform:translateX(-50%); }
.fb-dot.top:hover    { transform:translateX(-50%) scale(1.4); }
.fb-dot.bottom:hover { transform:translateX(-50%) scale(1.4); }

/* ── EMPTY STATE ── */
.fb-empty {
    position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
    text-align:center; pointer-events:none;
}
.fb-empty-icon { font-size:52px; opacity:0.2; margin-bottom:14px; }
.fb-empty p    { font-size:14px; color:rgba(255,255,255,0.25); }

/* ── CONNECTIONS ── */
.fb-path { stroke:#25d366; stroke-width:2; fill:none; }
.fb-path.solid { stroke-dasharray:none; }

/* ── TOAST ── */
#fb-toast {
    position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
    background:#25d366; color:#fff; padding:10px 22px;
    border-radius:20px; font-size:13px; font-weight:600;
    z-index:9999; opacity:0; transition:opacity 0.3s; pointer-events:none;
}
#fb-toast.show { opacity:1; }

/* ── FLOWS LIST MODAL ── */
.fb-modal-ov { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9998; display:none; align-items:center; justify-content:center; }
.fb-modal-ov.open { display:flex; }
.fb-modal-box { background:#1a1d27; border-radius:16px; padding:26px; width:580px; max-height:80vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.5); }
.fb-modal-box h5 { font-size:17px; font-weight:700; color:#fff; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; }
.fb-flow-item { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:13px 15px; margin-bottom:8px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; transition:0.15s; }
.fb-flow-item:hover { background:rgba(255,255,255,0.08); }
.fb-flow-item-name { font-size:14px; font-weight:600; color:#fff; }
.fb-flow-item-meta { font-size:12px; color:rgba(255,255,255,0.4); margin-top:2px; }

/* ── SAVE MODAL ── */
.fb-save-modal { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; display:none; align-items:center; justify-content:center; }
.fb-save-modal.open { display:flex; }
.fb-save-box { background:#1a1d27; border-radius:16px; padding:26px; width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.5); }
.fb-save-box h5 { font-size:16px; font-weight:700; color:#fff; margin-bottom:6px; }
.fb-save-box p  { font-size:13px; color:rgba(255,255,255,0.4); margin-bottom:18px; }
.fb-save-box label { font-size:11px; font-weight:700; color:rgba(255,255,255,0.5); text-transform:uppercase; display:block; margin-bottom:6px; }
.fb-save-box input { width:100%; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; padding:10px 14px; border-radius:8px; font-size:14px; font-family:inherit; outline:none; }

/* ── MOBILE ── */
@media(max-width:768px){
    .fb-sidebar { display:none; }
    .fb-topbar input[type="text"] { width:100px; }
}

<div id="fbAiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#161d26;border-radius:18px;padding:28px;width:500px;max-width:92vw;border:1px solid rgba(255,255,255,0.1);">
        <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:6px;"><i class="bi bi-stars me-2" style="color:#667eea;"></i>AI Flow Generator</div>
        <div style="font-size:12.5px;color:rgba(255,255,255,0.4);margin-bottom:18px;">Describe your WhatsApp flow and AI will build it instantly</div>
        <textarea id="fbAiPrompt" rows="4" placeholder="e.g. Welcome new customers, ask if they want support or sales, then assign to the right agent. Add a 1 hour delay then send a follow up message."
            style="width:100%;padding:12px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#fff;font-size:13px;outline:none;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
        <div id="fbAiError" style="display:none;background:rgba(229,57,53,0.15);color:#ef5350;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-top:10px;"></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="document.getElementById('fbAiModal').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:rgba(255,255,255,0.07);color:#fff;cursor:pointer;font-family:inherit;">Cancel</button>
            <button id="fbAiGenerateBtn" onclick="fbGenerateFlow()" style="flex:1;padding:11px;border:none;border-radius:9px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:700;cursor:pointer;font-family:inherit;"><i class="bi bi-stars me-1"></i> Generate Flow</button>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="fb-wrap" id="fbWrap">

    <!-- ── LEFT SIDEBAR ── -->
    <div class="fb-sidebar">
        <div class="fb-sidebar-top">
            <h6>Node Library</h6>
            <small>Drag nodes to canvas</small>
        </div>
        <div class="fb-node-list">
            <div class="fb-section-label">Trigger</div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'trigger')">
                <div class="fb-node-icon" style="background:rgba(37,211,102,0.15);">⚡</div>
                <div class="fb-node-info"><div class="fb-node-title">Start Trigger</div><div class="fb-node-desc">Starts the flow</div></div>
            </div>

            <div class="fb-section-label">Messages</div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'message')">
                <div class="fb-node-icon" style="background:rgba(25,118,210,0.15);">💬</div>
                <div class="fb-node-info"><div class="fb-node-title">Send Message</div><div class="fb-node-desc">Text message</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'template')">
                <div class="fb-node-icon" style="background:rgba(123,31,162,0.15);">📋</div>
                <div class="fb-node-info"><div class="fb-node-title">Send Template</div><div class="fb-node-desc">WhatsApp template</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'buttons')">
                <div class="fb-node-icon" style="background:rgba(37,211,102,0.15);">🔘</div>
                <div class="fb-node-info"><div class="fb-node-title">Button Message</div><div class="fb-node-desc">Quick reply buttons</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'list')">
                <div class="fb-node-icon" style="background:rgba(25,118,210,0.15);">📋</div>
                <div class="fb-node-info"><div class="fb-node-title">List Message</div><div class="fb-node-desc">Options list</div></div>
            </div>

            <div class="fb-section-label">Logic</div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'delay')">
                <div class="fb-node-icon" style="background:rgba(245,124,0,0.15);">⏱️</div>
                <div class="fb-node-info"><div class="fb-node-title">Wait / Delay</div><div class="fb-node-desc">Wait before next step</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'condition')">
                <div class="fb-node-icon" style="background:rgba(229,57,53,0.15);">🔀</div>
                <div class="fb-node-info"><div class="fb-node-title">Condition</div><div class="fb-node-desc">Branch on reply</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'end')">
                <div class="fb-node-icon" style="background:rgba(255,255,255,0.06);">🏁</div>
                <div class="fb-node-info"><div class="fb-node-title">End Flow</div><div class="fb-node-desc">Stop execution</div></div>
            </div>

            <div class="fb-section-label">Actions</div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'tag')">
                <div class="fb-node-icon" style="background:rgba(37,211,102,0.1);">🏷️</div>
                <div class="fb-node-info"><div class="fb-node-title">Add Tag</div><div class="fb-node-desc">Tag the contact</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'assign')">
                <div class="fb-node-icon" style="background:rgba(25,118,210,0.1);">👤</div>
                <div class="fb-node-info"><div class="fb-node-title">Assign Agent</div><div class="fb-node-desc">Assign to team</div></div>
            </div>
            <div class="fb-node-card" draggable="true" ondragstart="fbDragStart(event,'remove_tag')">
                <div class="fb-node-icon" style="background:rgba(229,57,53,0.1);">🏷️</div>
                <div class="fb-node-info"><div class="fb-node-title">Remove Tag</div><div class="fb-node-desc">Remove tag</div></div>
            </div>
        </div>
    </div>

    <!-- ── MAIN ── -->
    <div class="fb-main">

        <!-- Topbar -->
        <div class="fb-topbar">
            <button class="fb-btn-flows" onclick="fbShowFlows()">📊 My Flows</button>
            <input type="text" class="fb-flow-name" id="fbFlowName" placeholder="Untitled Flow" value="My First Flow">
            <span class="fb-status" id="fbStatus"></span>
            <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                <select id="fbTriggerType" class="fb-topbar-select">
                    <option value="keyword">Keyword Trigger</option>
                    <option value="welcome">Welcome (First Message)</option>
                    <option value="any">Any Message</option>
                </select>
                <input type="text" id="fbTriggerValue" placeholder="e.g. hello, hi" class="fb-topbar-input">
                <button onclick="fbOpenAiModal()" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:12px;font-weight:700;font-family:inherit;display:flex;align-items:center;gap:6px;"><i class="bi bi-stars me-1"></i>AI Generate</button>
                <button class="fb-btn-test" onclick="fbTest()"><i class="bi bi-play-fill me-1"></i>Test</button>
                <button class="fb-btn-save" onclick="fbSave()"><i class="bi bi-floppy-fill me-1"></i>Save</button>
            </div>
        </div>

        <!-- Canvas -->
        <div class="fb-canvas-wrap" id="fbCanvasWrap"
            ondragover="event.preventDefault()"
            ondrop="fbDrop(event)">
            <svg class="fb-svg" id="fbSvg"></svg>
            <div id="fb-canvas">
                <div class="fb-empty" id="fbEmpty">
                    <div class="fb-empty-icon">⚡</div>
                    <p>Drag nodes from the left panel<br>to build your flow</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="fb-toast"></div>

<!-- Flows List Modal -->
<div class="fb-modal-ov" id="fbFlowsModal">
    <div class="fb-modal-box">
        <h5>
            📊 My Flows
            <button onclick="document.getElementById('fbFlowsModal').classList.remove('open')" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:20px;">×</button>
        </h5>
        <button onclick="fbNewFlow()" class="fb-btn-save w-100 mb-3 justify-content-center" style="border-radius:8px;">➕ New Flow</button>
        <div id="fbFlowsList"></div>
    </div>
</div>

<!-- Save Modal -->
<div class="fb-save-modal" id="fbSaveModal">
    <div class="fb-save-box">
        <h5><i class="bi bi-floppy-fill me-2 text-success"></i>Save Flow</h5>
        <p>Give your flow a name to identify it.</p>
        <label for="fbSaveName">Flow Name *</label>
        <input type="text" id="fbSaveName" placeholder="e.g. Welcome Flow, Lead Follow-up" class="mb-3">
        <div class="d-flex gap-2 justify-content-end mt-3">
            <button onclick="document.getElementById('fbSaveModal').classList.remove('open')" style="background:rgba(255,255,255,0.06);color:#aaa;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-family:inherit;">Cancel</button>
            <button onclick="fbConfirmSave()" class="fb-btn-save" style="border-radius:8px;padding:10px 20px;">Save Flow</button>
        </div>
    </div>
</div>


<div id="fbAiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#161d26;border-radius:18px;padding:28px;width:500px;max-width:92vw;border:1px solid rgba(255,255,255,0.1);">
        <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:6px;"><i class="bi bi-stars me-2" style="color:#667eea;"></i>AI Flow Generator</div>
        <div style="font-size:12.5px;color:rgba(255,255,255,0.4);margin-bottom:18px;">Describe your WhatsApp flow and AI will build it instantly</div>
        <textarea id="fbAiPrompt" rows="4" placeholder="e.g. Welcome new customers, ask if they want support or sales, then assign to the right agent. Add a 1 hour delay then send a follow up message."
            style="width:100%;padding:12px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#fff;font-size:13px;outline:none;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
        <div id="fbAiError" style="display:none;background:rgba(229,57,53,0.15);color:#ef5350;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-top:10px;"></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="document.getElementById('fbAiModal').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:rgba(255,255,255,0.07);color:#fff;cursor:pointer;font-family:inherit;">Cancel</button>
            <button id="fbAiGenerateBtn" onclick="fbGenerateFlow()" style="flex:1;padding:11px;border:none;border-radius:9px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:700;cursor:pointer;font-family:inherit;"><i class="bi bi-stars me-1"></i> Generate Flow</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── PRE-LOADED DATA ── */
var fbPreloadedTemplates = @json($templates ?? []);

/* ── STATE ── */
let fbNodes=[], fbConns=[], fbCurrentId=null, fbNodeCounter=0;
let fbDraggingType=null, fbConnFrom=null, fbSelNode=null;
let fbDragOffset={x:0,y:0}, fbIsDragging=false, fbTempLine=null;
let fbFlowTemplates=[];

/* ── NODE COLORS ── */
const FB_COLORS={
    trigger:   {bg:'rgba(37,211,102,0.15)',  border:'rgba(37,211,102,0.4)',  icon:'⚡', label:'Start Trigger'},
    message:   {bg:'rgba(25,118,210,0.15)',  border:'rgba(25,118,210,0.4)',  icon:'💬', label:'Send Message'},
    template:  {bg:'rgba(123,31,162,0.15)',  border:'rgba(123,31,162,0.4)', icon:'📋', label:'Send Template'},
    delay:     {bg:'rgba(245,124,0,0.15)',   border:'rgba(245,124,0,0.4)',  icon:'⏱️', label:'Wait / Delay'},
    condition: {bg:'rgba(229,57,53,0.15)',   border:'rgba(229,57,53,0.4)', icon:'🔀', label:'Condition'},
    end:       {bg:'rgba(255,255,255,0.06)', border:'rgba(255,255,255,0.15)',icon:'🏁', label:'End Flow'},
    tag:       {bg:'rgba(37,211,102,0.08)',  border:'rgba(37,211,102,0.2)', icon:'🏷️', label:'Add Tag'},
    assign:    {bg:'rgba(25,118,210,0.08)',  border:'rgba(25,118,210,0.2)', icon:'👤', label:'Assign Agent'},
    remove_tag:{bg:'rgba(229,57,53,0.08)',   border:'rgba(229,57,53,0.2)',  icon:'🏷️', label:'Remove Tag'},
    buttons:   {bg:'rgba(37,211,102,0.12)',  border:'rgba(37,211,102,0.3)', icon:'🔘', label:'Button Message'},
    list:      {bg:'rgba(25,118,210,0.12)',  border:'rgba(25,118,210,0.3)', icon:'📋', label:'List Message'},
};

/* ── HELPERS ── */
function fbEsc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fbToast(msg){const t=document.getElementById('fb-toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2500);}
function showToast(m){fbToast(m);}

/* ── DRAG FROM SIDEBAR ── */
function fbDragStart(e,type){fbDraggingType=type;e.dataTransfer.effectAllowed='copy';}
function fbDrop(e){
    e.preventDefault();if(!fbDraggingType)return;
    const wrap=document.getElementById('fbCanvasWrap');const rect=wrap.getBoundingClientRect();
    fbAddNode(fbDraggingType,e.clientX-rect.left-105,e.clientY-rect.top-40);fbDraggingType=null;
}

/* ── ADD NODE ── */
function fbAddNode(type,x,y,data={}){
    const id='node_'+(++fbNodeCounter);const node={id,type,x,y,data};fbNodes.push(node);fbRenderNode(node);
    document.getElementById('fbEmpty').style.display='none';return id;
}

function fbRenderNode(node){
    const c=FB_COLORS[node.type]||FB_COLORS.message;
    const el=document.createElement('div');el.className='fb-flow-node';el.id=node.id;
    el.style.left=node.x+'px';el.style.top=node.y+'px';
    el.innerHTML=`
        <div class="fb-dot top" data-node="${node.id}" data-port="in" onmousedown="fbStartConn(event,'${node.id}','in')"></div>
        <div class="fb-node-inner">
            <div class="fb-node-header" style="background:${c.bg};border-bottom:1px solid ${c.border};">
                <span style="font-size:16px;">${c.icon}</span>
                <span class="fb-node-title">${c.label}</span>
                <button class="fb-node-del" onclick="fbDeleteNode('${node.id}')"><i class="bi bi-x" style="font-size:14px;"></i></button>
            </div>
            <div class="fb-node-body" id="fbbody_${node.id}">${fbNodeBody(node)}</div>
        </div>
        ${node.type!=='condition'?`<div class="fb-dot bottom" data-node="${node.id}" data-port="out" onmousedown="fbStartConn(event,'${node.id}','out')"></div>`:'<div style="height:20px;"></div>'}`;
    el.addEventListener('mousedown',(e)=>fbStartDrag(e,node.id));
    document.getElementById('fb-canvas').appendChild(el);
    if(node.data){setTimeout(()=>{
        // Normalize AI field names to builder field names
        if(node.data.trigger_value && !node.data.keywords) node.data.keywords = node.data.trigger_value;
        if(node.data.text && !node.data.message) node.data.message = node.data.text;
        if(node.data.text && !node.data.body) node.data.body = node.data.text;
        if(node.data.delay_minutes && !node.data.duration) node.data.duration = node.data.delay_minutes;
        if(node.data.body && !node.data.message) node.data.message = node.data.body;
        if(Array.isArray(node.data.buttons)){
            node.data.buttons.forEach((b,i)=>{ node.data["btn"+(i+1)] = b; });
        }

        if(node.data.keywords){const e2=document.querySelector('#'+node.id+' input[onchange*="keywords"]');if(e2)e2.value=node.data.keywords;}
        if(node.data.trigger_type){const e2=document.querySelector('#'+node.id+' select[onchange*="trigger_type"]');if(e2){e2.value=node.data.trigger_type;}}
        if(node.data.message){const e2=document.querySelector('#'+node.id+' textarea');if(e2)e2.value=node.data.message;}
        if(node.data.body){const e2s=document.querySelectorAll('#'+node.id+' textarea');if(e2s[0])e2s[0].value=node.data.body;}
        if(node.data.condition){const e2=document.querySelector('#'+node.id+' input[onchange*="condition"]');if(e2)e2.value=node.data.condition;}
        if(node.data.duration){const e2=document.querySelector('#'+node.id+' input[type="number"]');if(e2)e2.value=node.data.duration;}
        if(node.data.tag){const e2=document.querySelector('#'+node.id+' input[onchange*="tag"]');if(e2)e2.value=node.data.tag;}
        if(node.data.agent){const e2=document.querySelector('#'+node.id+' input[onchange*="agent"]');if(e2)e2.value=node.data.agent;}
        if(node.data.btn1){const e2=document.querySelector('#'+node.id+' input[onchange*="btn1"]');if(e2)e2.value=node.data.btn1;}
        if(node.data.btn2){const e2=document.querySelector('#'+node.id+' input[onchange*="btn2"]');if(e2)e2.value=node.data.btn2;}
        if(node.data.btn3){const e2=document.querySelector('#'+node.id+' input[onchange*="btn3"]');if(e2)e2.value=node.data.btn3;}
        if(node.data.options){const e2s=document.querySelectorAll('#'+node.id+' textarea');const last=e2s[e2s.length-1];if(last)last.value=typeof node.data.options==='string'?node.data.options:node.data.options.join("\n");}
        if(node.data.template_name){const e2=document.querySelector('#'+node.id+' input[onchange*="template_name"]');if(e2)e2.value=node.data.template_name;}
    },200);}
}

function fbNodeBody(node){
    switch(node.type){
        case 'trigger':return`<div class="fb-field"><label>Trigger Type</label><select onchange="fbUpdateData('${node.id}','trigger_type',this.value)"><option value="keyword">Keyword</option><option value="welcome">Welcome</option><option value="any">Any Message</option></select></div><div class="fb-field"><label>Keywords</label><input type="text" placeholder="hello, hi, start" onchange="fbUpdateData('${node.id}','keywords',this.value)"></div>`;
        case 'message':return`<div class="fb-field"><label>Message</label><textarea placeholder="Type your message…" onchange="fbUpdateData('${node.id}','message',this.value)" rows="3"></textarea></div>`;
        case 'template':return`<div class="fb-field"><label>Template Name</label><input type="text" placeholder="template_name" onchange="fbUpdateData('${node.id}','template_name',this.value)"></div><div class="fb-field"><label>Language</label><input type="text" placeholder="en_US" onchange="fbUpdateData('${node.id}','language',this.value)"></div>`;
        case 'delay':return`<div class="fb-field"><label>Duration</label><div style="display:flex;gap:6px;"><input type="number" value="5" min="1" style="width:70px;" onchange="fbUpdateData('${node.id}','duration',this.value)"><select onchange="fbUpdateData('${node.id}','unit',this.value)" style="flex:1;"><option value="minutes">Minutes</option><option value="hours">Hours</option><option value="days">Days</option></select></div></div>`;
        case 'condition':return`<div class="fb-field"><label>If reply contains</label><input type="text" placeholder="yes, ok, sure" onchange="fbUpdateData('${node.id}','condition',this.value)"></div><div style="display:flex;gap:6px;margin-top:10px;margin-bottom:8px;"><div style="flex:1;text-align:center;font-size:10px;color:#25d366;font-weight:700;background:rgba(37,211,102,0.1);padding:6px;border-radius:6px;position:relative;">✓ YES<div class="fb-dot" data-node="${node.id}" data-port="yes" style="bottom:-16px;left:50%;transform:translateX(-50%);background:#25d366;position:absolute;" onmousedown="fbStartConn(event,'${node.id}','yes')"></div></div><div style="flex:1;text-align:center;font-size:10px;color:#e53935;font-weight:700;background:rgba(229,57,53,0.1);padding:6px;border-radius:6px;position:relative;">✗ NO<div class="fb-dot" data-node="${node.id}" data-port="no" style="bottom:-16px;left:50%;transform:translateX(-50%);background:#e53935;position:absolute;" onmousedown="fbStartConn(event,'${node.id}','no')"></div></div></div>`;
        case 'end':return`<div style="font-size:11px;color:rgba(255,255,255,0.3);text-align:center;padding:4px 0;">Flow ends here</div>`;
        case 'tag':return`<div class="fb-field"><label>Tag Name</label><input type="text" placeholder="e.g. interested" onchange="fbUpdateData('${node.id}','tag',this.value)"></div>`;
        case 'assign':return`<div class="fb-field"><label>Assign To</label><input type="text" placeholder="Agent name or ID" onchange="fbUpdateData('${node.id}','agent',this.value)"></div>`;
        case 'remove_tag':return`<div class="fb-field"><label>Tag Name</label><input type="text" placeholder="e.g. interested" onchange="fbUpdateData('${node.id}','tag',this.value)"></div>`;
        case 'buttons':return`<div class="fb-field"><label>Message Body</label><textarea placeholder="What would you like to do?" onchange="fbUpdateData('${node.id}','body',this.value)" rows="2"></textarea></div><div class="fb-field"><label>Button 1</label><input type="text" placeholder="Option 1" onchange="fbUpdateData('${node.id}','btn1',this.value)"></div><div class="fb-field"><label>Button 2</label><input type="text" placeholder="Option 2" onchange="fbUpdateData('${node.id}','btn2',this.value)"></div><div class="fb-field"><label>Button 3 (opt)</label><input type="text" placeholder="Option 3" onchange="fbUpdateData('${node.id}','btn3',this.value)"></div>`;
        case 'list':return`<div class="fb-field"><label>Message Body</label><textarea placeholder="Please select an option:" onchange="fbUpdateData('${node.id}','body',this.value)" rows="2"></textarea></div><div class="fb-field"><label>Button Label</label><input type="text" placeholder="View Options" onchange="fbUpdateData('${node.id}','button_label',this.value)"></div><div class="fb-field"><label>Options (one per line)</label><textarea placeholder="Option 1&#10;Option 2&#10;Option 3" onchange="fbUpdateData('${node.id}','options',this.value)" rows="4"></textarea></div>`;
        default:return'';
    }
}

function fbUpdateData(id,key,value){const node=fbNodes.find(n=>n.id===id);if(node)node.data[key]=value;}

/* ── DRAG NODES ── */
function fbStartDrag(e,nodeId){
    if(e.target.closest('.fb-dot')||e.target.closest('.fb-node-del')||['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName))return;
    e.preventDefault();fbSelNode=nodeId;fbIsDragging=true;
    const node=fbNodes.find(n=>n.id===nodeId);
    fbDragOffset.x=e.clientX-node.x;fbDragOffset.y=e.clientY-node.y-60;
    document.querySelectorAll('.fb-flow-node').forEach(n=>n.classList.remove('selected'));
    document.getElementById(nodeId).classList.add('selected');
    document.addEventListener('mousemove',fbOnDrag);document.addEventListener('mouseup',fbStopDrag);
}
function fbOnDrag(e){
    if(!fbIsDragging||!fbSelNode)return;
    const node=fbNodes.find(n=>n.id===fbSelNode);if(!node)return;
    node.x=e.clientX-fbDragOffset.x;node.y=e.clientY-fbDragOffset.y-60;
    const el=document.getElementById(fbSelNode);el.style.left=node.x+'px';el.style.top=node.y+'px';
    fbDrawConns();
}
function fbStopDrag(){fbIsDragging=false;document.removeEventListener('mousemove',fbOnDrag);document.removeEventListener('mouseup',fbStopDrag);}

/* ── CONNECTIONS ── */
function fbStartConn(e,nodeId,port){
    e.preventDefault();e.stopPropagation();
    if(port==='out'||port==='yes'||port==='no'){
        fbConnFrom={nodeId,port};
        const wrap=document.getElementById('fbCanvasWrap');
        wrap.addEventListener('mousemove',fbDrawTempLine);wrap.addEventListener('mouseup',fbEndConn);
    }
}
function fbDrawTempLine(e){
    const wrap=document.getElementById('fbCanvasWrap');const rect=wrap.getBoundingClientRect();
    const fromEl=document.getElementById(fbConnFrom.nodeId);const fromRect=fromEl.getBoundingClientRect();
    const x1=fromRect.left+fromRect.width/2-rect.left;const y1=fromRect.bottom-rect.top;
    const x2=e.clientX-rect.left;const y2=e.clientY-rect.top;
    if(!fbTempLine){fbTempLine=document.createElementNS('http://www.w3.org/2000/svg','path');fbTempLine.setAttribute('stroke','#25d366');fbTempLine.setAttribute('stroke-width','2');fbTempLine.setAttribute('fill','none');fbTempLine.setAttribute('stroke-dasharray','6,3');fbTempLine.setAttribute('opacity','0.6');document.getElementById('fbSvg').appendChild(fbTempLine);}
    fbTempLine.setAttribute('d',fbBezier(x1,y1,x2,y2));
}
function fbEndConn(e){
    const wrap=document.getElementById('fbCanvasWrap');wrap.removeEventListener('mousemove',fbDrawTempLine);wrap.removeEventListener('mouseup',fbEndConn);
    if(fbTempLine){fbTempLine.remove();fbTempLine=null;}
    const target=e.target.closest('.fb-dot');
    if(target&&target.dataset.port==='in'){const toId=target.dataset.node;if(toId!==fbConnFrom.nodeId){fbConns=fbConns.filter(c=>!(c.from===fbConnFrom.nodeId&&c.port===fbConnFrom.port));fbConns.push({from:fbConnFrom.nodeId,to:toId,port:fbConnFrom.port||'out'});fbDrawConns();}}
    fbConnFrom=null;
}
function fbBezier(x1,y1,x2,y2){const cp=Math.abs(y2-y1)*0.5+40;return`M${x1},${y1} C${x1},${y1+cp} ${x2},${y2-cp} ${x2},${y2}`;}
function fbDrawConns(){
    const svg=document.getElementById('fbSvg');svg.innerHTML='';
    const wrap=document.getElementById('fbCanvasWrap');const rect=wrap.getBoundingClientRect();
    fbConns.forEach(conn=>{
        const fromEl=document.getElementById(conn.from);const toEl=document.getElementById(conn.to);if(!fromEl||!toEl)return;
        const fr=fromEl.getBoundingClientRect();const tr=toEl.getBoundingClientRect();
        const x1=fr.left+fr.width/2-rect.left;const y1=fr.bottom-rect.top;const x2=tr.left+tr.width/2-rect.left;const y2=tr.top-rect.top;
        const path=document.createElementNS('http://www.w3.org/2000/svg','path');path.setAttribute('class','fb-path solid');path.setAttribute('d',fbBezier(x1,y1,x2,y2));
        const arrow=document.createElementNS('http://www.w3.org/2000/svg','circle');arrow.setAttribute('cx',x2);arrow.setAttribute('cy',y2);arrow.setAttribute('r','4');arrow.setAttribute('fill','#25d366');
        svg.appendChild(path);svg.appendChild(arrow);
    });
}

/* ── DELETE NODE ── */
function fbDeleteNode(id){
    fbNodes=fbNodes.filter(n=>n.id!==id);fbConns=fbConns.filter(c=>c.from!==id&&c.to!==id);
    document.getElementById(id)?.remove();fbDrawConns();
    if(fbNodes.length===0)document.getElementById('fbEmpty').style.display='block';
}

/* ── SAVE ── */
function fbSave(){
    const name=document.getElementById('fbFlowName').value.trim();
    document.getElementById('fbSaveName').value=name||'';
    document.getElementById('fbSaveModal').classList.add('open');
    setTimeout(()=>document.getElementById('fbSaveName').focus(),100);
}
function fbConfirmSave(){
    const name=document.getElementById('fbSaveName').value.trim();
    if(!name){document.getElementById('fbSaveName').style.borderColor='#e53935';return;}
    document.getElementById('fbSaveModal').classList.remove('open');
    document.getElementById('fbFlowName').value=name;
    const payload={
        id:fbCurrentId,name,
        trigger_type:document.getElementById('fbTriggerType').value,
        trigger_value:document.getElementById('fbTriggerValue').value.trim(),
        nodes:fbNodes.map(n=>({node_id:n.id,type:n.type,data:n.data,position_x:n.x,position_y:n.y})),
        connections:fbConns
    };
    fetch('/flows',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(payload)})
    .then(r=>r.json()).then(data=>{
        if(data.error){fbToast('❌ '+data.error);return;}
        fbCurrentId=data.id;document.getElementById('fbStatus').textContent='Saved';fbToast('✅ Flow saved: '+name);
    }).catch(()=>fbToast('❌ Failed to save'));
}

/* ── FLOWS LIST ── */
function fbShowFlows(){
    document.getElementById('fbFlowsModal').classList.add('open');
    fetch('/flows',{credentials:'same-origin'}).then(r=>r.json()).then(data=>{
        const list=document.getElementById('fbFlowsList');const flows=data.flows||[];
        if(!flows.length){list.innerHTML='<div style="text-align:center;color:rgba(255,255,255,0.3);padding:20px;font-size:13px;">No flows yet. Create your first one!</div>';return;}
        list.innerHTML=flows.map(f=>`
            <div class="fb-flow-item" onclick="fbLoadFlow(${f.id})">
                <div>
                    <div class="fb-flow-item-name">${fbEsc(f.name)}</div>
                    <div class="fb-flow-item-meta">${f.trigger_type} trigger · ${f.is_active?'🟢 Active':'⭕ Inactive'}</div>
                </div>
                <div style="display:flex;gap:6px;">
                    <button onclick="event.stopPropagation();fbToggleFlow(${f.id})" style="background:${f.is_active?'rgba(229,57,53,0.15)':'rgba(37,211,102,0.15)'};color:${f.is_active?'#e53935':'#25d366'};border:none;padding:5px 10px;border-radius:6px;cursor:pointer;font-size:11px;font-weight:700;">${f.is_active?'Pause':'Activate'}</button>
                    <button onclick="event.stopPropagation();fbDeleteFlow(${f.id})" style="background:rgba(229,57,53,0.15);color:#e53935;border:none;padding:5px 10px;border-radius:6px;cursor:pointer;font-size:11px;">Delete</button>
                </div>
            </div>`).join('');
    });
}
function fbNewFlow(){
    document.getElementById('fbFlowsModal').classList.remove('open');
    fbCurrentId=null;fbNodes=[];fbConns=[];fbNodeCounter=0;
    document.getElementById('fb-canvas').innerHTML='<div class="fb-empty" id="fbEmpty"><div class="fb-empty-icon">⚡</div><p>Drag nodes from the left panel<br>to build your flow</p></div>';
    document.getElementById('fbSvg').innerHTML='';
    document.getElementById('fbFlowName').value='Untitled Flow';
    document.getElementById('fbStatus').textContent='';
}
function fbLoadFlow(id){
    document.getElementById('fbFlowsModal').classList.remove('open');
    fetch('/flows/'+id,{credentials:'same-origin'}).then(r=>r.json()).then(data=>{
        if(!data.flow)return;const f=data.flow;fbCurrentId=f.id;
        document.getElementById('fbFlowName').value=f.name;
        document.getElementById('fbTriggerType').value=f.trigger_type||'keyword';
        document.getElementById('fbTriggerValue').value=f.trigger_value||'';
        fbNodes=[];fbConns=data.connections||[];fbNodeCounter=0;
        document.getElementById('fb-canvas').innerHTML='<div class="fb-empty" id="fbEmpty" style="display:none;"></div>';
        document.getElementById('fbSvg').innerHTML='';
        (data.nodes||[]).forEach(n=>{
            fbNodeCounter++;
            const nd=typeof n.data==='string'?(JSON.parse(n.data||'{}')||{}):(n.data||{});
            const node={id:n.node_id,type:n.type,x:n.position_x,y:n.position_y,data:nd};
            fbNodes.push(node);fbRenderNode(node);
        });
        setTimeout(fbDrawConns,100);fbToast('✅ Flow loaded');
    });
}
function fbToggleFlow(id){fetch('/flows/'+id+'/toggle',{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>fbShowFlows());}
function fbDeleteFlow(id){if(!confirm('Delete this flow?'))return;fetch('/flows/'+id,{method:'DELETE',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>fbShowFlows());}
function fbTest(){fbToast('⚡ Flow test started! Check chat.');}

/* ── INIT ── */
document.addEventListener('DOMContentLoaded',()=>{
    const pc=document.getElementById('fuContent');if(pc){pc.style.padding='0';pc.style.overflow='hidden';}
    fbFlowTemplates=fbPreloadedTemplates.filter(t=>t.status==='APPROVED');
});
document.addEventListener('click',e=>{
    if(!e.target.closest('#fbFlowsModal')&&!e.target.closest('[onclick="fbShowFlows()"]'))document.getElementById('fbFlowsModal').classList.remove('open');
    if(!e.target.closest('#fbSaveModal')&&!e.target.closest('[onclick="fbSave()"]'))document.getElementById('fbSaveModal').classList.remove('open');
});

function fbOpenAiModal(){
    document.getElementById("fbAiPrompt").value="";
    document.getElementById("fbAiError").style.display="none";
    document.getElementById("fbAiModal").style.display="flex";
}
async function fbGenerateFlow(){
    const prompt = document.getElementById("fbAiPrompt").value.trim();
    if(!prompt) return;
    const btn = document.getElementById("fbAiGenerateBtn");
    const errDiv = document.getElementById("fbAiError");
    btn.innerHTML="Generating..."; btn.disabled=true; errDiv.style.display="none";
    try{
        const res = await fetch("/flows/ai-generate",{
            method:"POST", credentials:"same-origin",
            headers:{"Content-Type":"application/json","X-CSRF-TOKEN":document.querySelector("meta[name=csrf-token]").content,"Accept":"application/json"},
            body:JSON.stringify({prompt})
        });
        const d = await res.json();
        if(!d.success){errDiv.textContent=d.error||"Failed to generate";errDiv.style.display="block";return;}
        // Clear canvas
        fbNodes=[]; fbConns=[];
        document.querySelectorAll(".fb-node").forEach(el=>el.remove());
        if(typeof fbDrawConns==="function") fbDrawConns();
        // Load generated nodes - normalize field names
        (d.flow.nodes||[]).forEach((n,i)=>{
            const data=typeof n.data==="string"?JSON.parse(n.data||"{}"):(n.data||{});
            const nodeId = n.node_id || n.id || ("n"+(i+1));
            const x = Number(n.position_x||n.x) || (100+(i%4)*300);
            const y = Number(n.position_y||n.y) || (80+Math.floor(i/4)*180);
            const node={id:nodeId, type:n.type||"message", x, y, data};
            fbNodes.push(node);
            if(typeof fbRenderNode==="function") fbRenderNode(node);
        });
        fbConns=(d.flow.connections||[]).map(c=>({
            from: c.from||c.source,
            to: c.to||c.target,
            port: c.fromPort||c.port||"out"
        })).filter(c=>c.from&&c.to);
        if(typeof fbDrawConns==="function") setTimeout(fbDrawConns,150);
        document.getElementById("fbAiModal").style.display="none";
        fbToast("Flow generated! Review and save it.");
    }catch(err){
        errDiv.textContent="Network error. Please try again.";errDiv.style.display="block";
    }finally{
        btn.innerHTML="<i class=\"bi bi-stars me-1\"></i> Generate Flow";btn.disabled=false;
    }
}
</script>
@endpush