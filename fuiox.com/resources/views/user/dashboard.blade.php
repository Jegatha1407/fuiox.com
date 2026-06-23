@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ══════════════════════════════════════
   DASHBOARD LAYOUT — pure CSS grid,
   no reliance on Bootstrap col widths
══════════════════════════════════════ */

.dash-wrap {
    padding: 20px;
    max-width: 1400px;
}

/* ── 3-col stat row ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

/* ── 2-col equal row ── */
.two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

/* ── full-width row ── */
.full-row {
    margin-bottom: 20px;
}

/* ── Stat card ── */
.stat-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e9f0;
    border-left: 4px solid #25d366;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .2s, transform .2s;
}
.stat-card:hover       { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-2px); }
.stat-card.blue        { border-left-color: #1976d2; }
.stat-card.orange      { border-left-color: #f57c00; }
.stat-icon             { font-size: 2rem; line-height: 1; flex-shrink: 0; }
.stat-info .s-label    { font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 2px; }
.stat-info .s-value    { font-size: 28px; font-weight: 700; color: #1a1a2e; line-height: 1.1; }
.stat-info .s-sub      { font-size: 12px; color: #bbb; margin-top: 3px; }

/* ── Card box ── */
.card-box {
    background: #fff;
    border-radius: 14px;
    padding: 22px;
    border: 1px solid #e5e9f0;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    height: 100%;
}
.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 16px;
}

/* ── Action cards 2-col grid ── */
.action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.action-card {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 14px 12px;
    background: #f6f8fa;
    border: 1px solid #e5e9f0;
    border-radius: 12px;
    text-decoration: none;
    color: #1a1a2e;
    transition: background .15s, border-color .15s, transform .15s, box-shadow .15s;
}
.action-card:hover {
    background: #e8f5e9;
    border-color: #25d366;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,211,102,.14);
    color: #1a1a2e;
}
.ac-icon  { font-size: 22px; line-height: 1; }
.ac-title { font-size: 13px; font-weight: 700; }
.ac-desc  { font-size: 11px; color: #999; }

/* ── API Status ── */
.status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 13px;
}
.status-row:last-of-type { border-bottom: none; }
.status-key { color: #888; }
.status-val {
    color: #1a1a2e;
    font-weight: 500;
    font-size: 12px;
    text-align: right;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Charts ── */
.chart-wrap {
    position: relative;
    height: 220px;
    width: 100%;
    overflow: hidden; /* prevent canvas bleed */
}
.chart-wrap canvas {
    max-width: 100% !important;
    display: block;
}

/* ── Recent conversations ── */
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
    transition: opacity .15s;
}
.activity-item:last-child { border-bottom: none; }
.activity-item:hover      { opacity: .75; }
.activity-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #2e7d32;
    font-size: 14px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.activity-info { flex: 1; overflow: hidden; }
.activity-name { font-size: 14px; font-weight: 600; color: #1a1a2e; }
.activity-msg  { font-size: 13px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.activity-time { font-size: 12px; color: #aaa; flex-shrink: 0; }

/* ── Misc ── */
.btn-wa {
    background: #25d366; color: #fff; border: none;
    border-radius: 8px; padding: 9px 18px;
    font-size: 13px; font-weight: 600;
    text-decoration: none; display: inline-block;
    transition: background .15s;
}
.btn-wa:hover { background: #1ebe57; color: #fff; }
.unread-badge {
    background: #25d366; color: #fff;
    font-size: 11px; font-weight: 700;
    padding: 2px 7px; border-radius: 10px; margin-left: 6px;
}

/* ════════════════════════════
   RESPONSIVE
════════════════════════════ */

/* Tablet: stack 2-col rows */
@media (max-width: 900px) {
    .two-col-grid { grid-template-columns: 1fr; }
    .chart-wrap   { height: 200px; }
}

/* Mobile */
@media (max-width: 600px) {
    .dash-wrap    { padding: 12px; }

    /* Stats: 3-col compact */
    .stats-grid   { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .stat-card    { padding: 12px 8px; flex-direction: column; align-items: flex-start; gap: 4px; }
    .stat-icon    { font-size: 1.4rem; }
    .stat-info .s-value { font-size: 20px; }
    .stat-info .s-sub   { display: none; }

    /* Action grid stays 2-col */
    .action-grid  { gap: 8px; }
    .ac-title     { font-size: 12px; }

    /* Charts smaller, NO overflow */
    .chart-wrap   { height: 170px; }
    .card-box     { padding: 14px; }
    .section-title{ font-size: 14px; margin-bottom: 12px; }
}

/* Very small phones */
@media (max-width: 380px) {
    .stats-grid   { grid-template-columns: 1fr; }
    .chart-wrap   { height: 150px; }
}
</style>
@endpush

@section('content')
<div class="dash-wrap">

    {{-- ══ STAT CARDS ══ --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="s-label">Total Contacts</div>
                <div class="s-value">{{ $stats['total_messages'] ?? 0 }}</div>
                <div class="s-sub">WhatsApp contacts</div>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon">📩</div>
            <div class="stat-info">
                <div class="s-label">Pending</div>
                <div class="s-value">{{ $stats['pending_messages'] ?? 0 }}</div>
                <div class="s-sub">Unread messages</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="s-label">Meta Wallet</div>
                <div class="s-value" id="walletBalance">—</div>
                <div class="s-sub" id="walletCurrency">Fetching…</div>
            </div>
        </div>
    </div>

    {{-- ══ QUICK ACTIONS + API STATUS ══ --}}
    <div class="two-col-grid">
        <div class="card-box">
            <div class="section-title">⚡ Quick Actions</div>
            <div class="action-grid">
                <a href="{{ route('chat') }}" class="action-card">
                    <div class="ac-icon">💬</div>
                    <div class="ac-title">Chat</div>
                    <div class="ac-desc">View messages</div>
                </a>
                <a href="{{ route('automation') }}" class="action-card">
                    <div class="ac-icon">🤖</div>
                    <div class="ac-title">Automation</div>
                    <div class="ac-desc">Auto-replies</div>
                </a>
                <a href="{{ route('bulk.template') }}" class="action-card">
                    <div class="ac-icon">📤</div>
                    <div class="ac-title">Bulk Send</div>
                    <div class="ac-desc">Mass messages</div>
                </a>
                <a href="{{ route('campaigns') }}" class="action-card">
                    <div class="ac-icon">📢</div>
                    <div class="ac-title">Campaigns</div>
                    <div class="ac-desc">Manage campaigns</div>
                </a>
                <a href="{{ route('contacts') }}" class="action-card">
                    <div class="ac-icon">👥</div>
                    <div class="ac-title">Contacts</div>
                    <div class="ac-desc">Manage contacts</div>
                </a>
                <a href="{{ route('reports') }}" class="action-card">
                    <div class="ac-icon">📈</div>
                    <div class="ac-title">Reports</div>
                    <div class="ac-desc">Analytics</div>
                </a>
            </div>
        </div>

        <div class="card-box">
            <div class="section-title">🔗 API Connection</div>
            <div class="status-row">
                <span class="status-key">Status</span>
                @if($user->phone_number_id && $user->access_token)
                    <span style="background:#e8f5e9;color:#2e7d32;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;">✓ Connected</span>
                @else
                    <span style="background:#fdecea;color:#c62828;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;">✗ Not connected</span>
                @endif
            </div>
            <div class="status-row">
                <span class="status-key">Organisation</span>
                <span class="status-val">{{ $user->organisation }}</span>
            </div>
            <div class="status-row">
                <span class="status-key">Phone ID</span>
                <span class="status-val">{{ $user->phone_number_id ?? '—' }}</span>
            </div>
            <div class="status-row">
                <span class="status-key">Access Token</span>
                <span class="status-val">{{ $user->access_token ? substr($user->access_token,0,16).'...' : '—' }}</span>
            </div>
            @if(!$user->phone_number_id)
            <div style="margin-top:14px;">
                <a href="{{ route('settings') }}" class="btn-wa">⚙️ Connect WhatsApp</a>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ CHARTS ══ --}}
    <div class="two-col-grid">
        <div class="card-box">
            <div class="section-title">📊 Messages This Week</div>
            <div class="chart-wrap"><canvas id="msgChart"></canvas></div>
        </div>
        <div class="card-box">
            <div class="section-title">📱 Message Types</div>
            <div class="chart-wrap"><canvas id="typeChart"></canvas></div>
        </div>
    </div>

    {{-- ══ RECENT CONVERSATIONS ══ --}}
    <div class="full-row">
        <div class="card-box">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                <div class="section-title" style="margin-bottom:0;">💬 Recent Conversations</div>
                <a href="{{ route('chat') }}" style="font-size:13px;color:#25d366;text-decoration:none;font-weight:600;">View all →</a>
            </div>
            <div id="recentChats">
                <div style="text-align:center;color:#aaa;padding:1rem;">Loading…</div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* Wallet */
fetch('/chat/wallet-balance').then(r=>r.json()).then(d=>{
    document.getElementById('walletBalance').textContent = d.balance!=null ? parseFloat(d.balance).toFixed(2) : '—';
    document.getElementById('walletCurrency').textContent = d.balance!=null ? (d.currency||'USD')+' Credit' : 'Not available';
}).catch(()=>{
    document.getElementById('walletBalance').textContent='—';
    document.getElementById('walletCurrency').textContent='Could not fetch';
});

/* Recent chats */
fetch('/chat/users',{credentials:'same-origin'}).then(r=>r.json()).then(data=>{
    const box=document.getElementById('recentChats');
    if(!data.length){box.innerHTML='<div style="text-align:center;color:#aaa;padding:1rem;">💬 No conversations yet.</div>';return;}
    box.innerHTML=data.slice(0,8).map(u=>{
        const name=u.name||u.display_phone||u.phone;
        const badge=u.unread>0?`<span class="unread-badge">${u.unread}</span>`:'';
        return`<div class="activity-item" onclick="window.location='{{ route('chat') }}'">
            <div class="activity-avatar">${escHtml(name.slice(0,2).toUpperCase())}</div>
            <div class="activity-info">
                <div class="activity-name">${escHtml(name)}${badge}</div>
                <div class="activity-msg">${escHtml(u.last_message||'')}</div>
            </div>
            <div class="activity-time">${u.last_time||''}</div>
        </div>`;
    }).join('');
}).catch(()=>{
    document.getElementById('recentChats').innerHTML='<div style="text-align:center;color:#aaa;padding:1rem;">Could not load.</div>';
});

/* Charts */
new Chart(document.getElementById('msgChart'),{
    type:'bar',
    data:{
        labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets:[{label:'Messages',data:[12,19,8,24,15,28,22],backgroundColor:'rgba(37,211,102,0.75)',borderRadius:6}]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        clip: true,
        plugins:{legend:{display:false}},
        scales:{
            x:{grid:{display:false}, ticks:{font:{size:11}}, border:{display:false}},
            y:{beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:11}}, border:{display:false}}
        },
        layout:{ padding: 0 }
    }
});
new Chart(document.getElementById('typeChart'),{
    type:'doughnut',
    data:{
        labels:['Text','Image','Audio','Video','Doc'],
        datasets:[{data:[60,15,10,8,7],backgroundColor:['#25d366','#1976d2','#f57c00','#7b1fa2','#e53935'],borderWidth:2}]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        clip: true,
        plugins:{
            legend:{
                position:'bottom',
                labels:{boxWidth:10, font:{size:11}, padding:8}
            }
        },
        layout:{ padding: 0 }
    }
});
</script>
@endpush