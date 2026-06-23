<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Template — Fuiox</title>
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
        .template-preview{border:1px solid #e6f2ea;border-radius:16px;padding:1rem;background:#f8fcf8;}
        .template-preview pre{white-space:pre-wrap;font-size:0.95rem;line-height:1.6;color:#334431;}
        .contact-list{max-height:480px;overflow-y:auto;border:1px solid #e6f2ea;border-radius:16px;background:#fff;padding:1rem;}
        .contact-item{display:flex;align-items:center;gap:0.8rem;padding:0.8rem 0.25rem;border-bottom:1px solid #f1f6f1;}
        .contact-item:last-child{border-bottom:none;}
        .contact-item label{flex:1;cursor:pointer;color:#1f3e25;font-size:0.95rem;}
        .contact-phone{color:#556b55;font-size:0.85rem;}
        .btn-primary{background:#00c853;color:#fff;border:none;padding:0.85rem 1rem;border-radius:12px;cursor:pointer;font-weight:700;transition:transform 0.15s;}
        .btn-primary:hover{transform:translateY(-1px);}
        .btn-secondary{background:#eef7ee;color:#0a2f0d;border:none;padding:0.65rem 0.9rem;border-radius:10px;cursor:pointer;font-weight:700;}
        .alert-suc{background:#e8f5e9;color:#2e7d32;font-size:0.9rem;padding:0.9rem 1rem;border-radius:12px;margin-bottom:1rem;border-left:4px solid #00c853;}
        .alert-err{background:#ffebee;color:#c62828;font-size:0.9rem;padding:0.9rem 1rem;border-radius:12px;margin-bottom:1rem;border-left:4px solid #c62828;}
        .results{margin-top:1rem;padding:1rem;border:1px solid #dcecd5;border-radius:16px;background:#fbfff8;}
        .result-item{display:flex;justify-content:space-between;gap:1rem;padding:0.65rem 0;border-bottom:1px solid #eef4ed;}
        .result-item:last-child{border-bottom:none;}
        .status-sent{color:#2e7d32;font-weight:700;}
        .status-failed{color:#c62828;font-weight:700;}
        .note{font-size:0.85rem;color:#60745f;}
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
        <h1>Send Template</h1>
        <span class="date">{{ now()->format('D, d M Y') }}</span>
    </div>

    @if($errors->any())
        <div class="alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
        <div class="alert-suc">{{ session('success') }}</div>
    @endif

    <div class="grid">
        <div>
            <div class="card">
                <div class="section-title">Template preview</div>
                <div class="template-preview">
                    <div class="template-name">{{ $template->name }}</div>
                    <pre>{{ $template->body }}</pre>
                   <div class="note">
    Variables are replaced for each contact:
    <strong>@{{name}}</strong>,
    <strong>@{{phone}}</strong>.
</div>
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="section-title">Contacts</div>
                <form method="POST" action="{{ route('templates.send.post') }}">
                    @csrf
                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                    <div class="contact-list">
                        @forelse($contacts as $contact)
                            <div class="contact-item">
                                <input id="contact_{{ $contact->id }}" type="checkbox" name="contact_phones[]" value="{{ $contact->phone }}" style="width:18px;height:18px;" />
                                <label for="contact_{{ $contact->id }}">
                                    <div>{{ $contact->name ?: 'Contact' }}</div>
                                    <div class="contact-phone">{{ $contact->phone }}</div>
                                </label>
                            </div>
                        @empty
                            <div class="note">No contacts available. Add contacts to your account before sending templates.</div>
                        @endforelse
                    </div>
                    <button class="btn-primary" type="submit" style="width:100%;margin-top:1rem;">Send Template</button>
                </form>
            </div>
        </div>
    </div>

    @if(session('sendResults'))
        <div class="results">
            <div class="section-title">Send results</div>
            @foreach(session('sendResults') as $result)
                <div class="result-item">
                    <span>{{ $result['phone'] }}</span>
                    <span class="status-{{ $result['status'] }}">{{ ucfirst($result['status']) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
</body>
</html>
