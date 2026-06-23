<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Templates — Fuiox</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:#f4faf6;display:flex;min-height:100vh;}
        .sidebar{width:230px;background:#0a1a0f;min-height:100vh;padding:1.5rem 1rem;position:fixed;top:0;left:-230px;transition:all 0.3s ease;z-index:1000;}
        .sidebar-trigger{position:fixed;left:0;top:0;width:20px;height:100vh;z-index:1100;}
        .sidebar-trigger:hover + .sidebar,.sidebar:hover{left:0;}
        .logo{font-size:1.2rem;font-weight:800;color:#fff;margin-bottom:0.25rem;padding:0 0.5rem;}
        .logo span{color:#00e676;}
        .org{font-size:0.75rem;color:rgba(255,255,255,0.35);padding:0 0.5rem;margin-bottom:2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .nav a{display:flex;align-items:center;gap:0.6rem;color:rgba(255,255,255,0.6);text-decoration:none;padding:0.7rem 0.75rem;border-radius:10px;margin-bottom:0.25rem;font-size:0.88rem;transition:all 0.2s;}
        .nav a:hover,.nav a.active{background:rgba(0,230,118,0.12);color:#00e676;}
        .sidebar-bottom{position:absolute;bottom:1.5rem;left:1rem;right:1rem;}
        .user-info{background:rgba(255,255,255,0.06);border-radius:12px;padding:0.75rem;margin-bottom:0.75rem;}
        .user-name{color:#fff;font-size:0.85rem;font-weight:600;}
        .user-email{color:rgba(255,255,255,0.35);font-size:0.72rem;margin-top:0.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .btn-logout{width:100%;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);border:none;padding:0.6rem;border-radius:10px;font-size:0.82rem;cursor:pointer;font-family:'Inter',sans-serif;transition:all 0.2s;}
        .btn-logout:hover{background:rgba(229,57,53,0.15);color:#ef9a9a;}
        .main{margin-left:0;padding:2rem;flex:1;}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;}
        .topbar h1{font-size:1.4rem;font-weight:700;color:#0a1a0f;}
        .topbar .date{font-size:0.82rem;color:#aaa;}
        .card{background:#fff;border-radius:20px;padding:1.5rem;box-shadow:0 18px 45px rgba(26,115,54,0.08);margin-bottom:1.5rem;}
        .section-title{font-size:1rem;font-weight:700;color:#0f260f;margin-bottom:0.75rem;}
        .grid{display:grid;gap:1rem;grid-template-columns:1fr 320px;}
        .template-list{display:grid;gap:1rem;}
        .template-item{border:1px solid #e6f2ea;border-radius:16px;padding:1rem;background:#f8fcf8;}
        .template-name{font-weight:700;color:#0a260a;margin-bottom:0.35rem;}
        .template-body{font-size:0.92rem;color:#395c3b;line-height:1.5;white-space:pre-wrap;margin-bottom:0.9rem;}
        .btn-primary{background:#00c853;color:#fff;border:none;padding:0.75rem 1rem;border-radius:12px;cursor:pointer;font-weight:700;transition:transform 0.15s;}
        .btn-primary:hover{transform:translateY(-1px);}
        .btn-secondary{background:#eef7ee;color:#0a2f0d;border:none;padding:0.65rem 0.9rem;border-radius:10px;cursor:pointer;font-weight:700;}
        .input-group{display:flex;flex-direction:column;gap:0.75rem;}
        label{font-size:0.86rem;color:#445c44;font-weight:600;}
        input,textarea{width:100%;padding:0.85rem 1rem;border:1px solid #dfe8df;border-radius:14px;font-size:0.95rem;font-family:'Inter',sans-serif;}
        textarea{min-height:180px;resize:vertical;}
        .note{font-size:0.85rem;color:#60745f;}
        .alert-suc{background:#e8f5e9;color:#2e7d32;font-size:0.9rem;padding:0.9rem 1rem;border-radius:12px;margin-bottom:1rem;border-left:4px solid #00c853;}
    </style>
</head>
<body>
<div class="sidebar-trigger"></div>
<div class="sidebar">
    <div class="logo">Fuiox <span>Technologies</span></div>
    <div class="org">{{ $user->organisation }}</div>
    <nav class="nav">
        <a href="{{ route('dashboard') }}">📊 Dashboard</a>
        <a href="{{ route('setup') }}">⚙️ API Settings</a>
        <a href="https://business.facebook.com/latest/whatsapp_manager/message_templates" target="_blank" class="active">📝 Manage Templates</a>
    </nav>
    <div class="sidebar-bottom">
        <div class="user-info">
            <div class="user-name">{{ $user->name }}</div>
            <div class="user-email">{{ $user->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-logout" type="submit">← Sign out</button>
        </form>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <h1>Manage Templates</h1>
        <span class="date">{{ now()->format('D, d M Y') }}</span>
    </div>

    @if(session('success'))
        <div class="alert-suc">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="section-title">Create new template</div>
        <form method="POST" action="{{ route('templates.store') }}">
            @csrf
            <div class="input-group">
                <label for="name">Template name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            </div>
            <div class="input-group">
                <label for="body">Message body</label>
                <textarea id="body" name="body" required>{{ old('body') }}</textarea>
            </div>
          <div class="note">
    Use variables like <strong>@{{name}}</strong> and <strong>@{{phone}}</strong>.
</div>
            <button class="btn-primary" type="submit">Save Template</button>
        </form>
    </div>

    <div class="card">
        <div class="section-title">Your templates</div>
        @if($templates->isEmpty())
            <p class="note">No templates created yet. Add one to start sending messages.</p>
        @else
            <div class="template-list">
                @foreach($templates as $template)
                    <div class="template-item">
                        <div class="template-name">{{ $template->name }}</div>
                        <div class="template-body">{{ $template->body }}</div>
                        <a href="{{ route('templates.send', ['id' => $template->id]) }}" class="btn-secondary">Use Template</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <div class="section-title">Meta Business templates</div>
        @if(!$user->phone_number_id || !$user->access_token)
            <p class="note">Connect WhatsApp API first in API Settings to load templates from your Meta Business account.</p>
        @else
            @if($businessLink)
                <p class="note">Manage your official WhatsApp templates directly in Meta Business.</p>
                <a class="btn-secondary" href="{{ $businessLink }}" target="_blank">Open Meta Template Manager</a>
            @endif
            @if(!empty($metaTemplates) && count($metaTemplates) > 0)
                <div class="template-list" style="margin-top:1rem;">
                    @foreach($metaTemplates as $meta)
                        <div class="template-item">
                            <div class="template-name">{{ $meta['name'] }} ({{ $meta['language'] }})</div>
                            <div class="template-body">{{ $meta['preview'] ?: 'No preview available' }}</div>
                            <div class="note">Status: {{ ucfirst($meta['status']) }} · Category: {{ ucfirst($meta['category']) }} · Parameters: {{ $meta['parameter_count'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="note">No templates were found in Meta Business for this account.</p>
            @endif
        @endif
    </div>
</div>
</body>
</html>
