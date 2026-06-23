@extends('layouts.app')
@section('title', 'Apps')
@section('page_title', 'Apps')

@push('styles')
<style>
.ai-switch{position:relative;width:42px;height:23px;display:inline-block;}
.ai-switch input{opacity:0;width:0;height:0;}
.ai-slider{position:absolute;inset:0;background:#ddd;border-radius:23px;cursor:pointer;transition:.2s;}
.ai-slider::before{content:"";position:absolute;width:17px;height:17px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;}
.ai-switch input:checked + .ai-slider{background:#25d366;}
.ai-switch input:checked + .ai-slider::before{transform:translateX(19px);}
</style>
<style>
.app-card { background:#fff; border:1.5px solid #e5e9f0; border-radius:16px; padding:24px; transition:.2s; height:100%; }
.app-card:hover { border-color:#25d366; box-shadow:0 4px 20px rgba(37,211,102,0.1); }
.app-icon { width:56px; height:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:26px; margin-bottom:14px; }
.app-name { font-size:16px; font-weight:800; color:#1a1a2e; margin-bottom:4px; }
.app-desc { font-size:13px; color:#888; margin-bottom:16px; line-height:1.5; }
.app-status { font-size:12px; font-weight:700; padding:4px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:5px; margin-bottom:16px; }
.app-status.active { background:#e8f5e9; color:#2e7d32; }
.app-status.inactive { background:#f5f5f5; color:#999; }
.btn-app { width:100%; padding:10px; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; font-family:inherit; transition:.2s; margin-bottom:8px; }
.btn-app.install { background:#25d366; color:#fff; }
.btn-app.install:hover { background:#1fba58; }
.btn-app.open { background:#1a1a2e; color:#fff; }
.btn-app.open:hover { background:#000; }
.btn-app.deactivate { background:#fdecea; color:#c62828; }
.btn-app.deactivate:hover { background:#ffcdd2; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

@if(session('success'))
<div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
@endif

<div class="mb-4">
    <p style="color:#888;font-size:14px;">Activate industry-specific apps. When a customer's message matches an active app's topic, the AI automatically routes the conversation to that app's flow instead of a normal reply.</p>
</div>

<div class="row g-4">
@foreach($catalog as $key => $app)
    @php $inst = $installed[$key] ?? null; $isActive = $inst && $inst->is_active; @endphp
    <div class="col-md-6 col-xl-4">
        <div class="app-card">
            <div class="app-icon" style="background:{{ $app['color'] }};">{{ $app['icon'] }}</div>
            <div class="app-name">{{ $app['name'] }}</div>
            <div class="app-desc">{{ $app['desc'] }}</div>

            @if($isActive)
                <div style="display:flex;align-items:center;justify-content:space-between;background:#f9f9f9;border-radius:10px;padding:8px 12px;margin-bottom:12px;">
                    <span style="font-size:12px;color:#555;"><i class="bi bi-robot me-1"></i>Bot Active</span>
                    <label class="ai-switch" style="margin:0;">
                        <input type="checkbox" {{ ($inst->is_bot_active ?? 1) ? 'checked' : '' }} onchange="toggleAppBot(this, '{{ $key }}')">
                        <span class="ai-slider"></span>
                    </label>
                </div>
                <div class="app-status active"><i class="bi bi-check-circle-fill"></i> Active</div>
                <a href="{{ route('apps.builder', $key) }}" class="btn-app open d-block text-center text-decoration-none">
                    <i class="bi bi-diagram-3 me-1"></i> Open Flow Builder
                </a>
                <button class="btn-app deactivate" onclick="deactivateApp('{{ $key }}')">Deactivate</button>
            @else
                <div class="app-status inactive"><i class="bi bi-x-circle"></i> Not Active</div>
                <button class="btn-app install" onclick="installApp('{{ $key }}')">
                    <i class="bi bi-plus-lg me-1"></i> Activate App
                </button>
            @endif
        </div>
    </div>
@endforeach
</div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const ABT2 = document.querySelector("meta[name=csrf-token]").content;
function toggleAppBot(cb, appType){
    fetch("/apps/"+appType+"/toggle-bot", {method:"POST", credentials:"same-origin", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":ABT2}, body: JSON.stringify({status: cb.checked ? "on" : "off"})});
}

async function installApp(appType){
    const res = await fetch('/apps/install', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({app_type: appType})
    });
    const d = await res.json();
    if(d.success) location.reload();
    else await fuAlert(d.error || 'Failed to activate', {danger:true});
}

async function deactivateApp(appType){
    const ok = await fuConfirm('Deactivate this app? Customers will no longer be routed to it, but your flow design stays saved.', {confirmLabel:'Deactivate', danger:true});
    if(!ok) return;
    const res = await fetch('/apps/deactivate', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({app_type: appType})
    });
    const d = await res.json();
    if(d.success) location.reload();
}
</script>
@endpush
