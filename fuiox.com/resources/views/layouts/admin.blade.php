<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Fuiox Admin</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; min-height: 100vh; margin: 0; }

        /* SIDEBAR */
        #adminSidebar {
            width: 240px; min-height: 100vh;
            background: #0a1a0f;
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0;
            z-index: 1040;
            transition: width 0.25s ease, transform 0.3s ease;
            overflow: visible;
        }
        .sidebar-brand { padding: 20px 18px 16px; border-bottom: 1px solid rgba(255,255,255,.07); overflow: hidden; }
        .sidebar-brand .brand-title { font-size: 18px; font-weight: 800; color: #fff; white-space: nowrap; }
        .sidebar-brand .brand-title span { color: #00e676; }
        .sidebar-brand .brand-sub { font-size: 11px; color: rgba(255,255,255,.3); margin-top: 2px; white-space: nowrap; }
        .sidebar-nav { flex: 1; padding: 10px 0; overflow-y: auto; overflow-x: hidden; }
        .nav-section-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.25); text-transform: uppercase; letter-spacing: 1px; padding: 14px 18px 4px; white-space: nowrap; overflow: hidden; }
        .nav-link-item { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.55); text-decoration: none; padding: 11px 18px; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: background .15s, color .15s, border-color .15s; white-space: nowrap; overflow: hidden; }
        .nav-link-item i { font-size: 16px; width: 18px; text-align: center; flex-shrink: 0; }
        .nav-link-item:hover, .nav-link-item.active { background: rgba(0,230,118,.1); color: #00e676; border-left-color: #00e676; }
        .nav-link-item .nav-text { overflow: hidden; }
        .sidebar-footer { padding: 14px; border-top: 1px solid rgba(255,255,255,.07); overflow: hidden; }
        .admin-info-card { background: rgba(255,255,255,.06); border-radius: 10px; padding: 11px 13px; margin-bottom: 8px; overflow: hidden; }
        .admin-info-card .a-name { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; }
        .admin-info-card .a-role { font-size: 11px; color: rgba(255,255,255,.35); margin-top: 2px; white-space: nowrap; }
        .btn-logout { width: 100%; padding: 9px; border: none; border-radius: 8px; background: rgba(255,255,255,.06); color: rgba(255,255,255,.5); font-size: 13px; cursor: pointer; font-family: inherit; transition: background .2s, color .2s; display: flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap; overflow: hidden; }
        .btn-logout:hover { background: rgba(229,57,53,.18); color: #ff6b6b; }

        /* COLLAPSED */
        #adminSidebar.collapsed { width: 64px; }
        #adminSidebar.collapsed .brand-title,
        #adminSidebar.collapsed .brand-sub,
        #adminSidebar.collapsed .nav-section-label,
        #adminSidebar.collapsed .nav-text,
        #adminSidebar.collapsed .a-name,
        #adminSidebar.collapsed .a-role,
        #adminSidebar.collapsed .logout-text { display: none; }
        #adminSidebar.collapsed .nav-link-item { justify-content: center; padding: 12px 0; }
        #adminSidebar.collapsed .sidebar-brand { display: flex; justify-content: center; padding: 20px 0 16px; }
        #adminSidebar.collapsed .admin-info-card { padding: 8px; text-align: center; }
        #adminSidebar.collapsed ~ #adminMain { margin-left: 64px; }

        /* TOGGLE BTN */
        .sidebar-toggle-btn {
            position: absolute; top: 50%; right: -13px;
            transform: translateY(-50%);
            width: 26px; height: 26px;
            background: #fff; border: 1.5px solid #e0e0e0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 1060;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: 0.2s; color: #555; font-size: 12px;
        }
        .sidebar-toggle-btn:hover { background: #00e676; color: #fff; border-color: #00e676; }

        /* MOBILE OVERLAY */
        #adminOverlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1039; }

        /* MAIN */
        #adminMain { margin-left: 240px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left 0.25s ease; }
        #adminTopbar { background: #fff; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #e8ecf0; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-title { font-size: 17px; font-weight: 700; color: #0a1a0f; }
        .topbar-date { font-size: 13px; color: #aaa; }
        #sidebarToggle { display: none; background: none; border: none; font-size: 22px; color: #555; cursor: pointer; padding: 4px 6px; border-radius: 6px; line-height: 1; }
        #sidebarToggle:hover { background: #f0f2f5; }
        #pageContent { flex: 1; padding: 24px; }
        .alert-success-custom { background: #e8f5e9; color: #2e7d32; padding: 11px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; border-left: 3px solid #00c853; }
        .alert-error-custom { background: #fdecea; color: #c62828; padding: 11px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; border-left: 3px solid #e53935; }
        .fgrp { margin-bottom: 16px; }
        .fgrp label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .3px; }
        .fgrp input, .fgrp select, .fgrp textarea { width: 100%; padding: 10px 14px; border: 1.5px solid #e5e9f0; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; color: #333; transition: border-color .15s; }
        .fgrp input:focus, .fgrp select:focus, .fgrp textarea:focus { border-color: #00c853; }

        @media (max-width: 991px) {
            #adminSidebar { transform: translateX(-100%); width: 240px !important; }
            #adminSidebar.open { transform: translateX(0); }
            #adminOverlay.show { display: block; }
            #adminMain { margin-left: 0 !important; }
            #sidebarToggle { display: block; }
            #pageContent { padding: 16px; }
            .sidebar-toggle-btn { display: none; }
        }
    </style>
</head>
<body>

<aside id="adminSidebar">
    <button class="sidebar-toggle-btn" onclick="adminToggleSidebar()" title="Toggle sidebar">
        <i class="bi bi-chevron-left" id="adminSidebarIcon"></i>
    </button>
    <div class="sidebar-brand">
        <div class="brand-title">Fuiox <span>Admin</span></div>
        <div class="brand-sub">Control Panel</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i><span class="nav-text"> Dashboard</span>
        </a>
        <a href="{{ route('admin.credential.requests') }}" class="nav-link-item {{ request()->routeIs('admin.credential.requests') ? 'active' : '' }}">
            <i class="bi bi-key-fill"></i><span class="nav-text"> Credential Requests</span>
        </a>
        <a href="{{ route('admin.packages') }}" class="nav-link-item {{ request()->routeIs('admin.packages') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i><span class="nav-text"> Packages</span>
        </a>
        @stack('nav_items')
    </nav>
    <div class="sidebar-footer">
        <div class="admin-info-card">
            <div class="a-name">Admin</div>
            <div class="a-role">Super Administrator</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="bi bi-box-arrow-left"></i><span class="logout-text"> Sign out</span>
            </button>
        </form>
    </div>
</aside>

<div id="adminOverlay"></div>

<div id="adminMain">
    <header id="adminTopbar">
        <div class="topbar-left">
            <button id="sidebarToggle" onclick="toggleAdminSidebar()"><i class="bi bi-list"></i></button>
            <span class="topbar-title">@yield('page_title', 'Admin Panel')</span>
        </div>
        <span class="topbar-date">{{ now()->format('D, d M Y') }}</span>
    </header>
    <main id="pageContent">
        @if(session('success'))<div class="alert-success-custom">✅ {{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert-error-custom">❌ {{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert-error-custom">❌ {{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>

@stack('modals')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleAdminSidebar(){
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('adminOverlay').classList.toggle('show');
}
document.getElementById('adminOverlay').addEventListener('click', toggleAdminSidebar);

function adminToggleSidebar(){
    const s=document.getElementById('adminSidebar');
    const icon=document.getElementById('adminSidebarIcon');
    const collapsed=s.classList.toggle('collapsed');
    icon.className=collapsed?'bi bi-chevron-right':'bi bi-chevron-left';
    localStorage.setItem('adminSidebarCollapsed',collapsed?'1':'0');
}
document.addEventListener('DOMContentLoaded',function(){
    if(localStorage.getItem('adminSidebarCollapsed')==='1'){
        document.getElementById('adminSidebar').classList.add('collapsed');
        document.getElementById('adminSidebarIcon').className='bi bi-chevron-right';
    }
});

function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
const csrf=document.querySelector('meta[name=csrf-token]').content;
function adminPost(url,data,cb){ fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)}).then(r=>r.json()).then(d=>{if(cb)cb(d);else location.reload();}).catch(()=>location.reload()); }
function showToast(msg,type){ const t=document.createElement('div'); t.textContent=msg; t.style.cssText=`position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.15);background:${type==='success'?'#25d366':'#e53935'};color:#fff;`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); }
</script>
@stack('scripts')
</body>
</html>