<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Agent Dashboard') — Fuiox</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @stack('styles')
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:#f6f8fa;display:flex;height:100vh;overflow:hidden;}

        /* SIDEBAR */
        .sidebar{width:240px;min-width:240px;background:#1a1a2e;display:flex;flex-direction:column;height:100vh;flex-shrink:0;position:relative;transition:width 0.25s ease;overflow:visible;}
        .sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,0.08);overflow:hidden;}
        .sidebar-logo h2{font-size:18px;font-weight:800;color:#fff;white-space:nowrap;}
        .sidebar-logo h2 span{color:#25d366;}
        .sidebar-logo p{font-size:11px;color:rgba(255,255,255,0.4);margin-top:2px;white-space:nowrap;}
        .nav{flex:1;padding:12px 0;overflow-y:auto;overflow-x:hidden;}
        .nav-section{font-size:10px;font-weight:700;color:rgba(255,255,255,0.25);text-transform:uppercase;letter-spacing:1px;padding:16px 20px 4px;white-space:nowrap;overflow:hidden;}
        .nav a{display:flex;align-items:center;gap:12px;color:rgba(255,255,255,0.6);text-decoration:none;padding:11px 20px;font-size:14px;font-weight:500;border-left:3px solid transparent;transition:0.15s;white-space:nowrap;overflow:hidden;}
        .nav a:hover,.nav a.active{background:rgba(37,211,102,0.12);color:#25d366;border-left-color:#25d366;}
        .nav a .icon{font-size:18px;width:20px;text-align:center;flex-shrink:0;}
        .nav a .nav-text{overflow:hidden;}
        .sidebar-bottom{padding:16px;border-top:1px solid rgba(255,255,255,0.08);margin-top:auto;overflow:hidden;}
        .user-card{background:rgba(255,255,255,0.06);border-radius:10px;padding:12px;margin-bottom:10px;overflow:hidden;}
        .user-card .name{font-size:13px;font-weight:600;color:#fff;white-space:nowrap;}
        .user-card .email{font-size:11px;color:rgba(255,255,255,0.4);margin-top:2px;white-space:nowrap;}
        .btn-signout{width:100%;padding:9px;border:none;border-radius:8px;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);font-size:13px;cursor:pointer;font-family:'Inter',sans-serif;transition:0.2s;white-space:nowrap;overflow:hidden;}
        .btn-signout:hover{background:rgba(229,57,53,0.2);color:#ff6b6b;}

        /* COLLAPSED */
        .sidebar.collapsed{width:64px;min-width:64px;}
        .sidebar.collapsed .sidebar-logo h2,
        .sidebar.collapsed .sidebar-logo p,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .user-card .name,
        .sidebar.collapsed .user-card .email,
        .sidebar.collapsed .signout-text{display:none;}
        .sidebar.collapsed .sidebar-logo{padding:20px 0 16px;display:flex;justify-content:center;}
        .sidebar.collapsed .nav a{justify-content:center;padding:12px 0;}
        .sidebar.collapsed .user-card{padding:8px;text-align:center;}

        /* TOGGLE BTN */
        .sidebar-toggle-btn{
            position:absolute;top:50%;right:-13px;
            transform:translateY(-50%);
            width:26px;height:26px;
            background:#fff;border:1.5px solid #e0e0e0;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            cursor:pointer;z-index:1060;
            box-shadow:0 2px 6px rgba(0,0,0,0.15);
            transition:0.2s;color:#555;font-size:12px;
        }
        .sidebar-toggle-btn:hover{background:#25d366;color:#fff;border-color:#25d366;}

        /* MAIN */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden;transition:flex 0.25s ease;}

        @yield('styles_inline')
    </style>
</head>
<body>

<div class="sidebar" id="agentSidebar">
    <button class="sidebar-toggle-btn" onclick="agentToggleSidebar()" title="Toggle sidebar">
        <i class="bi bi-chevron-left" id="agentSidebarIcon"></i>
    </button>
    <div class="sidebar-logo">
        <h2>Fuiox <span>Technologies</span></h2>
        <p>{{ $user->organisation ?? '' }}</p>
    </div>
    <nav class="nav">
        <div class="nav-section">Main</div>
        <a href="{{ route('agent.dashboard') }}" class="{{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span><span class="nav-text"> Dashboard</span>
        </a>
        <a href="{{ route('chat') }}" class="{{ request()->routeIs('chat') ? 'active' : '' }}">
            <span class="icon">💬</span><span class="nav-text"> Chat</span>
        </a>
        <div class="nav-section">Account</div>
        <a href="{{ route('agent.password') }}" class="{{ request()->routeIs('agent.password') ? 'active' : '' }}">
            <span class="icon">🔒</span><span class="nav-text"> Change Password</span>
        </a>
        @stack('nav_items')
    </nav>
    <div class="sidebar-bottom">
        <div class="user-card">
            <div class="name">{{ $user->name }}</div>
            <div class="email">{{ $user->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-signout" type="submit">←<span class="signout-text"> Sign out</span></button>
        </form>
    </div>
</div>

<div class="main">
    @yield('content')
</div>

@stack('modals')
<script>
function escHtml(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function agentToggleSidebar(){
    const s=document.getElementById('agentSidebar');
    const icon=document.getElementById('agentSidebarIcon');
    const collapsed=s.classList.toggle('collapsed');
    icon.className=collapsed?'bi bi-chevron-right':'bi bi-chevron-left';
    localStorage.setItem('agentSidebarCollapsed',collapsed?'1':'0');
}
document.addEventListener('DOMContentLoaded',function(){
    if(localStorage.getItem('agentSidebarCollapsed')==='1'){
        document.getElementById('agentSidebar').classList.add('collapsed');
        document.getElementById('agentSidebarIcon').className='bi bi-chevron-right';
    }
});
</script>
@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>