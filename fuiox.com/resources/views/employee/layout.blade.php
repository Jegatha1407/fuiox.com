<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'App Portal')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background:#f5f7fa; display:flex; min-height:100vh; }
        .emp-sidebar { width:240px; background:#fff; border-right:1.5px solid #e5e9f0; display:flex; flex-direction:column; flex-shrink:0; position:fixed; top:0; left:0; height:100vh; z-index:100; }
        .emp-logo { padding:22px 20px; border-bottom:1.5px solid #e5e9f0; }
        .emp-logo-title { font-size:15px; font-weight:900; color:#1a1a2e; }
        .emp-logo-sub { font-size:11px; color:#888; margin-top:2px; }
        .emp-nav { flex:1; padding:16px 12px; overflow-y:auto; }
        .emp-nav-item { display:flex; align-items:center; gap:10px; padding:11px 14px; border-radius:10px; text-decoration:none; color:#555; font-size:13.5px; font-weight:600; margin-bottom:4px; transition:.15s; }
        .emp-nav-item:hover { background:#f5f7fa; color:#1a1a2e; }
        .emp-nav-item.active { background:#e8f5e9; color:#25d366; }
        .emp-nav-item i { font-size:16px; width:20px; text-align:center; }
        .emp-bottom { padding:16px 12px; border-top:1.5px solid #e5e9f0; }
        .emp-user { display:flex; align-items:center; gap:10px; padding:10px 14px; border-radius:10px; background:#f5f7fa; margin-bottom:8px; }
        .emp-user-avatar { width:32px; height:32px; border-radius:50%; background:#25d366; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:800; flex-shrink:0; }
        .emp-user-name { font-size:12.5px; font-weight:700; color:#1a1a2e; }
        .emp-user-role { font-size:10.5px; color:#888; }
        .emp-main { margin-left:240px; flex:1; min-height:100vh; }
        .emp-topbar { background:#fff; border-bottom:1.5px solid #e5e9f0; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; }
        .emp-topbar-title { font-size:15px; font-weight:800; color:#1a1a2e; }
        .emp-content { padding:0; }
        .btn-fu-primary { background:#25d366; color:#fff; border:none; font-weight:700; }
        .btn-fu-primary:hover { background:#1db954; color:#fff; }
        .ab-btn { padding:9px 14px; border-radius:9px; border:none; font-size:12.5px; font-weight:700; cursor:pointer; font-family:inherit; white-space:nowrap; }
        .ab-btn-save { background:#25d366; color:#fff; }
        .ab-btn-back { background:rgba(255,255,255,0.07); color:#fff; text-decoration:none; display:inline-flex; align-items:center; }
        @media(max-width:768px){
            .emp-sidebar { display:none; }
            .emp-main { margin-left:0; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $empUser = \App\Models\User::find(session('auth_user'));
    $empAssignment = $empUser ? \Illuminate\Support\Facades\DB::table('app_assignments')->where('staff_user_id', $empUser->id)->first() : null;
    $empAppType = $empAssignment->app_type ?? null;
    $empAppInfo = $empAppType ? (\App\Http\Controllers\AppsController::catalog()[$empAppType] ?? null) : null;
    $empConfig = $empAppType ? \App\Http\Controllers\AppsController::appConfig($empAppType) : null;
    $currentRoute = request()->route()->getName() ?? '';
@endphp

<div class="emp-sidebar">
    <div class="emp-logo">
        <div class="emp-logo-title">{{ $empAppInfo['icon'] ?? '🏢' }} {{ $empAppInfo['name'] ?? 'App Portal' }}</div>
        <div class="emp-logo-sub">Employee Portal</div>
    </div>

    <nav class="emp-nav">
        @if($empAppType)
        <a href="{{ route('apps.employee.dashboard', $empAppType) }}" class="emp-nav-item {{ $currentRoute === 'apps.employee.dashboard' ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        @if($empAssignment && $empAssignment->can_flow_builder)
        <a href="{{ route('apps.employee.flow-builder', $empAppType) }}" class="emp-nav-item {{ $currentRoute === 'apps.employee.flow-builder' ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i> Flow Builder
        </a>
        @endif

        @if($empAssignment && $empAssignment->can_resources)
        <a href="{{ route('apps.employee.resources', $empAppType) }}" class="emp-nav-item {{ $currentRoute === 'apps.employee.resources' ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manage {{ $empConfig['resource_label'] ?? 'Resources' }}s
        </a>
        @endif

        @if($empAssignment && $empAssignment->can_records)
        <a href="{{ route('apps.employee.records', $empAppType) }}" class="emp-nav-item {{ $currentRoute === 'apps.employee.records' ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> {{ $empConfig['record_label'] ?? 'Records' }}s
        </a>
        @endif
        @endif

        <a href="{{ route('apps.employee.change-password') }}" class="emp-nav-item {{ $currentRoute === 'apps.employee.change-password' ? 'active' : '' }}">
            <i class="bi bi-lock"></i> Change Password
        </a>
    </nav>

    <div class="emp-bottom">
        <div class="emp-user">
            <div class="emp-user-avatar">{{ strtoupper(substr($empUser->name ?? 'E', 0, 1)) }}</div>
            <div>
                <div class="emp-user-name">{{ $empUser->name ?? 'Employee' }}</div>
                <div class="emp-user-role">App Employee</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;background:none;border:none;color:#e53935;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>

<div class="emp-main">
    <div class="emp-topbar">
        <div class="emp-topbar-title">@yield('title', 'Dashboard')</div>
        <div>@stack('topbar_actions')</div>
    </div>
    <div class="emp-content">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div id="fuModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(20,20,30,0.55);backdrop-filter:blur(3px);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:28px;width:380px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;">
        <div id="fuModalIcon" style="width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;"></div>
        <div id="fuModalMessage" style="font-size:14.5px;color:#333;line-height:1.5;margin-bottom:22px;"></div>
        <div id="fuModalButtons" style="display:flex;gap:10px;"></div>
    </div>
</div>

<script>
function fuAlert(message, opts){
    opts = opts || {};
    return new Promise(function(resolve){
        const overlay = document.getElementById('fuModalOverlay');
        const icon = document.getElementById('fuModalIcon');
        const msg = document.getElementById('fuModalMessage');
        const btns = document.getElementById('fuModalButtons');
        icon.style.background = opts.danger ? '#fdecea' : '#e8f5e9';
        icon.innerHTML = opts.danger ? '⚠️' : '✅';
        msg.textContent = message;
        btns.innerHTML = '';
        const okBtn = document.createElement('button');
        okBtn.textContent = 'OK';
        okBtn.style.cssText = 'flex:1;padding:11px;border:none;border-radius:9px;background:#25d366;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;font-size:13.5px;';
        okBtn.onclick = function(){ overlay.style.display='none'; resolve(true); };
        btns.appendChild(okBtn);
        overlay.style.display = 'flex';
    });
}
function fuConfirm(message, opts){
    opts = opts || {};
    return new Promise(function(resolve){
        const overlay = document.getElementById('fuModalOverlay');
        const icon = document.getElementById('fuModalIcon');
        const msg = document.getElementById('fuModalMessage');
        const btns = document.getElementById('fuModalButtons');
        icon.style.background = opts.danger ? '#fdecea' : '#fff3e0';
        icon.innerHTML = opts.danger ? '🗑️' : '❓';
        msg.textContent = message;
        btns.innerHTML = '';
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = opts.cancelLabel || 'Cancel';
        cancelBtn.style.cssText = 'flex:1;padding:11px;border:none;border-radius:9px;background:#f5f5f5;color:#333;font-weight:600;cursor:pointer;font-family:inherit;font-size:13.5px;';
        cancelBtn.onclick = function(){ overlay.style.display='none'; resolve(false); };
        btns.appendChild(cancelBtn);
        const okBtn = document.createElement('button');
        okBtn.textContent = opts.confirmLabel || 'Confirm';
        okBtn.style.cssText = 'flex:1;padding:11px;border:none;border-radius:9px;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;font-size:13.5px;background:'+(opts.danger?'#e53935':'#25d366')+';';
        okBtn.onclick = function(){ overlay.style.display='none'; resolve(true); };
        btns.appendChild(okBtn);
        overlay.style.display = 'flex';
    });
}
</script>

@stack('scripts')
</body>
</html>
