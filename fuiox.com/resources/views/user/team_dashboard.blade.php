@extends('layouts.app')

@section('title', $role === 'agent' ? 'My Dashboard' : 'Team Dashboard')
@section('page_title', $role === 'agent' ? 'My Dashboard' : 'Team Dashboard')

@section('page_styles')
.welcome-bar { background: linear-gradient(135deg,#1a1a2e,#2d2d4e); border-radius: 14px; padding: 22px 24px; color: #fff; margin-bottom: 24px; display: flex; align-items: center; gap: 16px; }
.welcome-av { width: 48px; height: 48px; border-radius: 50%; background: #25d366; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; }
.welcome-name { font-size: 18px; font-weight: 700; }
.welcome-sub { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 2px; }
.stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border-left: 4px solid #25d366; display: flex; align-items: center; gap: 14px; }
.stat-card.blue   { border-left-color: #1976d2; }
.stat-card.orange { border-left-color: #f57c00; }
.stat-card.purple { border-left-color: #7b1fa2; }
.stat-icon  { font-size: 26px; }
.stat-label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 24px; font-weight: 800; color: #1a1a2e; }
.conv-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f5f5f5; }
.conv-item:last-child { border-bottom: none; }
.conv-av { width: 36px; height: 36px; border-radius: 50%; background: #e8f5e9; color: #2e7d32; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
@endsection

@section('content')

<div class="welcome-bar">
    <div class="welcome-av">{{ substr($user->name,0,1) }}</div>
    <div>
        <div class="welcome-name">Welcome, {{ $user->name }}! 👋</div>
        <div class="welcome-sub">{{ ucfirst($role) }} · {{ $role==='agent'?'Showing your assigned chats only':'Showing all chats' }}</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div><div class="stat-label">Total Chats</div><div class="stat-value">{{ $stats['totalChats'] }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card blue">
            <div class="stat-icon">📤</div>
            <div><div class="stat-label">Sent Today</div><div class="stat-value">{{ $stats['sentToday'] }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card orange">
            <div class="stat-icon">📥</div>
            <div><div class="stat-label">Received Today</div><div class="stat-value">{{ $stats['receivedToday'] }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card purple">
            <div class="stat-icon">🔔</div>
            <div><div class="stat-label">Unread</div><div class="stat-value">{{ $stats['unread'] }}</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card fu-card">
            <div class="card-header">📊 Messages — Last 7 Days</div>
            <div class="card-body">
                <canvas id="msgChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card fu-card">
            <div class="card-header">🕐 Recent Chats</div>
            <div class="card-body">
                @forelse($recentConvs as $conv)
                <div class="conv-item">
                    <div class="conv-av">{{ strtoupper(substr($conv->name??$conv->wa_id,0,2)) }}</div>
                    <div style="flex:1;overflow:hidden;">
                        <div style="font-size:13px;font-weight:600;color:#1a1a2e;">{{ $conv->name??$conv->wa_id }}</div>
                        <div style="font-size:11px;color:#888;">{{ $conv->wa_id }}</div>
                    </div>
                    <a href="{{ route('chat') }}" class="btn btn-sm btn-outline-success rounded-pill" style="font-size:11px;">Chat</a>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:13px;">No conversations yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartData = @json($chartData);
new Chart(document.getElementById('msgChart'), {
    type: 'bar',
    data: {
        labels: chartData.map(d=>d.date),
        datasets: [
            { label:'Sent',     data:chartData.map(d=>d.sent),     backgroundColor:'#25d366', borderRadius:6 },
            { label:'Received', data:chartData.map(d=>d.received), backgroundColor:'#1976d2', borderRadius:6 },
        ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
});
</script>
@endpush