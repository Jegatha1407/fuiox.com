@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports & Analytics')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ════════════════════════════
   REPORTS LAYOUT
════════════════════════════ */
.reports-wrap {
    padding: 20px;
    max-width: 1400px;
}

/* ── 4-col stat row ── */
.stats-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

/* ── 2-col grid ── */
.two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

/* ── full row ── */
.full-row { margin-bottom: 20px; }

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
.stat-card:hover        { box-shadow: 0 4px 16px rgba(0,0,0,.09); transform: translateY(-2px); }
.stat-card.blue         { border-left-color: #1976d2; }
.stat-card.orange       { border-left-color: #f57c00; }
.stat-card.purple       { border-left-color: #7b1fa2; }
.stat-icon              { font-size: 1.8rem; line-height: 1; flex-shrink: 0; }
.stat-info .s-label     { font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 2px; }
.stat-info .s-value     { font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1.1; }
.stat-info .s-sub       { font-size: 12px; color: #bbb; margin-top: 3px; }

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

/* ── Chart wrap ── */
.chart-wrap {
    position: relative;
    height: 220px;
    width: 100%;
    overflow: hidden;
}
.chart-wrap canvas {
    max-width: 100% !important;
    display: block;
}

/* ── Top contacts ── */
.top-contact {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid #f5f5f5;
}
.top-contact:last-child { border-bottom: none; }
.rank-badge {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: #25d366;
    color: #fff;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.top-name  { font-size: 13px; font-weight: 600; color: #1a1a2e; }
.top-phone { font-size: 11px; color: #888; }
.top-count { font-size: 13px; font-weight: 700; color: #25d366; flex-shrink: 0; }

/* ── Chart range select ── */
.chart-select {
    padding: 6px 10px;
    border: 1px solid #e5e9f0;
    border-radius: 8px;
    font-size: 12px;
    outline: none;
    color: #555;
    background: #f6f8fa;
    cursor: pointer;
}
.chart-select:focus { border-color: #25d366; }

/* ── Data table ── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table thead th {
    text-align: left;
    padding: 10px 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #999;
    border-bottom: 2px solid #f0f0f0;
    white-space: nowrap;
}
.data-table tbody td {
    padding: 11px 12px;
    border-bottom: 1px solid #f8f8f8;
    color: #1a1a2e;
    vertical-align: middle;
}
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover td { background: #fafbfc; }

.progress-bar-wrap {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.progress-track {
    background: #f0f0f0;
    border-radius: 4px;
    height: 6px;
    width: 70px;
}
.progress-fill {
    background: #25d366;
    height: 6px;
    border-radius: 4px;
}

/* ════════════════════════════
   RESPONSIVE
════════════════════════════ */
@media (max-width: 1100px) {
    .stats-grid-4 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .two-col-grid { grid-template-columns: 1fr; }
    .chart-wrap   { height: 200px; }
}
@media (max-width: 600px) {
    .reports-wrap  { padding: 12px; }
    .stats-grid-4  { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .stat-card     { padding: 12px 10px; flex-direction: column; align-items: flex-start; gap: 4px; }
    .stat-icon     { font-size: 1.4rem; }
    .stat-info .s-value { font-size: 20px; }
    .stat-info .s-sub   { display: none; }
    .card-box      { padding: 14px; }
    .section-title { font-size: 14px; margin-bottom: 12px; }
    .chart-wrap    { height: 170px; }
    .data-table    { font-size: 12px; }
    .data-table thead th,
    .data-table tbody td { padding: 8px 8px; }
}
@media (max-width: 380px) {
    .stats-grid-4  { grid-template-columns: 1fr; }
    .chart-wrap    { height: 150px; }
}
</style>
@endpush

@section('content')
<div class="reports-wrap">

    {{-- ══ STAT CARDS ══ --}}
    <div class="stats-grid-4">
        <div class="stat-card">
            <div class="stat-icon">📤</div>
            <div class="stat-info">
                <div class="s-label">Messages Sent</div>
                <div class="s-value">{{ $stats['total_sent'] ?? '—' }}</div>
                <div class="s-sub">Total outgoing</div>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon">📥</div>
            <div class="stat-info">
                <div class="s-label">Messages Received</div>
                <div class="s-value">{{ $stats['total_received'] ?? '—' }}</div>
                <div class="s-sub">Total incoming</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <div class="s-label">Total Contacts</div>
                <div class="s-value">{{ $stats['total_contacts'] ?? '—' }}</div>
                <div class="s-sub">Unique numbers</div>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">💬</div>
            <div class="stat-info">
                <div class="s-label">Response Rate</div>
                <div class="s-value">{{ $stats['response_rate'] ?? '—' }}%</div>
                <div class="s-sub">Contacts who replied</div>
            </div>
        </div>
    </div>

    {{-- ══ CHARTS ROW 1 ══ --}}
    <div class="two-col-grid">
        <div class="card-box">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                <div class="section-title" style="margin-bottom:0;">Messages Over Time</div>
                <select id="chartRange" class="chart-select" onchange="updateChart()">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                </select>
            </div>
            <div class="chart-wrap"><canvas id="reportChart"></canvas></div>
        </div>
        <div class="card-box">
            <div class="section-title">Media Types Sent</div>
            <div class="chart-wrap"><canvas id="mediaChart"></canvas></div>
        </div>
    </div>

    {{-- ══ CHARTS ROW 2 ══ --}}
    <div class="two-col-grid">
        <div class="card-box">
            <div class="section-title">Hourly Activity</div>
            <div class="chart-wrap"><canvas id="hourlyChart"></canvas></div>
        </div>
        <div class="card-box">
            <div class="section-title">Top 10 Contacts</div>
            <div style="max-height:240px;overflow-y:auto;">
                @forelse($topContacts as $i => $c)
                <div class="top-contact">
                    <div class="rank-badge">{{ $i + 1 }}</div>
                    <div style="flex:1;overflow:hidden;">
                        <div class="top-name">{{ $c->name ?? $c->phone }}</div>
                        <div class="top-phone">{{ $c->phone }}</div>
                    </div>
                    <div class="top-count">{{ $c->total }}</div>
                </div>
                @empty
                <div style="padding:1.5rem;text-align:center;color:#aaa;font-size:13px;">No data yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══ CAMPAIGN PERFORMANCE ══ --}}
    <div class="full-row">
        <div class="card-box">
            <div class="section-title">Recent Campaign Performance</div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Total</th>
                            <th>Sent</th>
                            <th>Failed</th>
                            <th>Success Rate</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $c)
                        @php $rate = $c->total > 0 ? round(($c->sent / $c->total) * 100) : 0; @endphp
                        <tr>
                            <td><strong>{{ $c->name }}</strong></td>
                            <td>{{ $c->total }}</td>
                            <td style="color:#2e7d32;font-weight:600;">{{ $c->sent }}</td>
                            <td style="color:#c62828;">{{ $c->failed }}</td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:{{ $rate }}%;"></div>
                                    </div>
                                    <span style="font-size:12px;color:#555;">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td style="font-size:12px;color:#aaa;white-space:nowrap;">
                                {{ $c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('d M Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:#aaa;padding:1.5rem;">No campaigns yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const last7      = @json($last7);
const last30     = @json($last30);
const hourly     = @json($hourly);
const mediaTypes = @json($mediaTypes);

const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    clip: true,
    layout: { padding: 0 }
};

let reportChart;

function renderReportChart(data) {
    if (reportChart) reportChart.destroy();
    reportChart = new Chart(document.getElementById('reportChart'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.date),
            datasets: [
                { label: 'Sent',     data: data.map(d => d.sent),     backgroundColor: 'rgba(37,211,102,0.75)', borderRadius: 4 },
                { label: 'Received', data: data.map(d => d.received), backgroundColor: 'rgba(25,118,210,0.7)',  borderRadius: 4 }
            ]
        },
        options: {
            ...chartDefaults,
            plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } }, border: { display: false } }
            }
        }
    });
}

function updateChart() {
    renderReportChart(document.getElementById('chartRange').value === '30' ? last30 : last7);
}

renderReportChart(last7);

/* Media doughnut */
const mediaLabels = Object.keys(mediaTypes);
const mediaValues = Object.values(mediaTypes);
if (mediaLabels.length) {
    new Chart(document.getElementById('mediaChart'), {
        type: 'doughnut',
        data: {
            labels: mediaLabels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
            datasets: [{ data: mediaValues, backgroundColor: ['#25d366','#1976d2','#f57c00','#7b1fa2','#e53935'], borderWidth: 2 }]
        },
        options: {
            ...chartDefaults,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } } }
        }
    });
} else {
    document.getElementById('mediaChart').parentElement.innerHTML =
        '<div style="height:100%;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:13px;">No media messages yet</div>';
}

/* Hourly bar */
new Chart(document.getElementById('hourlyChart'), {
    type: 'bar',
    data: {
        labels: hourly.map(h => h.hour),
        datasets: [{ label: 'Messages', data: hourly.map(h => h.total), backgroundColor: 'rgba(37,211,102,0.65)', borderRadius: 4 }]
    },
    options: {
        ...chartDefaults,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } }, border: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } }, border: { display: false } }
        }
    }
});
</script>
@endpush