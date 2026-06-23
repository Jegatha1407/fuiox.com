@extends('layouts.app')
@section('title', 'Channels')
@section('page_title', 'Connected Channels')

@push('styles')
<style>
.channel-card {
    background: #fff;
    border: 1.5px solid #e5e9f0;
    border-radius: 16px;
    padding: 24px;
    transition: .2s;
    height: 100%;
}
.channel-card:hover { border-color: #25d366; box-shadow: 0 4px 20px rgba(37,211,102,0.1); }
.channel-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; margin-bottom: 14px;
}
.channel-name { font-size: 16px; font-weight: 800; color: #1a1a2e; margin-bottom: 4px; }
.channel-desc { font-size: 13px; color: #888; margin-bottom: 16px; line-height: 1.5; }
.channel-status { font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 16px; }
.channel-status.connected { background: #e8f5e9; color: #2e7d32; }
.channel-status.disconnected { background: #f5f5f5; color: #999; }
.btn-connect { width: 100%; padding: 10px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: inherit; transition: .2s; }
.btn-connect.connect { background: #25d366; color: #fff; }
.btn-connect.connect:hover { background: #1fba58; }
.btn-connect.disconnect { background: #fdecea; color: #c62828; }
.btn-connect.disconnect:hover { background: #ffcdd2; }
.fu-modal-overlay { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; backdrop-filter:blur(4px); }
.fu-modal-overlay.show { display:flex; }
.fu-modal { background:#fff; border-radius:20px; padding:28px; max-width:440px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.fu-modal-title { font-size:18px; font-weight:800; color:#1a1a2e; margin-bottom:6px; }
.fu-modal-sub { font-size:13px; color:#888; margin-bottom:20px; }
.fu-label { font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; display:block; }
.fu-inp { width:100%; padding:11px 14px; border:1.5px solid #e5e9f0; border-radius:10px; font-size:14px; outline:none; font-family:inherit; transition:.2s; }
.fu-inp:focus { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.1); }
.fu-alert-sm { font-size:12px; padding:10px 12px; border-radius:8px; margin-bottom:12px; display:none; }
.fu-alert-sm.err { background:#fdecea; color:#c62828; border-left:3px solid #e53935; }
.fu-alert-sm.suc { background:#e8f5e9; color:#2e7d32; border-left:3px solid #25d366; }
.page-option { border:1.5px solid #e5e9f0; border-radius:10px; padding:12px 14px; cursor:pointer; transition:.2s; margin-bottom:8px; display:flex; align-items:center; gap:10px; }
.page-option:hover { border-color:#25d366; background:#f9fff9; }
.page-option.selected { border-color:#25d366; background:#e8f5e9; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
    @endif

    <div class="row g-4">

        {{-- WhatsApp --}}
        <div class="col-md-6 col-xl-4">
            <div class="channel-card">
                <div class="channel-icon" style="background:#e8f5e9;">📱</div>
                <div class="channel-name">WhatsApp Business</div>
                <div class="channel-desc">Send and receive WhatsApp messages, run campaigns and automate responses.</div>
                @php $wa = $connections->where('channel','whatsapp')->first(); @endphp
                <div class="channel-status connected"><i class="bi bi-check-circle-fill"></i> Connected</div>
                <div style="font-size:12px;color:#888;">{{ auth()->user()->mobile ?? 'Connected' }}</div>
            </div>
        </div>

        {{-- Messenger --}}
        <div class="col-md-6 col-xl-4">
            <div class="channel-card">
                <div class="channel-icon" style="background:#e3f2fd;">💬</div>
                <div class="channel-name">Facebook Messenger</div>
                <div class="channel-desc">Manage Facebook Page messages and reply to customers from Fuiox.</div>
                @php $messenger = $connections->where('channel','messenger')->where('is_active',1)->first(); @endphp
                @if($messenger)
                <div class="channel-status connected"><i class="bi bi-check-circle-fill"></i> Connected</div>
                <div style="font-size:12px;color:#555;margin-bottom:16px;">{{ $messenger->page_name }}</div>
                <button class="btn-connect disconnect" onclick="disconnectChannel('messenger')">Disconnect</button>
                @else
                <div class="channel-status disconnected"><i class="bi bi-x-circle"></i> Not Connected</div>
                <button class="btn-connect connect" onclick="showModal('messenger')"><i class="bi bi-plus-lg me-1"></i> Connect Messenger</button>
                @endif
            </div>
        </div>

        {{-- Instagram --}}
        <div class="col-md-6 col-xl-4">
            <div class="channel-card">
                <div class="channel-icon" style="background:#fce4ec;">📸</div>
                <div class="channel-name">Instagram DMs</div>
                <div class="channel-desc">Receive and reply to Instagram Direct Messages from your business inbox.</div>
                @php $instagram = $connections->where('channel','instagram')->where('is_active',1)->first(); @endphp
                @if($instagram)
                <div class="channel-status connected"><i class="bi bi-check-circle-fill"></i> Connected</div>
                <div style="font-size:12px;color:#555;margin-bottom:16px;">@{{ $instagram->username }}</div>
                <button class="btn-connect disconnect" onclick="disconnectChannel('instagram')">Disconnect</button>
                @else
                <div class="channel-status disconnected"><i class="bi bi-x-circle"></i> Not Connected</div>
                <button class="btn-connect connect" onclick="showModal('instagram')"><i class="bi bi-plus-lg me-1"></i> Connect Instagram</button>
                @endif
            </div>
        </div>

        {{-- Telegram --}}
        <div class="col-md-6 col-xl-4">
            <div class="channel-card">
                <div class="channel-icon" style="background:#e3f2fd;">✈️</div>
                <div class="channel-name">Telegram</div>
                <div class="channel-desc">Connect your Telegram bot to receive and reply to Telegram messages.</div>
                @php $telegram = $connections->where('channel','telegram')->where('is_active',1)->first(); @endphp
                @if($telegram)
                <div class="channel-status connected"><i class="bi bi-check-circle-fill"></i> Connected</div>
                <div style="font-size:12px;color:#555;margin-bottom:16px;">@{{ $telegram->username }}</div>
                <button class="btn-connect disconnect" onclick="disconnectChannel('telegram')">Disconnect</button>
                @else
                <div class="channel-status disconnected"><i class="bi bi-x-circle"></i> Not Connected</div>
                <button class="btn-connect connect" onclick="showModal('telegram')"><i class="bi bi-plus-lg me-1"></i> Connect Telegram</button>
                @endif
            </div>
        </div>



    </div>
</div>

{{-- Messenger Modal --}}
<div class="fu-modal-overlay" id="modalMessenger">
    <div class="fu-modal">
        <div class="fu-modal-title">💬 Connect Facebook Messenger</div>
        <div class="fu-modal-sub">Enter your Facebook Page access token to connect Messenger.</div>
        <div id="alertMessenger" class="fu-alert-sm"></div>
        <div class="mb-3">
            <label class="fu-label" for="messengerToken">Page Access Token *</label>
            <input type="text" id="messengerToken" class="fu-inp" placeholder="EAAxxxxxxxx…" autocomplete="off">
            <div style="font-size:11px;color:#aaa;margin-top:4px;">Get from Meta Business Manager → Pages → Page Access Token</div>
        </div>
        <div id="messengerPages" style="display:none;">
            <div class="fu-label">Select Page</div>
            <div id="messengerPageList"></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="closeModal('messenger')" style="flex:1;padding:11px;border:1.5px solid #e5e5e5;border-radius:10px;background:#f9f9f9;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
            <button id="messengerFetchBtn" onclick="fetchPages('messenger')" style="flex:1;padding:11px;border:none;border-radius:10px;background:#1877f2;color:#fff;font-size:14px;font-weight:700;cursor:pointer;">Fetch Pages</button>
        </div>
    </div>
</div>

{{-- Instagram Modal --}}
<div class="fu-modal-overlay" id="modalInstagram">
    <div class="fu-modal">
        <div class="fu-modal-title">📸 Connect Instagram DMs</div>
        <div class="fu-modal-sub">Enter your Facebook Page access token linked to your Instagram Business account.</div>
        <div id="alertInstagram" class="fu-alert-sm"></div>
        <div class="mb-3">
            <label class="fu-label" for="instagramToken">Page Access Token *</label>
            <input type="text" id="instagramToken" class="fu-inp" placeholder="EAAxxxxxxxx…" autocomplete="off">
            <div style="font-size:11px;color:#aaa;margin-top:4px;">Your Instagram must be linked to a Facebook Page</div>
        </div>
        <div id="instagramPages" style="display:none;">
            <div class="fu-label">Select Facebook Page</div>
            <div id="instagramPageList"></div>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="closeModal('instagram')" style="flex:1;padding:11px;border:1.5px solid #e5e5e5;border-radius:10px;background:#f9f9f9;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
            <button id="instagramFetchBtn" onclick="fetchPages('instagram')" style="flex:1;padding:11px;border:none;border-radius:10px;background:linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff;font-size:14px;font-weight:700;cursor:pointer;">Fetch Pages</button>
        </div>
    </div>
</div>

{{-- Telegram Modal --}}
<div class="fu-modal-overlay" id="modalTelegram">
    <div class="fu-modal">
        <div class="fu-modal-title">✈️ Connect Telegram Bot</div>
        <div class="fu-modal-sub">Create a bot via @BotFather on Telegram and paste the token here.</div>
        <div id="alertTelegram" class="fu-alert-sm"></div>
        <div class="mb-3">
            <label class="fu-label" for="telegramToken">Bot Token *</label>
            <input type="text" id="telegramToken" class="fu-inp" placeholder="1234567890:ABCxxxxxxxx…" autocomplete="off">
            <div style="font-size:11px;color:#aaa;margin-top:4px;">
                Open Telegram → search @BotFather → /newbot → copy token
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button onclick="closeModal('telegram')" style="flex:1;padding:11px;border:1.5px solid #e5e5e5;border-radius:10px;background:#f9f9f9;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
            <button id="telegramConnectBtn" onclick="connectTelegram()" style="flex:1;padding:11px;border:none;border-radius:10px;background:#229ED9;color:#fff;font-size:14px;font-weight:700;cursor:pointer;">Connect Bot</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
let selectedPage = null;

function showModal(channel) {
    document.getElementById('modal' + channel.charAt(0).toUpperCase() + channel.slice(1)).classList.add('show');
}
function closeModal(channel) {
    document.getElementById('modal' + channel.charAt(0).toUpperCase() + channel.slice(1)).classList.remove('show');
}

function showAlert(channel, msg, type) {
    const el = document.getElementById('alert' + channel.charAt(0).toUpperCase() + channel.slice(1));
    el.textContent = msg; el.className = 'fu-alert-sm ' + type; el.style.display = 'block';
}

function fetchPages(channel) {
    const token = document.getElementById(channel + 'Token').value.trim();
    if (!token) { showAlert(channel, 'Please enter access token', 'err'); return; }
    const btn = document.getElementById(channel + 'FetchBtn');
    btn.disabled = true; btn.textContent = 'Fetching…';

    fetch('/channels/pages?access_token=' + encodeURIComponent(token), { credentials: 'same-origin' })
    .then(r => r.json()).then(d => {
        btn.disabled = false; btn.textContent = 'Fetch Pages';
        if (d.error) { showAlert(channel, '❌ ' + d.error, 'err'); return; }
        const pages = d.pages || [];
        if (!pages.length) { showAlert(channel, 'No pages found', 'err'); return; }

        const listId = channel + 'PageList';
        const wrapId = channel + 'Pages';
        document.getElementById(listId).innerHTML = pages.map(p => `
            <div class="page-option" onclick="selectPage(this,'${channel}','${p.id}','${p.access_token || token}')">
                <i class="bi bi-facebook" style="font-size:20px;color:#1877f2;"></i>
                <div>
                    <div style="font-size:14px;font-weight:700;">${escHtml(p.name)}</div>
                    <div style="font-size:12px;color:#888;">${p.id}</div>
                </div>
            </div>
        `).join('');
        document.getElementById(wrapId).style.display = 'block';
    }).catch(() => { btn.disabled = false; btn.textContent = 'Fetch Pages'; showAlert(channel, '❌ Network error', 'err'); });
}

function selectPage(el, channel, pageId, token) {
    document.querySelectorAll('#' + channel + 'PageList .page-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    selectedPage = { pageId, token };

    // Connect
    const btn = document.getElementById(channel + 'FetchBtn');
    btn.textContent = 'Connecting…'; btn.disabled = true;

    fetch('/channels/connect/' + channel, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ access_token: token, page_id: pageId })
    }).then(r => r.json()).then(d => {
        btn.disabled = false; btn.textContent = 'Fetch Pages';
        if (d.error) { showAlert(channel, '❌ ' + d.error, 'err'); return; }
        showAlert(channel, '✅ Connected successfully!', 'suc');
        setTimeout(() => location.reload(), 1200);
    }).catch(() => { btn.disabled = false; showAlert(channel, '❌ Network error', 'err'); });
}

function connectTelegram() {
    const token = document.getElementById('telegramToken').value.trim();
    if (!token) { showAlert('telegram', 'Please enter bot token', 'err'); return; }
    const btn = document.getElementById('telegramConnectBtn');
    btn.disabled = true; btn.textContent = 'Connecting…';

    fetch('/channels/connect/telegram', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ bot_token: token })
    }).then(r => r.json()).then(d => {
        btn.disabled = false; btn.textContent = 'Connect Bot';
        if (d.error) { showAlert('telegram', '❌ ' + d.error, 'err'); return; }
        showAlert('telegram', '✅ Bot connected: @' + d.bot, 'suc');
        setTimeout(() => location.reload(), 1200);
    }).catch(() => { btn.disabled = false; showAlert('telegram', '❌ Network error', 'err'); });
}

function disconnectChannel(channel) {
    if (!confirm('Disconnect ' + channel + '?')) return;
    fetch('/channels/disconnect', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ channel })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
    });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close modal on outside click
document.querySelectorAll('.fu-modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); });
});
</script>
@endpush
