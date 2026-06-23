@extends('layouts.app')
@section('title', $appInfo['name'] . ' Flow Builder')
@section('page_title', $appInfo['icon'] . ' ' . $appInfo['name'] . ' Flow')

@push('styles')
<style>
.ab-wrap { display:flex; height:calc(100vh - 90px); background:#0a0f14; border-radius:14px; overflow:hidden; }
.ab-sidebar { width:260px; background:#11161c; border-right:1px solid rgba(255,255,255,0.06); padding:18px; overflow-y:auto; flex-shrink:0; }
.ab-section-label { font-size:11px; font-weight:700; color:rgba(255,255,255,0.35); text-transform:uppercase; letter-spacing:.5px; margin:16px 0 8px; }
.ab-node-card { display:flex; gap:10px; align-items:center; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:10px; padding:10px 12px; margin-bottom:8px; cursor:grab; transition:.2s; }
.ab-node-card:hover { background:rgba(255,255,255,0.08); border-color:rgba(37,211,102,0.3); }
.ab-node-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.ab-node-title { font-size:12.5px; font-weight:700; color:#fff; }
.ab-node-desc { font-size:10px; color:rgba(255,255,255,0.4); }
.ab-canvas-wrap { flex:1; position:relative; overflow:auto; background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px); background-size:22px 22px; }
#ab-canvas { position:relative; width:2800px; height:1800px; }
.ab-node { position:absolute; width:230px; background:#161d26; border:1.5px solid rgba(255,255,255,0.1); border-radius:12px; padding:12px; cursor:move; box-shadow:0 4px 16px rgba(0,0,0,0.3); z-index:2; }
.ab-node.selected { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.2); }
.ab-node-head { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.ab-node-head .ab-node-icon { width:26px; height:26px; font-size:13px; }
.ab-node-head-title { font-size:12.5px; font-weight:700; color:#fff; flex:1; }
.ab-node-del { background:none; border:none; color:rgba(255,255,255,0.3); cursor:pointer; font-size:14px; }
.ab-node-del:hover { color:#e53935; }
.ab-node-body { font-size:11.5px; color:rgba(255,255,255,0.55); line-height:1.5; }
.ab-port { position:absolute; width:14px; height:14px; background:#25d366; border-radius:50%; border:2px solid #11161c; cursor:crosshair; z-index:5; }
.ab-port:hover { transform:scale(1.3); }
.ab-port.in { left:-7px; top:50%; margin-top:-7px; background:#1976d2; }
.ab-port.out { right:-7px; }
.ab-option-row { display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.04); border-radius:7px; padding:6px 8px; margin-top:6px; position:relative; border:1px solid transparent; }
.ab-option-row:hover { border-color:rgba(37,211,102,0.3); }
.ab-inline-input { width:100%; padding:6px 8px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:6px; color:#fff; font-size:11.5px; outline:none; font-family:inherit; box-sizing:border-box; }
.ab-toolbar { display:flex; align-items:center; gap:8px; padding:12px 18px; background:#11161c; border-bottom:1px solid rgba(255,255,255,0.06); flex-wrap:wrap; }
.ab-btn { padding:9px 14px; border-radius:9px; border:none; font-size:12.5px; font-weight:700; cursor:pointer; font-family:inherit; white-space:nowrap; }
.ab-btn-save { background:#25d366; color:#fff; }
.ab-btn-back { background:rgba(255,255,255,0.07); color:#fff; text-decoration:none; display:inline-flex; align-items:center; }
.ab-empty { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; color:rgba(255,255,255,0.25); }
.ab-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
.ab-modal-overlay.open { display:flex; }
.ab-modal { background:#161d26; border-radius:16px; padding:24px; width:440px; max-width:92vw; border:1px solid rgba(255,255,255,0.1); max-height:85vh; overflow-y:auto; }
.ab-modal label { font-size:11px; font-weight:700; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:.4px; display:block; margin-bottom:5px; margin-top:12px; }
.ab-modal label:first-child { margin-top:0; }
.ab-modal input, .ab-modal textarea, .ab-modal select { width:100%; padding:10px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:8px; color:#fff; font-size:13px; outline:none; font-family:inherit; }
.ab-toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(100px); background:#1a1a1a; color:#fff; padding:11px 22px; border-radius:10px; font-size:13px; z-index:99999; opacity:0; transition:.3s; }
.ab-toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
svg.ab-svg { position:absolute; top:0; left:0; width:2800px; height:1800px; pointer-events:none; z-index:1; }
svg.ab-svg path.ab-conn { stroke:#25d366; stroke-width:2.5; fill:none; }
svg.ab-svg path.ab-preview { stroke:#1976d2; stroke-width:2.5; fill:none; stroke-dasharray:5,4; }
</style>
@endpush

@section('content')
<div class="ab-toolbar">
    <a href="{{ route('apps') }}" class="ab-btn ab-btn-back"><i class="bi bi-arrow-left me-1"></i> Apps</a>
    <a href="{{ route('apps.resources', $appType) }}" class="ab-btn ab-btn-back" style="text-decoration:none;"><i class="bi bi-list-ul me-1"></i> {{ $resourceLabel }}s</a>
    @if($config['is_time_based'])
    <a href="{{ route('apps.appointments', $appType) }}" class="ab-btn ab-btn-back" style="text-decoration:none;"><i class="bi bi-calendar-check me-1"></i> {{ $config['record_label'] }}s</a>
    @else
    <a href="{{ route('apps.records', $appType) }}" class="ab-btn ab-btn-back" style="text-decoration:none;"><i class="bi bi-receipt me-1"></i> {{ $config['record_label'] }}s</a>
    @endif
    <a href="{{ route('apps.access', $appType) }}" class="ab-btn ab-btn-back" style="text-decoration:none;"><i class="bi bi-shield-lock me-1"></i> Access</a>
    <button class="ab-btn" onclick="abOpenAiModal()" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;"><i class="bi bi-stars me-1"></i> AI Generate</button>
    <div style="flex:1;"></div>
    <span id="abStatus" style="font-size:12px;color:rgba(255,255,255,0.4);"></span>
    <button class="ab-btn ab-btn-save" onclick="abSave()"><i class="bi bi-floppy-fill me-1"></i> Save Flow</button>
</div>

<div class="ab-wrap">
    <div class="ab-sidebar">
        <div class="ab-section-label">Trigger</div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'trigger')">
            <div class="ab-node-icon" style="background:rgba(37,211,102,0.15);">⚡</div>
            <div><div class="ab-node-title">Start</div><div class="ab-node-desc">Entry point</div></div>
        </div>

        <div class="ab-section-label">Messages</div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'message')">
            <div class="ab-node-icon" style="background:rgba(25,118,210,0.15);">💬</div>
            <div><div class="ab-node-title">Send Message</div><div class="ab-node-desc">Plain text</div></div>
        </div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'template')">
            <div class="ab-node-icon" style="background:rgba(123,31,162,0.15);">📋</div>
            <div><div class="ab-node-title">Send Template</div><div class="ab-node-desc">WhatsApp template</div></div>
        </div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'list')">
            <div class="ab-node-icon" style="background:rgba(245,124,0,0.15);">📑</div>
            <div><div class="ab-node-title">List Message</div><div class="ab-node-desc">Each option branches</div></div>
        </div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'form')">
            <div class="ab-node-icon" style="background:rgba(0,150,136,0.15);">📝</div>
            <div><div class="ab-node-title">Collect Details</div><div class="ab-node-desc">Name, phone, address</div></div>
        </div>

        <div class="ab-section-label">{{ $resourceLabel }}s</div>
        @forelse($resourcesList as $res)
        <div class="ab-node-card" draggable="true"
            ondragstart="abDragStart(event,'resource','{{ $res->id }}','{{ addslashes($res->name) }}','{{ addslashes($res->category ?? '') }}','{{ addslashes($res->price ?? '') }}')">
            <div class="ab-node-icon" style="background:rgba(229,57,53,0.15);">{{ $config['resource_icon'] }}</div>
            <div><div class="ab-node-title">{{ $res->name }}</div><div class="ab-node-desc">{{ $res->category ?? ('Book this ' . strtolower($resourceLabel)) }}</div></div>
        </div>
        @empty
        <div style="font-size:11.5px;color:rgba(255,255,255,0.35);padding:8px 4px;">No {{ strtolower($resourceLabel) }}s added yet. <a href="{{ route('apps.resources', $appType) }}" style="color:#25d366;">Add one</a></div>
        @endforelse

        <div class="ab-section-label">Flow Control</div>
        <div class="ab-node-card" draggable="true" ondragstart="abDragStart(event,'end')">
            <div class="ab-node-icon" style="background:rgba(120,120,120,0.2);">🏁</div>
            <div><div class="ab-node-title">End Flow</div><div class="ab-node-desc">Finish & hand back</div></div>
        </div>
    </div>

    <div class="ab-canvas-wrap" id="abCanvasWrap">
        <svg class="ab-svg" id="abSvg"></svg>
        <div id="ab-canvas">
            <div class="ab-empty" id="abEmpty">
                <div style="font-size:38px;margin-bottom:8px;">{{ $appInfo['icon'] }}</div>
                <p>Drag nodes from the left to build your<br>{{ $appInfo['name'] }} conversation flow</p>
            </div>
        </div>
    </div>
</div>

<div class="ab-modal-overlay" id="abEditModal">
    <div class="ab-modal">
        <div id="abEditFields"></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="document.getElementById('abEditModal').classList.remove('open')" style="flex:1;padding:10px;border:none;border-radius:8px;background:rgba(255,255,255,0.07);color:#fff;cursor:pointer;font-family:inherit;">Cancel</button>
            <button onclick="abSaveNodeEdit()" style="flex:1;padding:10px;border:none;border-radius:8px;background:#25d366;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;">Save</button>
        </div>
    </div>
</div>


<div id="abAiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#161d26;border-radius:18px;padding:28px;width:500px;max-width:92vw;border:1px solid rgba(255,255,255,0.1);">
        <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:6px;"><i class="bi bi-stars me-2" style="color:#667eea;"></i>AI Flow Generator</div>
        <div style="font-size:12.5px;color:rgba(255,255,255,0.4);margin-bottom:18px;">Describe your flow and AI will build it instantly</div>
        <textarea id="abAiPrompt" rows="4" placeholder="e.g. Build a hospital appointment flow with Cardiology, Neurology and General departments. Each department shows a doctor, then collects patient name and phone number."
            style="width:100%;padding:12px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#fff;font-size:13px;outline:none;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
        <div id="abAiError" style="display:none;background:rgba(229,57,53,0.15);color:#ef5350;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-top:10px;"></div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="document.getElementById('abAiModal').style.display='none';" style="flex:1;padding:11px;border:none;border-radius:9px;background:rgba(255,255,255,0.07);color:#fff;cursor:pointer;font-family:inherit;">Cancel</button>
            <button id="abAiGenerateBtn" onclick="abGenerateFlow()" style="flex:1;padding:11px;border:none;border-radius:9px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-weight:700;cursor:pointer;font-family:inherit;"><i class="bi bi-stars me-1"></i> Generate Flow</button>
        </div>
    </div>
</div>\n<div class="ab-toast" id="abToast"></div>
@endsection

@push('scripts')
<script>
const ABT = document.querySelector('meta[name=csrf-token]').content;
const ABT_APP = '{{ $appType }}';
const ABT_DEFAULT_FORM_FIELDS = @json($config['form_fields']);
let abNodes = [], abConns = [], abCounter = 0, abEditingId = null;
let abDragType = null, abDragNodeId = null, abDragOffX = 0, abDragOffY = 0;
let abDragResource = null;
let abWiring = null;

const ABT_LABELS = {
    trigger: {icon:'⚡', color:'rgba(37,211,102,0.15)', title:'Start'},
    message: {icon:'💬', color:'rgba(25,118,210,0.15)', title:'Send Message'},
    template: {icon:'📋', color:'rgba(123,31,162,0.15)', title:'Send Template'},
    list: {icon:'📑', color:'rgba(245,124,0,0.15)', title:'List Message'},
    form: {icon:'📝', color:'rgba(0,150,136,0.15)', title:'Collect Details'},
    resource: {icon:'{{ $config["resource_icon"] }}', color:'rgba(229,57,53,0.15)', title:'{{ $resourceLabel }}'},
    end: {icon:'🏁', color:'rgba(120,120,120,0.2)', title:'End Flow'},
};

function abToast(msg){ const t=document.getElementById('abToast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2000); }
function abEsc(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function abGetOptions(node){
    if(node.type !== 'list') return [];
    return (node.data.options || '').split('\n').map(s=>s.trim());
}

/* ── DRAG NEW NODE FROM SIDEBAR ── */
function abDragStart(e,type,resId,resName,resCategory,resPrice){
    abDragType=type;
    abDragResource = (type==='resource') ? {id:resId, name:resName, category:resCategory, price:resPrice} : null;
    e.dataTransfer.effectAllowed='copy';
}
document.getElementById('abCanvasWrap').addEventListener('dragover', e=>e.preventDefault());
document.getElementById('abCanvasWrap').addEventListener('drop', e=>{
    e.preventDefault();
    if(!abDragType) return;
    const rect = document.getElementById('ab-canvas').getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    let preset = null;
    if(abDragResource) preset = {resource_id: abDragResource.id, resource_name: abDragResource.name, category: abDragResource.category, price: abDragResource.price};
    if(abDragType === 'form') preset = {fields: ABT_DEFAULT_FORM_FIELDS.map(f => ({...f}))};
    abAddNode(abDragType, x, y, preset);
    abDragType = null;
    abDragResource = null;
});

function abAddNode(type, x, y, presetData){
    abCounter++;
    const id = 'n'+Date.now()+abCounter;
    const node = { id, type, x: Math.max(0,x-115), y: Math.max(0,y-30), data: presetData || {} };
    abNodes.push(node);
    abRenderNode(node);
    document.getElementById('abEmpty').style.display = 'none';
    return node;
}

/* ── RENDER ── */
function abRenderNode(node){
    const meta = ABT_LABELS[node.type];
    const el = document.createElement('div');
    el.className = 'ab-node';
    el.id = 'node-'+node.id;
    el.style.left = node.x+'px';
    el.style.top = node.y+'px';

    // Header
    const head = document.createElement('div');
    head.className = 'ab-node-head';
    head.innerHTML = `<div class="ab-node-icon" style="background:${meta.color};">${meta.icon}</div><div class="ab-node-head-title">${meta.title}</div>`;
    const delBtn = document.createElement('button');
    delBtn.className = 'ab-node-del';
    delBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
    delBtn.onclick = () => abDeleteNode(node.id);
    head.appendChild(delBtn);
    el.appendChild(head);

    // Body container
    const body = document.createElement('div');
    body.id = 'body-'+node.id;
    el.appendChild(body);

    let dblclickable = true;

    if(node.type === 'list'){
        dblclickable = false;
        abBuildListBody(body, node);

    } else if(node.type === 'form'){
        dblclickable = false;
        if(!node.data.fields || !node.data.fields.length){
            node.data.fields = ABT_DEFAULT_FORM_FIELDS.map(f => Object.assign({}, f));
        }
        abBuildFormBody(body, node);

    } else {
        body.innerHTML = `<div class="ab-node-body">${abEsc(abNodeSummary(node))}</div>`;
        if(node.type !== 'end'){
            const outPort = document.createElement('div');
            outPort.className = 'ab-port out';
            outPort.onmousedown = (e) => abStartWire(e, node.id, 'out');
            el.appendChild(outPort);
        }
    }

    if(node.type !== 'trigger'){
        const inPort = document.createElement('div');
        inPort.className = 'ab-port in';
        inPort.dataset.node = node.id;
        el.appendChild(inPort);
    }

    el.addEventListener('mousedown', e=>{
        if(e.target.closest('.ab-node-del')||e.target.closest('.ab-port')||e.target.tagName==='INPUT'||e.target.tagName==='BUTTON') return;
        abDragNodeId = node.id;
        const r = el.getBoundingClientRect();
        abDragOffX = e.clientX - r.left;
        abDragOffY = e.clientY - r.top;
        document.querySelectorAll('.ab-node').forEach(n=>n.classList.remove('selected'));
        el.classList.add('selected');
    });
    if(dblclickable){
        el.addEventListener('dblclick', e=>{ if(!e.target.closest('.ab-port')) abOpenEdit(node.id); });
    }
    document.getElementById('ab-canvas').appendChild(el);
}

function abBuildListBody(body, node){
    body.innerHTML = '';

    const titleInput = document.createElement('input');
    titleInput.type = 'text';
    titleInput.className = 'ab-inline-input';
    titleInput.placeholder = 'List title (e.g. Choose a Department)';
    titleInput.value = node.data.title || '';
    titleInput.style.marginBottom = '6px';
    titleInput.addEventListener('mousedown', e => e.stopPropagation());
    titleInput.addEventListener('change', () => { node.data.title = titleInput.value; });
    body.appendChild(titleInput);

    const optsWrap = document.createElement('div');
    body.appendChild(optsWrap);

    const opts = abGetOptions(node);
    const optsToRender = opts.length ? opts : [''];

    optsToRender.forEach((optText, i) => {
        const row = document.createElement('div');
        row.className = 'ab-option-row';

        const badge = document.createElement('span');
        badge.textContent = (i+1);
        badge.style.cssText = 'background:rgba(37,211,102,0.2);color:#25d366;font-size:9px;font-weight:800;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;';
        row.appendChild(badge);
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'ab-inline-input';
        input.placeholder = 'Option '+(i+1);
        input.value = optText;
        input.style.cssText = 'flex:1;min-width:0;';
        input.addEventListener('mousedown', e => e.stopPropagation());
        input.addEventListener('change', () => {
            const o = abGetOptions(node);
            o[i] = input.value;
            node.data.options = o.join('\n');
        });
        row.appendChild(input);

        const port = document.createElement('div');
        port.className = 'ab-port out';
        port.dataset.port = 'opt'+i;
        port.title = 'Connect Option '+(i+1);
        port.style.cssText = 'position:relative;right:auto;top:auto;width:13px;height:13px;flex-shrink:0;';
        port.addEventListener('mousedown', e => abStartWire(e, node.id, 'opt'+i));
        row.appendChild(port);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '✕';
        removeBtn.style.cssText = 'background:none;border:none;color:rgba(255,255,255,0.3);cursor:pointer;font-size:12px;padding:2px;flex-shrink:0;';
        removeBtn.addEventListener('mousedown', e => e.stopPropagation());
        removeBtn.addEventListener('click', () => abRemoveListOption(node.id, i));
        row.appendChild(removeBtn);

        optsWrap.appendChild(row);
    });


      
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.textContent = '+ Add Option';
    addBtn.style.cssText = 'margin-top:6px;width:100%;padding:6px;border:1px dashed rgba(255,255,255,0.2);background:none;color:rgba(255,255,255,0.5);border-radius:6px;cursor:pointer;font-size:10.5px;font-family:inherit;';
    addBtn.addEventListener('mousedown', e => e.stopPropagation());
    addBtn.addEventListener('click', () => abAddListOption(node.id));
    body.appendChild(addBtn);
}

function abBuildFormBody(body, node){
    body.innerHTML = '';

    const label = document.createElement('div');
    label.textContent = 'Fields to ask the customer:';
    label.style.cssText = 'font-size:10.5px;color:rgba(255,255,255,0.4);margin-bottom:6px;';
    body.appendChild(label);

    const fieldsWrap = document.createElement('div');
    body.appendChild(fieldsWrap);

    node.data.fields.forEach((fld, i) => {
        const row = document.createElement('div');
        row.className = 'ab-option-row';

        const badge = document.createElement('span');
        badge.textContent = (i+1);
        badge.style.cssText = 'background:rgba(0,150,136,0.2);color:#1de9b6;font-size:9px;font-weight:800;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;';
        row.appendChild(badge);

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'ab-inline-input';
        input.placeholder = 'Field label';
        input.value = fld.label || '';
        input.style.cssText = 'flex:1;min-width:0;';
        input.addEventListener('mousedown', e => e.stopPropagation());
        input.addEventListener('change', () => {
            const key = input.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || ('field_'+i);
            node.data.fields[i].label = input.value;
            node.data.fields[i].key = key;
        });
        row.appendChild(input);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '✕';
        removeBtn.style.cssText = 'background:none;border:none;color:rgba(255,255,255,0.3);cursor:pointer;font-size:12px;padding:2px;flex-shrink:0;';
        removeBtn.addEventListener('mousedown', e => e.stopPropagation());
        removeBtn.addEventListener('click', () => abRemoveFormField(node.id, i));
        row.appendChild(removeBtn);

        fieldsWrap.appendChild(row);
    });

    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.textContent = '+ Add Field';
    addBtn.style.cssText = 'margin-top:6px;width:100%;padding:6px;border:1px dashed rgba(255,255,255,0.2);background:none;color:rgba(255,255,255,0.5);border-radius:6px;cursor:pointer;font-size:10.5px;font-family:inherit;';
    addBtn.addEventListener('mousedown', e => e.stopPropagation());
    addBtn.addEventListener('click', () => abAddFormField(node.id));
    body.appendChild(addBtn);
}

/* ── LIST OPTION INLINE EDITING ── */
function abUpdateListTitle(nodeId, value){
    const node = abNodes.find(n=>n.id===nodeId);
    if(node) node.data.title = value;
}
function abUpdateListOption(nodeId, idx, value){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node) return;
    const opts = abGetOptions(node);
    opts[idx] = value;
    node.data.options = opts.join('\n');
}function abAddListOption(nodeId){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node) return;
    const opts = (node.data.options || '').split('\n');
    opts.push('');
    node.data.options = opts.join('\n');
    const body = document.getElementById('body-'+nodeId);
    if(body){ abBuildListBody(body, node);
    setTimeout(abDrawConns, 30);
}

    setTimeout(abDrawConns, 30);
}

function abRemoveListOption(nodeId, idx){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node) return;
    const opts = abGetOptions(node);
    opts.splice(idx, 1);
    node.data.options = opts.join('\n');
    abConns = abConns.filter(c => !(c.from === nodeId && c.fromPort === 'opt'+idx));
    abConns.forEach(c => {
        if(c.from === nodeId && c.fromPort && c.fromPort.startsWith('opt')){
            const portIdx = parseInt(c.fromPort.replace('opt',''));
            if(portIdx > idx) c.fromPort = 'opt' + (portIdx - 1);
        }
    });
    const body = document.getElementById('body-'+nodeId);
    if(body) abBuildListBody(body, node);
    setTimeout(abDrawConns, 30);
}

/* ── FORM FIELD INLINE EDITING ── */
function abUpdateFormField(nodeId, idx, value){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node || !node.data.fields || !node.data.fields[idx]) return;
    const key = value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || ('field_'+idx);
    node.data.fields[idx].label = value;
    node.data.fields[idx].key = key;
}
function abAddFormField(nodeId){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node) return;
    if(!node.data.fields) node.data.fields = [];
    node.data.fields.push({key: 'field_'+node.data.fields.length, label: '', type: 'text', required: true});
    const body = document.getElementById('body-'+nodeId);
    if(body) abBuildFormBody(body, node);
    setTimeout(abDrawConns, 30);
}
function abRemoveFormField(nodeId, idx){
    const node = abNodes.find(n=>n.id===nodeId);
    if(!node || !node.data.fields) return;
    node.data.fields.splice(idx, 1);
    const body = document.getElementById('body-'+nodeId);
    if(body) abBuildFormBody(body, node);
    setTimeout(abDrawConns, 30);
}

/* ── DRAG-TO-CONNECT WIRING ── */
function abStartWire(e, nodeId, port){
    e.stopPropagation();
    e.preventDefault();
    abWiring = { fromId: nodeId, fromPort: port };
    document.addEventListener('mousemove', abWireMove);
    document.addEventListener('mouseup', abWireEnd);
}
function abWireMove(e){
    if(!abWiring) return;
    const canvasRect = document.getElementById('ab-canvas').getBoundingClientRect();
    abWiring.x = e.clientX - canvasRect.left;
    abWiring.y = e.clientY - canvasRect.top;
    abDrawConns();
}
function abWireEnd(e){
    document.removeEventListener('mousemove', abWireMove);
    document.removeEventListener('mouseup', abWireEnd);
    if(!abWiring) return;
    const target = document.elementFromPoint(e.clientX, e.clientY);
    const inPort = target && target.classList.contains('ab-port') && target.classList.contains('in') ? target : null;
    if(inPort){
        const toId = inPort.dataset.node;
        if(toId && toId !== abWiring.fromId){
            abConns = abConns.filter(c => !(c.from === abWiring.fromId && c.fromPort === abWiring.fromPort));
            abConns.push({ from: abWiring.fromId, fromPort: abWiring.fromPort, to: toId });
            abToast('Connected');
        }
    }
    abWiring = null;
    abDrawConns();
}

function abDeleteNode(id){
    abNodes = abNodes.filter(n=>n.id!==id);
    abConns = abConns.filter(c=>c.from!==id && c.to!==id);
    document.getElementById('node-'+id)?.remove();
    abDrawConns();
    if(!abNodes.length) document.getElementById('abEmpty').style.display='block';
}

function abPortPos(nodeId, port){
    const el = document.getElementById('node-'+nodeId);
    if(!el) return null;
    if(port && port.startsWith('opt')){
        const portEl = el.querySelector(`.ab-port[data-port="${port}"]`);
        if(portEl){
            const pr = portEl.getBoundingClientRect();
            const canvasRect = document.getElementById('ab-canvas').getBoundingClientRect();
            return { x: pr.left - canvasRect.left + pr.width/2, y: pr.top - canvasRect.top + pr.height/2 };
        }
    }
    return { x: el.offsetLeft + el.offsetWidth, y: el.offsetTop + 28 };
}
function abInPortPos(nodeId){
    const el = document.getElementById('node-'+nodeId);
    if(!el) return null;
    return { x: el.offsetLeft, y: el.offsetTop + 28 };
}

function abDrawConns(){
    const svg = document.getElementById('abSvg');
    svg.innerHTML = '';
    abConns.forEach(c=>{
        const p1 = abPortPos(c.from, c.fromPort);
        const p2 = abInPortPos(c.to);
        if(!p1||!p2) return;
        const mx = (p1.x+p2.x)/2;
        const path = document.createElementNS('http://www.w3.org/2000/svg','path');
        path.setAttribute('class','ab-conn');
        path.setAttribute('d', `M${p1.x},${p1.y} C${mx},${p1.y} ${mx},${p2.y} ${p2.x},${p2.y}`);
        svg.appendChild(path);
    });
    if(abWiring && abWiring.x !== undefined){
        const p1 = abPortPos(abWiring.fromId, abWiring.fromPort);
        if(p1){
            const mx = (p1.x+abWiring.x)/2;
            const path = document.createElementNS('http://www.w3.org/2000/svg','path');
            path.setAttribute('class','ab-preview');
            path.setAttribute('d', `M${p1.x},${p1.y} C${mx},${p1.y} ${mx},${abWiring.y} ${abWiring.x},${abWiring.y}`);
            svg.appendChild(path);
        }
    }
}

document.addEventListener('mousemove', e=>{
    if(!abDragNodeId) return;
    const canvasRect = document.getElementById('ab-canvas').getBoundingClientRect();
    const node = abNodes.find(n=>n.id===abDragNodeId);
    if(!node) return;
    node.x = e.clientX - canvasRect.left - abDragOffX;
    node.y = e.clientY - canvasRect.top - abDragOffY;
    const el = document.getElementById('node-'+node.id);
    el.style.left = node.x+'px'; el.style.top = node.y+'px';
    abDrawConns();
});
document.addEventListener('mouseup', ()=>{ abDragNodeId = null; });

/* ── NODE SUMMARY ── */
function abNodeSummary(node){
    if(node.type==='trigger') return node.data.trigger_value ? 'Keyword: '+node.data.trigger_value : 'Double-click to set trigger keyword';
    if(node.type==='message') return node.data.text || 'Double-click to set message text';
    if(node.type==='template') return node.data.template_name || 'Double-click to select template';
    if(node.type==='resource') return node.data.resource_name ? ('Books '+node.data.resource_name+(node.data.category?' ('+node.data.category+')':'')) : '{{ $resourceLabel }}';
    if(node.type==='end') return 'Conversation ends here';
    return '';
}

/* ── EDIT MODAL (trigger/message/template/end only) ── */
function abOpenEdit(id){
    abEditingId = id;
    const node = abNodes.find(n=>n.id===id);
    const f = document.getElementById('abEditFields');
    if(node.type==='trigger'){
        f.innerHTML = `<label>Trigger Keyword(s) — comma separated</label><input id="abF1" value="${abEsc(node.data.trigger_value||'')}" placeholder="e.g. appointment, doctor, book">`;
    } else if(node.type==='message'){
        f.innerHTML = `<label>Message Text</label><textarea id="abF1" rows="4" placeholder="Type the message...">${abEsc(node.data.text||'')}</textarea>`;
    } else if(node.type==='template'){
        f.innerHTML = `<label>Template Name</label><input id="abF1" value="${abEsc(node.data.template_name||'')}" placeholder="e.g. appointment_confirmation">`;
    } else if(node.type==='end'){
        f.innerHTML = `<label>Closing Message (optional)</label><textarea id="abF1" rows="3" placeholder="e.g. Thank you!">${abEsc(node.data.text||'')}</textarea>`;
    }
    document.getElementById('abEditModal').classList.add('open');
}
function abSaveNodeEdit(){
    const node = abNodes.find(n=>n.id===abEditingId);
    if(!node) return;
    if(node.type==='trigger') node.data.trigger_value = document.getElementById('abF1').value.trim();
    else if(node.type==='message') node.data.text = document.getElementById('abF1').value.trim();
    else if(node.type==='template') node.data.template_name = document.getElementById('abF1').value.trim();
    else if(node.type==='end') node.data.text = document.getElementById('abF1').value.trim();
    document.getElementById('node-'+node.id).remove();
    abRenderNode(node);
    document.getElementById('abEditModal').classList.remove('open');
    setTimeout(abDrawConns, 30);
}

/* ── SAVE / LOAD ── */
function abSave(){
    const payload = {
        nodes: abNodes.map(n=>({node_id:n.id, type:n.type, data:n.data, position_x:n.x, position_y:n.y})),
        connections: abConns,
    };
    fetch('/apps/'+ABT_APP+'/flow', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':ABT, 'Accept':'application/json'},
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(d=>{
        if(d.error){ abToast('❌ '+d.error); return; }
        document.getElementById('abStatus').textContent = 'Saved';
        abToast('✅ Flow saved');
    }).catch(()=> abToast('❌ Failed to save'));
}

function abLoad(){
    fetch('/apps/'+ABT_APP+'/flow', {credentials:'same-origin'})
    .then(r=>r.json()).then(d=>{
        const nodes = d.nodes || [];
        const conns = d.connections || [];
        if(!nodes.length) return;
        document.getElementById('abEmpty').style.display = 'none';
        nodes.forEach(n=>{
            const data = typeof n.data === 'string' ? (JSON.parse(n.data||'{}')||{}) : (n.data||{});
            const node = { id:n.node_id, type:n.type, x: Number(n.position_x)||0, y: Number(n.position_y)||0, data };
            abNodes.push(node);
            abRenderNode(node);
        });
        abConns = conns;
        setTimeout(abDrawConns, 150);
    }).catch(()=>{});
}

document.addEventListener('DOMContentLoaded', abLoad);
function abOpenAiModal(){
    document.getElementById('abAiPrompt').value = '';
    document.getElementById('abAiError').style.display = 'none';
    document.getElementById('abAiModal').style.display = 'flex';
}
async function abGenerateFlow(){
    const prompt = document.getElementById('abAiPrompt').value.trim();
    const btn = document.getElementById('abAiGenerateBtn');
    const errDiv = document.getElementById('abAiError');
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Generating...';
    btn.disabled = true;
    errDiv.style.display = 'none';
    try {
        const res = await fetch('/apps/'+ABT_APP+'/ai-generate-flow', {
            method:'POST', credentials:'same-origin',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':ABT,'Accept':'application/json'},
            body: JSON.stringify({prompt, app_type: ABT_APP})
        });
        const d = await res.json();
        abNodes=[]; abConns=[];
        document.querySelectorAll('.ab-node').forEach(el=>el.remove());
        document.getElementById('abSvg').innerHTML='';
        document.getElementById('abEmpty').style.display='none';
        // Fetch real resources to replace AI placeholders
        const realResources = await fetch('/apps/'+ABT_APP+'/resources-list', {credentials:'same-origin'})
            .then(r=>r.json()).then(d2=>d2.items||[]).catch(()=>[]);
        let resourceIdx = 0;
        (d.flow.nodes||[]).forEach(n=>{
            const data = typeof n.data==='string' ? JSON.parse(n.data||'{}') : (n.data||{});
            if(n.type === 'resource' && realResources.length > 0){
                const real = realResources[resourceIdx % realResources.length];
                resourceIdx++;
                data.resource_id = String(real.id);
                data.resource_name = real.name;
                data.category = real.category || '';
                data.price = real.price || null;
            }
            const node = {id:n.node_id, type:n.type, x:Number(n.position_x)||0, y:Number(n.position_y)||0, data};
            abNodes.push(node); abRenderNode(node);
        });
        abConns = d.flow.connections||[];
        setTimeout(abDrawConns, 150);
        document.getElementById('abAiModal').style.display='none';
        abToast('Flow generated with your real data! Review and save.');
    } catch(err){
        errDiv.textContent='Network error. Please try again.'; errDiv.style.display='block';
    } finally {
        btn.innerHTML='<i class="bi bi-stars me-1"></i> Generate Flow'; btn.disabled=false;
    }
}
</script>
@endpush
