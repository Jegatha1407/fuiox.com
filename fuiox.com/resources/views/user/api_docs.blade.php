@extends('layouts.app')

@section('title', 'API Documentation')
@section('page_title', 'API Documentation')

@push('styles')
<style>
/* ════════════════════════════
   API DOCS LAYOUT
════════════════════════════ */
.apidocs-wrap {
    padding: 20px;
    max-width: 1100px;
}

/* ── API Key banner ── */
.api-key-banner {
    background: #12131f;
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 20px;
}
.api-key-label {
    font-size: 10px;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}
.api-key-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.api-key-value {
    font-family: monospace;
    font-size: 13px;
    color: #69f0ae;
    flex: 1;
    word-break: break-all;
    min-width: 180px;
}
.api-key-actions { display: flex; gap: 6px; flex-shrink: 0; }
.btn-key {
    background: rgba(255,255,255,.1);
    color: #fff;
    border: none;
    padding: 7px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    transition: background .15s;
}
.btn-key:hover       { background: rgba(255,255,255,.18); }
.btn-key.primary     { background: #25d366; font-weight: 700; }
.btn-key.primary:hover { background: #1ebe57; }

/* ── Card box ── */
.card-box {
    background: #fff;
    border-radius: 14px;
    padding: 22px;
    border: 1px solid #e5e9f0;
    box-shadow: 0 1px 6px rgba(0,0,0,.05);
    margin-bottom: 20px;
}
.box-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 14px;
}

/* ── Code blocks ── */
.code-block {
    border-radius: 8px;
    padding: 14px 16px;
    font-family: monospace;
    font-size: 12px;
    white-space: pre;
    overflow-x: auto;
    line-height: 1.6;
}
.code-dark  { background: #12131f; color: #69f0ae; }
.code-light { background: #f6f8fa; color: #2e7d32; border-left: 3px solid #25d366; }

/* ── Info pills ── */
.info-pills {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    margin-top: 14px;
}
.info-pill {
    border-radius: 8px;
    padding: 10px 12px;
}
.info-pill .pill-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.info-pill code        { font-size: 12px; font-family: monospace; }
.pill-green  { background: #f0fdf4; border-left: 3px solid #25d366; }
.pill-green .pill-label  { color: #2e7d32; }
.pill-blue   { background: #e3f2fd; border-left: 3px solid #1976d2; }
.pill-blue .pill-label   { color: #1565c0; }
.pill-orange { background: #fff3e0; border-left: 3px solid #f57c00; }
.pill-orange .pill-label { color: #e65100; }

/* ── Section heading ── */
.section-heading {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 4px 0 14px;
}

/* ── Endpoint cards ── */
.endpoint-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e9f0;
    box-shadow: 0 1px 5px rgba(0,0,0,.05);
    margin-bottom: 10px;
    overflow: hidden;
    transition: box-shadow .2s;
}
.endpoint-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.08); }

.endpoint-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    cursor: pointer;
    user-select: none;
    flex-wrap: wrap;
    gap: 10px;
}
.endpoint-body {
    display: none;
    padding: 0 20px 20px;
    border-top: 1px solid #f5f5f5;
}
.endpoint-body.open { display: block; }

.method-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    color: #fff;
    flex-shrink: 0;
    letter-spacing: .3px;
}
.endpoint-path {
    font-family: monospace;
    font-size: 13px;
    color: #1a1a2e;
    font-weight: 600;
}
.endpoint-name {
    font-size: 13px;
    color: #888;
    flex: 1;
}
.ep-arrow {
    color: #bbb;
    font-size: 11px;
    transition: transform .2s;
    flex-shrink: 0;
}
.ep-arrow.open { transform: rotate(180deg); }

.ep-desc {
    font-size: 13px;
    color: #555;
    margin: 14px 0 12px;
    line-height: 1.5;
}
.ep-section-label {
    font-size: 10px;
    font-weight: 700;
    color: #aaa;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 6px;
}

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

/* ════════════════════════════
   RESPONSIVE
════════════════════════════ */
@media (max-width: 600px) {
    .apidocs-wrap     { padding: 12px; }
    .api-key-banner   { padding: 14px 16px; }
    .api-key-value    { font-size: 11px; }
    .card-box         { padding: 14px; }
    .endpoint-header  { padding: 12px 14px; }
    .endpoint-body    { padding: 0 14px 14px; }
    .endpoint-path    { font-size: 12px; }
    .code-block       { font-size: 11px; padding: 10px 12px; }
    .info-pills       { grid-template-columns: 1fr 1fr; }
    .data-table       { font-size: 12px; }
    .data-table thead th,
    .data-table tbody td { padding: 8px 8px; }
}
</style>
@endpush

@section('content')
<div class="apidocs-wrap">

    {{-- Page header --}}
    <div style="margin-bottom:20px;">
        <div style="font-size:22px;font-weight:800;color:#1a1a2e;">📖 API Documentation</div>
        <div style="font-size:13px;color:#888;margin-top:4px;">Integrate Fuiox with your CRM or any external system</div>
    </div>

    {{-- ══ API KEY ══ --}}
    <div class="api-key-banner">
        <div class="api-key-label">Your API Key</div>
        <div class="api-key-row">
            <span id="apiKeyDisplay" class="api-key-value">{{ $user->api_key ?? 'Not generated yet' }}</span>
            <div class="api-key-actions">
                <button class="btn-key" onclick="copyApiKey()">📋 Copy</button>
                <button class="btn-key primary" onclick="generateApiKey()">
                    {{ $user->api_key ? '🔄 Regenerate' : '⚡ Generate' }}
                </button>
            </div>
        </div>
    </div>

    {{-- ══ BASE URL & AUTH ══ --}}
    <div class="card-box">
        <div class="box-title">🔗 Base URL & Authentication</div>
        <div class="code-block code-light" style="margin-bottom:12px;font-size:14px;">https://fuiox.com/api</div>
        <p style="font-size:13px;color:#555;margin:0 0 10px;">Include your API key in every request header:</p>
        <div class="code-block code-dark">X-API-Key: your_api_key_here</div>
        <div class="info-pills">
            <div class="info-pill pill-green">
                <div class="pill-label">Content Type</div>
                <code>application/json</code>
            </div>
            <div class="info-pill pill-blue">
                <div class="pill-label">Response Format</div>
                <code>JSON</code>
            </div>
            <div class="info-pill pill-orange">
                <div class="pill-label">Auth Method</div>
                <code>Header Key</code>
            </div>
        </div>
    </div>

    {{-- ══ ENDPOINTS ══ --}}
    <div class="section-heading">📡 Endpoints</div>

    @php
    $endpoints = [
        ['method'=>'POST','color'=>'#1976d2','path'=>'/messages/send-image','title'=>'Send Image Message',
         'desc'=>'Send an image to one or multiple contacts. Supports Excel/CSV file upload for bulk sending.',
         'body'=>'{"phone":"919876543210","image_url":"https://i.ibb.co/xxx/img.jpg","caption":"Hello!"}',
         'response'=>'{"success":true,"sent":1,"failed":0,"total":1}'],
        ['method'=>'POST','color'=>'#25d366','path'=>'/messages/send','title'=>'Send Text Message',
         'desc'=>'Send a WhatsApp text message to a phone number.',
         'body'=>'{"phone":"919876543210","message":"Hello from Fuiox!"}',
         'response'=>'{"success":true,"status":200}'],
        ['method'=>'POST','color'=>'#25d366','path'=>'/templates/send','title'=>'Send Template Message',
         'desc'=>'Send an approved WhatsApp template. Supports body parameters and header image.',
         'body'=>'{"phone":"919876543210","template_name":"hello_world","language":"en_US","parameters":["John"]}',
         'response'=>'{"success":true,"status":200}'],
        ['method'=>'GET','color'=>'#7b1fa2','path'=>'/contacts','title'=>'Get All Contacts',
         'desc'=>'Retrieve all contacts in your account.','body'=>null,
         'response'=>'{"contacts":[{"id":1,"name":"John","phone":"919876543210"}]}'],
        ['method'=>'POST','color'=>'#25d366','path'=>'/contacts','title'=>'Add Contact',
         'desc'=>'Add a new contact to your account.',
         'body'=>'{"name":"John Doe","phone":"919876543210","email":"john@example.com","tags":"lead"}',
         'response'=>'{"success":true,"contact":{"id":5}}'],
        ['method'=>'GET','color'=>'#7b1fa2','path'=>'/messages/{phone}','title'=>'Get Message History',
         'desc'=>'Get chat history for a specific phone number.','body'=>null,
         'response'=>'{"messages":[{"type":"incoming","message":"Hello","created_at":"2026-01-01"}]}'],
        ['method'=>'POST','color'=>'#f57c00','path'=>'/campaigns','title'=>'Launch Campaign',
         'desc'=>'Send a template to multiple contacts at once.',
         'body'=>'{"name":"Promo","template_name":"hello_world","language":"en_US","phones":["919876543210"]}',
         'response'=>'{"success":true,"sent":1,"failed":0}'],
        ['method'=>'GET','color'=>'#7b1fa2','path'=>'/templates','title'=>'Get Templates',
         'desc'=>'List all approved WhatsApp templates.','body'=>null,
         'response'=>'{"templates":[{"name":"hello_world","status":"APPROVED","language":"en_US"}]}'],
    ];
    @endphp

    @foreach($endpoints as $i => $ep)
    <div class="endpoint-card">
        <div class="endpoint-header" onclick="toggleEP({{ $i }})">
            <span class="method-badge" style="background:{{ $ep['color'] }};">{{ $ep['method'] }}</span>
            <span class="endpoint-path">/api{{ $ep['path'] }}</span>
            <span class="endpoint-name">— {{ $ep['title'] }}</span>
            <span id="arrow-{{ $i }}" class="ep-arrow">▼</span>
        </div>
        <div id="body-{{ $i }}" class="endpoint-body">
            <p class="ep-desc">{{ $ep['desc'] }}</p>
            @if($ep['body'])
            <div style="margin-bottom:14px;">
                <div class="ep-section-label">Request Body</div>
                <div class="code-block code-dark">{{ $ep['body'] }}</div>
            </div>
            @endif
            <div>
                <div class="ep-section-label">Response</div>
                <div class="code-block code-light">{{ $ep['response'] }}</div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- ══ ERROR CODES ══ --}}
    <div class="card-box" style="margin-top:20px;">
        <div class="box-title">⚠️ Error Codes</div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr><th>Code</th><th>Meaning</th><th>Solution</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code style="background:#fdecea;color:#c62828;padding:2px 8px;border-radius:4px;">401</code></td>
                        <td>Invalid API Key</td>
                        <td style="color:#888;">Check X-API-Key header</td>
                    </tr>
                    <tr>
                        <td><code style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:4px;">400</code></td>
                        <td>Bad Request</td>
                        <td style="color:#888;">Check required fields</td>
                    </tr>
                    <tr>
                        <td><code style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:4px;">403</code></td>
                        <td>Plan Limit Reached</td>
                        <td style="color:#888;">Upgrade your plan</td>
                    </tr>
                    <tr>
                        <td><code style="background:#fff8e1;color:#f57c00;padding:2px 8px;border-radius:4px;">429</code></td>
                        <td>Rate Limited</td>
                        <td style="color:#888;">Slow down requests</td>
                    </tr>
                    <tr>
                        <td><code style="background:#fdecea;color:#c62828;padding:2px 8px;border-radius:4px;">500</code></td>
                        <td>Server Error</td>
                        <td style="color:#888;">Contact support</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleEP(i) {
    const body  = document.getElementById('body-' + i);
    const arrow = document.getElementById('arrow-' + i);
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    arrow.classList.toggle('open', !isOpen);
}

function copyApiKey() {
    const key = document.getElementById('apiKeyDisplay').textContent.trim();
    if (!key || key === 'Not generated yet') {
        showToast('Generate your API key first', 'error');
        return;
    }
    navigator.clipboard.writeText(key).then(() => showToast('✅ API key copied!', 'success'));
}

function generateApiKey() {
    fetch('/api/generate-key', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.key) {
            document.getElementById('apiKeyDisplay').textContent = d.key;
            showToast('✅ API key generated!', 'success');
        }
    });
}
</script>
@endpush