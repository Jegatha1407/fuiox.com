<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fuiox') — Fuiox Technologies</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', sans-serif;
            --fu-green: #25d366;
            --fu-green-dark: #1aaa50;
            --fu-green-light: #e8f5e9;
            --fu-dark: #1a1a2e;
            --fu-sidebar-w: 255px;
            --fu-sidebar-collapsed-w: 64px;
            --fu-topbar-h: 60px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f6f8fa; overflow-x: hidden; }

        /* SIDEBAR */
        #fuSidebar {
            width: var(--fu-sidebar-w); height: 100vh;
            position: fixed; top: 0; left: 0;
            background: var(--fu-dark);
            display: flex; flex-direction: column;
            z-index: 1040;
            transition: width 0.25s ease, transform 0.3s ease;
            overflow: visible;
        }
        .sidebar-logo { padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; overflow: hidden; }
        .sidebar-logo h5 { font-size: 17px; font-weight: 800; color: #fff; margin: 0; white-space: nowrap; }
        .sidebar-logo h5 span { color: var(--fu-green); }
        .sidebar-logo small { font-size: 11px; color: rgba(255,255,255,0.35); white-space: nowrap; }
        .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 10px 0; }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }
        .nav-section-label { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.25); text-transform: uppercase; letter-spacing: 1px; padding: 14px 20px 4px; white-space: nowrap; overflow: hidden; }
        .sidebar-nav .nav-link { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: rgba(255,255,255,0.55); font-size: 13.5px; font-weight: 500; border-left: 3px solid transparent; border-radius: 0; transition: all 0.15s; text-decoration: none; white-space: nowrap; overflow: hidden; }
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .sidebar-nav .nav-link.active { background: rgba(37,211,102,0.12); color: var(--fu-green); border-left-color: var(--fu-green); font-weight: 600; }
        .sidebar-nav .nav-link .bi { font-size: 16px; flex-shrink: 0; }
        .sidebar-nav .nav-link span.nav-text { overflow: hidden; transition: opacity 0.2s; }
        .sidebar-bottom { padding: 14px; border-top: 1px solid rgba(255,255,255,0.08); flex-shrink: 0; overflow: hidden; }
        .sidebar-user-card { background: rgba(255,255,255,0.06); border-radius: 10px; padding: 11px 13px; margin-bottom: 10px; overflow: hidden; }
        .sidebar-user-card .name { font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; }
        .sidebar-user-card .email { font-size: 11px; color: rgba(255,255,255,0.35); white-space: nowrap; }
        .btn-signout { width: 100%; padding: 9px; background: rgba(255,255,255,0.06); border: none; border-radius: 8px; color: rgba(255,255,255,0.45); font-size: 13px; font-family: inherit; cursor: pointer; transition: 0.2s; white-space: nowrap; overflow: hidden; }
        .btn-signout:hover { background: rgba(229,57,53,0.2); color: #ff6b6b; }

        /* COLLAPSED STATE */
        #fuSidebar.collapsed { width: var(--fu-sidebar-collapsed-w); }
        #fuSidebar.collapsed .sidebar-logo { padding: 20px 0 16px; display: flex; justify-content: center; }
        #fuSidebar.collapsed .sidebar-logo h5,
        #fuSidebar.collapsed .sidebar-logo small,
        #fuSidebar.collapsed .nav-section-label,
        #fuSidebar.collapsed .nav-text,
        #fuSidebar.collapsed .sidebar-user-card .name,
        #fuSidebar.collapsed .sidebar-user-card .email,
        #fuSidebar.collapsed .btn-signout .signout-text { display: none; }
        #fuSidebar.collapsed .sidebar-nav .nav-link { justify-content: center; padding: 12px 0; }
        #fuSidebar.collapsed .sidebar-user-card { padding: 8px; text-align: center; }
        #fuSidebar.collapsed ~ #fuTopbar { left: var(--fu-sidebar-collapsed-w); }
        #fuSidebar.collapsed ~ #fuContent { margin-left: var(--fu-sidebar-collapsed-w); }

        /* SIDEBAR TOGGLE BTN */
        .sidebar-toggle-btn {
            position: absolute;
            top: 50%;
            right: -13px;
            transform: translateY(-50%);
            width: 26px; height: 26px;
            background: #fff;
            border: 1.5px solid #e0e0e0;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            z-index: 1060;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: 0.2s;
            color: #555;
            font-size: 12px;
            flex-shrink: 0;
        }
        .sidebar-toggle-btn:hover { background: var(--fu-green); color: #fff; border-color: var(--fu-green); }

        /* TOPBAR */
        #fuTopbar { height: var(--fu-topbar-h); background: #fff; border-bottom: 1px solid #e8ecf0; position: fixed; top: 0; left: var(--fu-sidebar-w); right: 0; z-index: 1030; display: flex; align-items: center; padding: 0 20px; gap: 12px; transition: left 0.25s ease; }
        .topbar-title { font-size: 16px; font-weight: 700; color: var(--fu-dark); flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        #sidebarToggle { display: none; background: none; border: none; font-size: 22px; color: var(--fu-dark); cursor: pointer; padding: 4px 6px; border-radius: 6px; flex-shrink: 0; }
        #sidebarToggle:hover { background: #f0f0f0; }
        .plan-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; white-space: nowrap; text-decoration: none; }
        .plan-badge.active { background: #e8f5e9; color: #2e7d32; }
        .plan-badge.trial  { background: #fff3e0; color: #e65100; }
        .plan-badge.expired{ background: #fdecea; color: #c62828; }
        .notif-btn { position: relative; width: 36px; height: 36px; background: none; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: 0.15s; }
        .notif-btn:hover { background: #f0f0f0; }
        .notif-badge { position: absolute; top: 2px; right: 2px; width: 16px; height: 16px; background: #e53935; color: #fff; font-size: 9px; font-weight: 700; border-radius: 50%; display: none; align-items: center; justify-content: center; }
        .profile-btn { display: flex; align-items: center; gap: 7px; background: none; border: none; padding: 5px 8px; border-radius: 8px; cursor: pointer; transition: 0.15s; }
        .profile-btn:hover { background: #f0f0f0; }
        .profile-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--fu-green); color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .profile-name { font-size: 13px; font-weight: 600; color: var(--fu-dark); }

        /* PAGE CONTENT */
        #fuContent { margin-left: var(--fu-sidebar-w); margin-top: var(--fu-topbar-h); min-height: calc(100vh - var(--fu-topbar-h)); padding: 24px; transition: margin-left 0.25s ease; }

        /* SIDEBAR OVERLAY */
        #sidebarOverlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1035; }

        /* COMMON */
        .fu-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: none; }
        .fu-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 16px 20px; font-weight: 700; font-size: 15px; }
        .fu-card .card-body { padding: 20px; }
        .fu-stat-card { border-radius: 14px; padding: 18px; border-left: 4px solid var(--fu-green); background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .fu-stat-card.blue  { border-left-color: #1976d2; }
        .fu-stat-card.orange{ border-left-color: #f57c00; }
        .fu-stat-card.purple{ border-left-color: #7b1fa2; }
        .fu-stat-card.red   { border-left-color: #e53935; }
        .fu-stat-card .stat-icon { font-size: 26px; }
        .fu-stat-card .stat-label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .fu-stat-card .stat-value { font-size: 26px; font-weight: 800; color: var(--fu-dark); }
        .btn-fu-primary { background: var(--fu-green); color: #fff; border: none; font-weight: 600; }
        .btn-fu-primary:hover { background: var(--fu-green-dark); color: #fff; }
        .btn-fu-outline { border: 1.5px solid var(--fu-green); color: var(--fu-green); background: transparent; font-weight: 600; }
        .btn-fu-outline:hover { background: var(--fu-green); color: #fff; }

        /* TOAST */
        #fuToast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--fu-dark); color: #fff; padding: 11px 24px; border-radius: 24px; font-size: 13px; font-weight: 600; z-index: 99999; box-shadow: 0 4px 20px rgba(0,0,0,0.3); display: none; opacity: 0; transition: opacity 0.3s; white-space: nowrap; }

        /* RESPONSIVE */
        @media (max-width: 991.98px) {
            #fuSidebar { transform: translateX(-100%); width: var(--fu-sidebar-w) !important; }
            #fuSidebar.show { transform: translateX(0); }
            #fuTopbar { left: 0 !important; }
            #fuContent { margin-left: 0 !important; }
            #sidebarToggle { display: flex !important; align-items: center; justify-content: center; }
            #sidebarOverlay.show { display: block; }
            .profile-name { display: none; }
            .sidebar-toggle-btn { display: none; }
        }
        @media (max-width: 575.98px) { #fuContent { padding: 16px; } #fuTopbar { padding: 0 12px; } }

        @yield('page_styles')
    </style>
</head>
<body>

<div id="sidebarOverlay" onclick="fuCloseSidebar()"></div>

<!-- SIDEBAR -->
<div id="fuSidebar">
    <button class="sidebar-toggle-btn" id="sidebarCollapseBtn" onclick="fuToggleSidebar()" title="Toggle sidebar">
        <i class="bi bi-chevron-left" id="sidebarCollapseIcon"></i>
    </button>
    <div class="sidebar-logo">
        <h5>Fuiox <span>Technologies</span></h5>
        <small>{{ $user->organisation ?? '' }}</small>
    </div>
    <nav class="sidebar-nav">
        @if(empty($user->parent_user_id))
        <div class="nav-section-label">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-link @if(Route::is('dashboard')) active @endif"><i class="bi bi-grid-fill"></i><span class="nav-text"> Dashboard</span></a>
        <a href="{{ route('chat') }}" class="nav-link @if(Route::is('chat')) active @endif"><i class="bi bi-chat-dots-fill"></i><span class="nav-text"> Chat</span></a>
        <div class="nav-section-label">Tools</div>
        <a href="{{ route('contacts') }}" class="nav-link @if(Route::is('contacts')) active @endif"><i class="bi bi-people-fill"></i><span class="nav-text"> Contacts</span></a>
        <a href="{{ route('campaigns') }}" class="nav-link @if(Route::is('campaigns')) active @endif"><i class="bi bi-megaphone-fill"></i><span class="nav-text"> Campaigns</span></a>
        <a href="{{ route('bulk.template') }}" class="nav-link @if(Route::is('bulk.template')) active @endif"><i class="bi bi-send-fill"></i><span class="nav-text"> Bulk Send</span></a>
        <a href="{{ route('templates.manager') }}" class="nav-link @if(Route::is('templates.manager')) active @endif"><i class="bi bi-file-earmark-text-fill"></i><span class="nav-text"> Templates</span></a>
        <a href="{{ route('reports') }}" class="nav-link @if(Route::is('reports')) active @endif"><i class="bi bi-bar-chart-fill"></i><span class="nav-text"> Reports</span></a>
        <a href="{{ route('team') }}" class="nav-link @if(Route::is('team')) active @endif"><i class="bi bi-person-workspace"></i><span class="nav-text"> Team</span></a>
        <a href="{{ route('flows.builder') }}" class="nav-link @if(Route::is('flows.builder')) active @endif"><i class="bi bi-lightning-fill"></i><span class="nav-text"> Flow Builder</span></a>
        <a href="{{ route('automation') }}" class="nav-link @if(Route::is('automation')) active @endif"><i class="bi bi-robot"></i><span class="nav-text"> Automation</span></a>
        <a href="{{ route('channels') }}" class="nav-link @if(Route::is('channels')) active @endif"><i class="bi bi-grid-1x2-fill"></i><span class="nav-text"> Channels</span></a>
        <a href="{{ route('ai.settings') }}" class="nav-link @if(Route::is('ai.settings')) active @endif"><i class="bi bi-stars"></i><span class="nav-text"> AI Settings</span></a>
        <a href="{{ route('apps') }}" class="nav-link @if(Route::is('apps') || Route::is('apps.builder')) active @endif"><i class="bi bi-grid-3x3-gap-fill"></i><span class="nav-text"> Apps</span></a>
        <div class="nav-section-label">Account</div>
        <a href="{{ route('billing') }}" class="nav-link @if(Route::is('billing')) active @endif"><i class="bi bi-credit-card-fill"></i><span class="nav-text"> Billing</span></a>
        <a href="{{ route('settings') }}" class="nav-link @if(Route::is('settings')) active @endif"><i class="bi bi-gear-fill"></i><span class="nav-text"> Settings</span></a>
        <a href="{{ route('api.docs') }}" class="nav-link @if(Route::is('api.docs')) active @endif"><i class="bi bi-code-slash"></i><span class="nav-text"> API Docs</span></a>
        @endif

        @if(!empty($user->parent_user_id))
        <div class="nav-section-label">Main</div>
        <a href="{{ route('agent.dashboard') }}" class="nav-link @if(Route::is('agent.dashboard')) active @endif"><i class="bi bi-grid-fill"></i><span class="nav-text"> Dashboard</span></a>
        <a href="{{ route('chat') }}" class="nav-link @if(Route::is('chat')) active @endif"><i class="bi bi-chat-dots-fill"></i><span class="nav-text"> Chat</span></a>
        <div class="nav-section-label">Account</div>
        <a href="{{ route('agent.password') }}" class="nav-link @if(Route::is('agent.password')) active @endif"><i class="bi bi-shield-lock-fill"></i><span class="nav-text"> Change Password</span></a>
        @endif
    </nav>
    <div class="sidebar-bottom">
        <div class="sidebar-user-card">
            <div class="name">{{ $user->name }}</div>
            <div class="email text-truncate">{{ $user->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-signout" type="submit"><i class="bi bi-box-arrow-left"></i><span class="signout-text"> Sign out</span></button>
        </form>
    </div>
</div>

<!-- TOPBAR -->
<div id="fuTopbar">
    <button id="sidebarToggle" onclick="fuOpenSidebar()"><i class="bi bi-list"></i></button>
    <div class="topbar-title">@yield('page_title', 'Dashboard')</div>
    <div class="topbar-right">
        @if(empty($user->parent_user_id))
        @php
            $hasActiveSub = \Illuminate\Support\Facades\DB::table('subscriptions')->where('user_id',$user->id)->where('status','active')->where('expires_at','>',now())->exists();
            $inTrial = $user->free_trial_enabled && $user->trial_ends_at && \Carbon\Carbon::parse($user->trial_ends_at)->isFuture();
            $trialDays = $inTrial ? (int)\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($user->trial_ends_at)) : 0;
        @endphp
        @if($hasActiveSub)
            @php $subPlan = \Illuminate\Support\Facades\DB::table('subscriptions')->where('user_id',$user->id)->where('status','active')->where('expires_at','>',now())->orderByDesc('created_at')->first(); @endphp
            <a href="{{ route('billing') }}" class="plan-badge active">✅ {{ $subPlan->plan_name ?? 'Active' }}</a>
        @elseif($inTrial)
            <a href="{{ route('billing') }}" class="plan-badge trial">🎁 Free Trial · {{ $trialDays }}d left</a>
        @else
            <a href="{{ route('billing') }}" class="plan-badge expired">⚠️ Upgrade</a>
        @endif
        <div class="dropdown">
            <button class="notif-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" onclick="fuLoadNotifs()">
                <i class="bi bi-bell-fill"></i>
                <span class="notif-badge" id="notifBadge"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0" style="width:320px;margin-top:8px;">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <span class="fw-bold" style="font-size:15px;">Notifications</span>
                    <button onclick="fuMarkAllRead()" class="btn btn-link btn-sm p-0 text-success text-decoration-none" style="font-size:12px;font-weight:600;">Mark all read</button>
                </div>
                <div id="notifList" style="max-height:300px;overflow-y:auto;"><div class="text-center text-muted py-4" style="font-size:13px;">Loading…</div></div>
            </div>
        </div>
        <div class="dropdown">
            <button class="profile-btn" data-bs-toggle="dropdown">
                <div class="profile-avatar">{{ substr($user->name,0,1) }}</div>
                <span class="profile-name">{{ $user->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:10px;color:#888;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0" style="min-width:200px;margin-top:8px;">
                <div class="px-3 py-2 border-bottom">
                    <div class="fw-bold" style="font-size:14px;">{{ $user->name }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $user->email }}</div>
                </div>
                <a href="{{ route('settings') }}" class="dropdown-item py-2"><i class="bi bi-gear me-2"></i>Settings</a>
                <a href="{{ route('billing') }}" class="dropdown-item py-2"><i class="bi bi-credit-card me-2"></i>Billing</a>
                <div class="dropdown-divider my-0"></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-left me-2"></i>Sign out</button></form>
            </div>
        </div>
        @endif
        @if(!empty($user->parent_user_id))
        <span class="plan-badge active" style="background:#e3f2fd;color:#1565c0;">{{ ucfirst($user->team_role ?? 'agent') }}</span>
        @endif
    </div>
</div>

<!-- PAGE CONTENT -->
<div id="fuContent" style="@yield('page_content_style')">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show rounded-3 border-0 mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 mb-3"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($errors->any())<div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 mb-3"><i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @yield('content')
</div>

<div id="fuToast"></div>
@stack('modals')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fuOpenSidebar(){ document.getElementById('fuSidebar').classList.add('show'); document.getElementById('sidebarOverlay').classList.add('show'); }
function fuCloseSidebar(){ document.getElementById('fuSidebar').classList.remove('show'); document.getElementById('sidebarOverlay').classList.remove('show'); }
function fuToggleSidebar(){
    const s=document.getElementById('fuSidebar');
    const icon=document.getElementById('sidebarCollapseIcon');
    const collapsed=s.classList.toggle('collapsed');
    icon.className=collapsed?'bi bi-chevron-right':'bi bi-chevron-left';
    localStorage.setItem('fuSidebarCollapsed',collapsed?'1':'0');
}
document.addEventListener('DOMContentLoaded',function(){
    if(localStorage.getItem('fuSidebarCollapsed')==='1'){
        document.getElementById('fuSidebar').classList.add('collapsed');
        document.getElementById('sidebarCollapseIcon').className='bi bi-chevron-right';
    }
});
function showToast(msg,type){ const t=document.getElementById('fuToast'); t.textContent=msg; t.style.background=type==='error'?'#e53935':type==='success'?'#25d366':'#1a1a2e'; t.style.display='block'; t.style.opacity='1'; setTimeout(()=>{ t.style.opacity='0'; setTimeout(()=>t.style.display='none',300); },3000); }
function showNotif(msg,type){ showToast(msg,type); }
function fuLoadNotifs(){ fetch('/notifications',{credentials:'same-origin'}).then(r=>r.json()).then(data=>{ const list=document.getElementById('notifList'); const notifs=data.notifications||[]; const unread=notifs.filter(n=>!n.is_read).length; const badge=document.getElementById('notifBadge'); if(unread>0){badge.style.display='flex';badge.textContent=unread>9?'9+':unread;}else badge.style.display='none'; if(!notifs.length){list.innerHTML='<div class="text-center text-muted py-4" style="font-size:13px;">No notifications yet.</div>';return;} const icons={message:'💬',campaign:'📢',template:'📝',billing:'💳',team:'👥',system:'🔔'}; list.innerHTML=notifs.map(n=>`<div onclick="fuMarkRead(${n.id},this)" style="padding:12px 14px;border-bottom:1px solid #f5f5f5;cursor:pointer;background:${n.is_read?'#fff':'#f0fdf4'};"><div class="d-flex gap-2 align-items-start"><span style="font-size:17px;">${icons[n.type]||'🔔'}</span><div style="flex:1;"><div style="font-size:13px;font-weight:${n.is_read?'500':'700'};color:#1a1a2e;">${n.title||''}</div><div style="font-size:12px;color:#666;margin-top:1px;">${n.message||''}</div></div>${!n.is_read?'<span style="width:8px;height:8px;background:#25d366;border-radius:50%;flex-shrink:0;margin-top:4px;display:block;"></span>':''}</div></div>`).join(''); }).catch(()=>{ document.getElementById('notifList').innerHTML='<div class="text-center text-muted py-4">Could not load.</div>'; }); }
function fuMarkRead(id,el){ fetch('/notifications/'+id+'/read',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},credentials:'same-origin'}).then(()=>{el.style.background='#fff';fuLoadNotifs();}); }
function fuMarkAllRead(){ fetch('/notifications/read-all',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},credentials:'same-origin'}).then(()=>fuLoadNotifs()); }
fetch('/notifications/count',{credentials:'same-origin'}).then(r=>r.json()).then(data=>{ const badge=document.getElementById('notifBadge'); if(data.count>0){badge.style.display='flex';badge.textContent=data.count>9?'9+':data.count;} }).catch(()=>{});
function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
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
        okBtn.onclick = function(){ overlay.style.display = 'none'; resolve(true); };
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
        cancelBtn.onclick = function(){ overlay.style.display = 'none'; resolve(false); };
        btns.appendChild(cancelBtn);

        const okBtn = document.createElement('button');
        okBtn.textContent = opts.confirmLabel || 'Confirm';
        okBtn.style.cssText = 'flex:1;padding:11px;border:none;border-radius:9px;color:#fff;font-weight:700;cursor:pointer;font-family:inherit;font-size:13.5px;background:' + (opts.danger ? '#e53935' : '#25d366') + ';';
        okBtn.onclick = function(){ overlay.style.display = 'none'; resolve(true); };
        btns.appendChild(okBtn);

        overlay.style.display = 'flex';
    });
}
</script>

@stack('scripts')
</body>
</html>