<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Connect WhatsApp — Fuiox</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --green: #25d366;
    --green-dark: #128C7E;
    --green-glow: rgba(37,211,102,0.3);
    --bg: #0a0f0d;
    --card: #111a14;
    --border: rgba(37,211,102,0.15);
    --text: #e8f5e9;
    --muted: rgba(232,245,233,0.45);
}
* { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family:'Plus Jakarta Sans',sans-serif;
    background:var(--bg);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    position:relative;
    overflow:hidden;
}
body::before {
    content:'';
    position:fixed;
    top:-200px; left:-200px;
    width:600px; height:600px;
    background:radial-gradient(circle, rgba(37,211,102,0.08) 0%, transparent 70%);
    pointer-events:none;
}
body::after {
    content:'';
    position:fixed;
    bottom:-200px; right:-200px;
    width:500px; height:500px;
    background:radial-gradient(circle, rgba(18,140,126,0.06) 0%, transparent 70%);
    pointer-events:none;
}

.setup-wrap {
    width:100%;
    max-width:480px;
    position:relative;
    z-index:1;
}

/* Logo */
.logo {
    text-align:center;
    margin-bottom:32px;
}
.logo-badge {
    display:inline-flex;
    align-items:center;
    gap:10px;
    background:rgba(37,211,102,0.08);
    border:1px solid var(--border);
    border-radius:50px;
    padding:8px 20px;
}
.logo-icon {
    width:32px; height:32px;
    background:var(--green);
    border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:16px;
}
.logo-text {
    font-size:15px;
    font-weight:800;
    color:var(--text);
    letter-spacing:-0.3px;
}
.logo-text span { color:var(--green); }

/* Card */
.card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:24px;
    padding:36px;
    position:relative;
    overflow:hidden;
}
.card::before {
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:1px;
    background:linear-gradient(90deg, transparent, var(--green), transparent);
}

.user-chip {
    display:flex;
    align-items:center;
    gap:10px;
    background:rgba(37,211,102,0.06);
    border:1px solid rgba(37,211,102,0.12);
    border-radius:12px;
    padding:10px 14px;
    margin-bottom:28px;
}
.user-av {
    width:34px; height:34px;
    background:linear-gradient(135deg,var(--green),var(--green-dark));
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:800; color:#fff;
    flex-shrink:0;
}
.user-info { flex:1; min-width:0; }
.user-name { font-size:13px; font-weight:700; color:var(--text); }
.user-email { font-size:11px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.card-title {
    font-size:22px;
    font-weight:900;
    color:var(--text);
    letter-spacing:-0.5px;
    margin-bottom:6px;
}
.card-sub {
    font-size:13px;
    color:var(--muted);
    margin-bottom:28px;
    line-height:1.6;
}

/* Steps indicator */
.steps {
    display:flex;
    align-items:center;
    gap:0;
    margin-bottom:28px;
}
.step-item {
    display:flex;
    align-items:center;
    gap:8px;
    flex:1;
}
.step-num {
    width:28px; height:28px;
    border-radius:50%;
    background:rgba(37,211,102,0.1);
    border:1.5px solid rgba(37,211,102,0.2);
    color:var(--muted);
    font-size:12px; font-weight:700;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
    transition:.3s;
}
.step-num.active {
    background:var(--green);
    border-color:var(--green);
    color:#fff;
    box-shadow:0 0 12px var(--green-glow);
}
.step-num.done {
    background:rgba(37,211,102,0.2);
    border-color:var(--green);
    color:var(--green);
}
.step-label {
    font-size:11px;
    color:var(--muted);
    font-weight:600;
}
.step-label.active { color:var(--green); }
.step-line {
    flex:1;
    height:1px;
    background:rgba(37,211,102,0.1);
    margin:0 8px;
}

/* Alert */
.fu-alert {
    padding:12px 14px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:16px;
    display:none;
}
.fu-alert.err { background:rgba(229,57,53,0.1); border:1px solid rgba(229,57,53,0.3); color:#ef9a9a; }
.fu-alert.suc { background:rgba(37,211,102,0.1); border:1px solid rgba(37,211,102,0.3); color:var(--green); }

/* Buttons */
.btn-fb {
    width:100%;
    padding:14px;
    background:linear-gradient(135deg, #1877f2, #0d6efd);
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:15px;
    font-weight:700;
    font-family:inherit;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    transition:.2s;
    margin-bottom:12px;
    box-shadow:0 4px 20px rgba(24,119,242,0.3);
}
.btn-fb:hover { transform:translateY(-1px); box-shadow:0 6px 24px rgba(24,119,242,0.4); }
.btn-fb:disabled { opacity:.6; cursor:not-allowed; transform:none; }

.btn-manual {
    width:100%;
    padding:12px;
    background:transparent;
    color:var(--muted);
    border:1px solid var(--border);
    border-radius:12px;
    font-size:13px;
    font-weight:600;
    font-family:inherit;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:.2s;
    margin-bottom:16px;
}
.btn-manual:hover { border-color:var(--green); color:var(--green); }

.btn-green {
    width:100%;
    padding:14px;
    background:linear-gradient(135deg, var(--green), var(--green-dark));
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:15px;
    font-weight:700;
    font-family:inherit;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:.2s;
    box-shadow:0 4px 20px var(--green-glow);
}
.btn-green:hover { transform:translateY(-1px); box-shadow:0 6px 28px var(--green-glow); }
.btn-green:disabled { opacity:.6; cursor:not-allowed; transform:none; }

.btn-back {
    background:none;
    border:none;
    color:var(--muted);
    font-size:13px;
    font-family:inherit;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:6px;
    margin-bottom:20px;
    padding:0;
    transition:.2s;
}
.btn-back:hover { color:var(--green); }

/* Manual input */
.fu-group { margin-bottom:14px; }
.fu-group label {
    display:block;
    font-size:11px;
    font-weight:700;
    color:var(--muted);
    text-transform:uppercase;
    letter-spacing:.5px;
    margin-bottom:6px;
}
.fu-input {
    width:100%;
    padding:12px 14px;
    background:rgba(255,255,255,0.04);
    border:1.5px solid var(--border);
    border-radius:10px;
    color:var(--text);
    font-size:14px;
    font-family:inherit;
    outline:none;
    transition:.2s;
}
.fu-input:focus {
    border-color:var(--green);
    box-shadow:0 0 0 3px rgba(37,211,102,0.1);
    background:rgba(37,211,102,0.03);
}
.fu-input::placeholder { color:rgba(232,245,233,0.2); }

/* Preview card */
.preview-card {
    background:rgba(37,211,102,0.05);
    border:1px solid rgba(37,211,102,0.2);
    border-radius:14px;
    padding:18px;
    margin-bottom:20px;
}
.preview-title {
    font-size:12px;
    font-weight:700;
    color:var(--green);
    text-transform:uppercase;
    letter-spacing:.5px;
    margin-bottom:12px;
    display:flex;
    align-items:center;
    gap:6px;
}
.preview-row {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:8px 0;
    border-bottom:1px solid rgba(37,211,102,0.08);
    font-size:13px;
}
.preview-row:last-child { border-bottom:none; padding-bottom:0; }
.preview-key { color:var(--muted); }
.preview-val { font-weight:700; color:var(--text); font-family:monospace; font-size:12px; text-align:right; max-width:60%; overflow:hidden; text-overflow:ellipsis; }

/* Phone selector */
.phone-option {
    background:rgba(255,255,255,0.03);
    border:1.5px solid var(--border);
    border-radius:12px;
    padding:14px 16px;
    cursor:pointer;
    transition:.2s;
    margin-bottom:10px;
    display:flex;
    align-items:center;
    gap:12px;
}
.phone-option:hover { border-color:var(--green); background:rgba(37,211,102,0.05); }
.phone-option.selected { border-color:var(--green); background:rgba(37,211,102,0.08); }
.phone-icon {
    width:40px; height:40px;
    background:rgba(37,211,102,0.1);
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    color:var(--green);
    font-size:18px;
    flex-shrink:0;
}
.phone-name { font-size:14px; font-weight:700; color:var(--text); }
.phone-num { font-size:12px; color:var(--muted); margin-top:2px; }

/* Divider */
.divider {
    display:flex;
    align-items:center;
    gap:12px;
    margin:16px 0;
}
.divider-line { flex:1; height:1px; background:var(--border); }
.divider-text { font-size:11px; color:var(--muted); font-weight:600; white-space:nowrap; }

/* Loading spinner */
.spin { animation:spin .8s linear infinite; display:inline-block; }
@keyframes spin { to { transform:rotate(360deg); } }

/* Sections */
.section { display:none; }
.section.active { display:block; }

/* Logout */
.logout-wrap {
    text-align:center;
    margin-top:20px;
}
.btn-logout {
    background:none;
    border:none;
    color:var(--muted);
    font-size:12px;
    cursor:pointer;
    font-family:inherit;
    transition:.2s;
}
.btn-logout:hover { color:var(--green); }

/* Trust badges */
.trust {
    display:flex;
    justify-content:center;
    gap:16px;
    margin-top:20px;
    flex-wrap:wrap;
}
.trust-item {
    display:flex;
    align-items:center;
    gap:5px;
    font-size:11px;
    color:var(--muted);
}
.trust-item i { color:var(--green); font-size:12px; }
</style>
</head>
<body>

<div class="setup-wrap">

    <!-- Logo -->
    <div class="logo">
        <div class="logo-badge">
            <div class="logo-icon">💬</div>
            <div class="logo-text">Fuiox <span>Technologies</span></div>
        </div>
    </div>

    <!-- Card -->
    <div class="card">

        <!-- User chip -->
        <div class="user-chip">
            <div class="user-av">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-email">{{ $user->email }}</div>
            </div>
            <i class="bi bi-check-circle-fill" style="color:var(--green);font-size:16px;"></i>
        </div>

        <!-- Alert -->
        <div id="jsAlert" class="fu-alert err"></div>
        <div id="jsSuc" class="fu-alert suc"></div>

        @if($errors->any())
        <div class="fu-alert err" style="display:block;">{{ $errors->first() }}</div>
        @endif

        <!-- Steps -->
        <div class="steps">
            <div class="step-item">
                <div class="step-num active" id="step1Num">1</div>
                <div class="step-label active" id="step1Label">Connect</div>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-num" id="step2Num">2</div>
                <div class="step-label" id="step2Label">Verify</div>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-num" id="step3Num">3</div>
                <div class="step-label" id="step3Label">Done</div>
            </div>
        </div>

        <!-- ── SECTION 1: Choose method ── -->
        <div class="section active" id="sec1">
            <div class="card-title">Connect WhatsApp Business</div>
            <div class="card-sub">Link your Meta WhatsApp API to start sending messages and managing conversations.</div>

            <!-- Facebook Embedded Signup Button -->
            <button class="btn-fb" id="fbSignupBtn" onclick="startEmbeddedSignup()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Continue with Facebook
            </button>

            <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-text">or connect manually</div>
                <div class="divider-line"></div>
            </div>

            <button class="btn-manual" onclick="showManual()">
                <i class="bi bi-key-fill"></i>
                Enter credentials manually
            </button>

            <div style="font-size:11px;color:var(--muted);text-align:center;line-height:1.6;">
                <i class="bi bi-shield-check me-1" style="color:var(--green);"></i>
                Your credentials are encrypted and never shared
            </div>
        </div>

        <!-- ── SECTION 2: Manual entry ── -->
        <div class="section" id="sec2">
            <button class="btn-back" onclick="showSection('sec1')">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <div class="card-title">Enter Credentials</div>
            <div class="card-sub">Find these in Meta Business Manager → WhatsApp → API Setup</div>

            <div class="fu-group">
                <label for="sToken">Access Token *</label>
                <input type="text" id="sToken" class="fu-input" placeholder="EAAxxxxxxxxxxxxxxx…" autocomplete="off">
            </div>
            <div class="fu-group">
                <label for="sWaba">WABA ID *</label>
                <input type="text" id="sWaba" class="fu-input" placeholder="e.g. 24968695112827640" autocomplete="off">
            </div>

            <button class="btn-green" id="fetchBtn" onclick="doFetch()" style="margin-top:4px;">
                <i class="bi bi-search"></i> Fetch My WhatsApp Details
            </button>
        </div>

        <!-- ── SECTION 3: Phone selection (if multiple phones) ── -->
        <div class="section" id="sec3">
            <button class="btn-back" onclick="goBackFromPhones()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <div class="card-title">Select Phone Number</div>
            <div class="card-sub">Multiple numbers found. Select which one to connect.</div>
            <div id="phoneList"></div>
        </div>

        <!-- ── SECTION 4: Confirm details ── -->
        <div class="section" id="sec4">
            <button class="btn-back" onclick="goBackFromConfirm()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <div class="card-title">Confirm & Connect</div>
            <div class="card-sub">Review your WhatsApp Business details before connecting.</div>

            <div class="preview-card">
                <div class="preview-title"><i class="bi bi-whatsapp"></i> Account Details</div>
                <div class="preview-row">
                    <span class="preview-key">Business Name</span>
                    <span class="preview-val" id="fpName">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-key">Phone Number</span>
                    <span class="preview-val" id="fpDisplay">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-key">Phone ID</span>
                    <span class="preview-val" id="fpPhoneId">—</span>
                </div>
                <div class="preview-row">
                    <span class="preview-key">WABA ID</span>
                    <span class="preview-val" id="fpWabaId">—</span>
                </div>
            </div>

            <button class="btn-green" id="connectBtn" onclick="doConnect()">
                <i class="bi bi-whatsapp"></i> Connect WhatsApp
            </button>
        </div>

        <!-- ── SECTION 5: Success ── -->
        <div class="section" id="sec5">
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:56px;margin-bottom:16px;">🎉</div>
                <div style="font-size:20px;font-weight:900;color:var(--text);margin-bottom:8px;">Connected!</div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:28px;line-height:1.6;">Your WhatsApp Business account is now connected to Fuiox. You can start sending messages.</div>
                <button class="btn-green" onclick="window.location.href='{{ route('dashboard') }}'">
                    <i class="bi bi-arrow-right-circle"></i> Go to Dashboard
                </button>
            </div>
        </div>

        <!-- Hidden form data -->
        <input type="hidden" id="hToken">
        <input type="hidden" id="hPhoneId">
        <input type="hidden" id="hWabaId">
        <input type="hidden" id="hMobile">
        <input type="hidden" id="hVerifiedName">
        <input type="hidden" id="hFromFb"> {{-- 1 = from facebook signup --}}

    </div>

    <!-- Logout -->
    <div class="logout-wrap">
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout">← Sign out</button>
        </form>
    </div>

    <!-- Trust badges -->
    <div class="trust">
        <div class="trust-item"><i class="bi bi-shield-fill-check"></i> SSL Secured</div>
        <div class="trust-item"><i class="bi bi-meta"></i> Meta Partner</div>
        <div class="trust-item"><i class="bi bi-lock-fill"></i> Encrypted</div>
    </div>

</div>

<!-- Meta Facebook SDK -->
<script>
window.fbAsyncInit = function() {
    FB.init({
        appId   : '{{ config("services.meta.app_id") }}',
        cookie  : true,
        xfbml   : true,
        version : 'v25.0'
    });
};
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
let fbAccessToken = null;
let allPhones = [];
let currentFromFb = false;

function showErr(msg){ const el=document.getElementById('jsAlert'); el.textContent=msg; el.style.display='block'; document.getElementById('jsSuc').style.display='none'; }
function showSuc(msg){ const el=document.getElementById('jsSuc'); el.textContent=msg; el.style.display='block'; document.getElementById('jsAlert').style.display='none'; }
function clearAlerts(){ document.getElementById('jsAlert').style.display='none'; document.getElementById('jsSuc').style.display='none'; }

function showSection(id){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    clearAlerts();
}

function updateSteps(step){
    for(let i=1;i<=3;i++){
        const num = document.getElementById('step'+i+'Num');
        const lbl = document.getElementById('step'+i+'Label');
        if(i < step){ num.className='step-num done'; num.innerHTML='<i class="bi bi-check"></i>'; lbl.className='step-label'; }
        else if(i === step){ num.className='step-num active'; num.textContent=i; lbl.className='step-label active'; }
        else{ num.className='step-num'; num.textContent=i; lbl.className='step-label'; }
    }
}

function showManual(){
    showSection('sec2');
    updateSteps(1);
}

function goBackFromPhones(){
    currentFromFb ? showSection('sec1') : showSection('sec2');
    updateSteps(1);
}

function goBackFromConfirm(){
    if(allPhones.length > 1){ showSection('sec3'); updateSteps(2); }
    else { currentFromFb ? showSection('sec1') : showSection('sec2'); updateSteps(1); }
}

// ── Facebook Embedded Signup ──
function startEmbeddedSignup(){
    clearAlerts();
    const btn = document.getElementById('fbSignupBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spin"><i class="bi bi-arrow-repeat"></i></span> Opening Facebook…';

    // Use Embedded Signup flow which returns WABA ID automatically
    FB.login(function(response){
        btn.disabled = false;
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg> Continue with Facebook';

        if(response.authResponse){
            fbAccessToken = response.authResponse.accessToken;
            currentFromFb = true;
            showSuc('Facebook connected! Waiting for WhatsApp account details…');
        } else {
            showErr('Facebook login was cancelled or failed. Please try again.');
        }
    }, {
        config_id: '1493432832581006',
        response_type: 'code',
        override_default_response_type: true,
        extras: {
            sessionInfoVersion: '3',
        }
    });
}

// Listen for WABA ID from Embedded Signup session info
window.addEventListener('message', function(event){
    if(event.origin !== 'https://www.facebook.com' && event.origin !== 'https://web.facebook.com') return;
    try {
        const data = JSON.parse(event.data);
        console.log('FB message received:', data);
        if(data.type === 'WA_EMBEDDED_SIGNUP'){
            if(data.event === 'FINISH'){
                const wabaId  = data.data.waba_id;
                const phoneId = data.data.phone_number_id;
                console.log('WABA ID:', wabaId, 'Phone ID:', phoneId);
                if(wabaId && phoneId && fbAccessToken){
                    // We have both WABA and Phone ID — fetch details
                    fetchPhoneDetails(fbAccessToken, wabaId);
                } else if(wabaId && fbAccessToken){
                    fetchPhoneDetails(fbAccessToken, wabaId);
                } else {
                    // Fallback to manual
                    fetchWabaFromToken(fbAccessToken);
                }
            } else if(data.event === 'CANCEL'){
                showErr('Setup was cancelled. Please try again.');
            } else if(data.event === 'ERROR'){
                showErr('An error occurred: ' + (data.data.error_message || 'Unknown error'));
            }
        }
    } catch(e){ console.log('Message parse error:', e); }
});

// ── After FB login: fetch WABA and phone details ──
function fetchFromFacebook(token){
    showSection('sec1');
    showSuc('Facebook connected! Fetching your WhatsApp details…');

    // Step 1: Get user's businesses
    FB.api('/me/businesses', {access_token: token, fields: 'id,name'}, function(bizResp){
        if(!bizResp || bizResp.error || !bizResp.data || !bizResp.data.length){
            // Try getting WABA directly from user
            FB.api('/me/whatsapp_business_accounts', {access_token: token}, function(directWaba){
                if(!directWaba || directWaba.error || !directWaba.data || !directWaba.data.length){
                    fetchWabaFromToken(token);
                    return;
                }
                const wabaId = directWaba.data[0].id;
                fetchPhoneDetails(token, wabaId);
            });
            return;
        }

        const bizId = bizResp.data[0].id;

        // Step 2: Get owned WABA accounts
        FB.api('/'+bizId+'/owned_whatsapp_business_accounts', {access_token: token, fields: 'id,name,currency,timezone_id'}, function(wabaResp){
            if(!wabaResp || wabaResp.error || !wabaResp.data || !wabaResp.data.length){
                // Try client WABA
                FB.api('/'+bizId+'/client_whatsapp_business_accounts', {access_token: token}, function(clientWaba){
                    if(!clientWaba || clientWaba.error || !clientWaba.data || !clientWaba.data.length){
                        fetchWabaFromToken(token);
                        return;
                    }
                    const wabaId = clientWaba.data[0].id;
                    fetchPhoneDetails(token, wabaId);
                });
                return;
            }
            const wabaId = wabaResp.data[0].id;
            fetchPhoneDetails(token, wabaId);
        });
    });
}

function fetchWabaFromToken(token){
    // Fallback: auto-fill token, ask only for WABA ID
    showSection('sec2');
    document.getElementById('sToken').value = token;
    document.getElementById('sToken').style.opacity = '0.5';
    document.getElementById('sToken').readOnly = true;
    showSuc('✅ Facebook connected! Just enter your WABA ID below to complete setup.');
    updateSteps(1);
}

function fetchPhoneDetails(token, wabaId){
    fetch('/embedded-signup/fetch-details', {
        method:'POST',
        credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({access_token:token, waba_id:wabaId})
    }).then(r=>r.json()).then(d=>{
        if(d.error){ showErr('❌ '+d.error); showSection('sec1'); return; }

        if(d.all_phones && d.all_phones.length > 1){
            // Multiple phones — let user choose
            allPhones = d.all_phones;
            document.getElementById('hToken').value = token;
            document.getElementById('hWabaId').value = wabaId;
            renderPhoneList(d.all_phones, token, wabaId);
            showSection('sec3');
            updateSteps(2);
        } else {
            // Single phone — go straight to confirm
            fillConfirm(d, token);
            showSection('sec4');
            updateSteps(2);
        }
    }).catch(()=>{ showErr('❌ Network error. Please try again.'); showSection('sec1'); });
}

function renderPhoneList(phones, token, wabaId){
    const list = document.getElementById('phoneList');
    list.innerHTML = phones.map((p,i) => `
        <div class="phone-option" onclick="selectPhone('${p.id}','${token}','${wabaId}',this)">
            <div class="phone-icon"><i class="bi bi-whatsapp"></i></div>
            <div>
                <div class="phone-name">${escHtml(p.verified_name||'Unknown')}</div>
                <div class="phone-num">${escHtml(p.display_phone_number||p.id)}</div>
            </div>
        </div>
    `).join('');
}

function selectPhone(phoneId, token, wabaId, el){
    document.querySelectorAll('.phone-option').forEach(o=>o.classList.remove('selected'));
    el.classList.add('selected');

    fetch('/embedded-signup/select-phone', {
        method:'POST',
        credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({access_token:token, waba_id:wabaId, phone_number_id:phoneId})
    }).then(r=>r.json()).then(d=>{
        if(d.error){ showErr('❌ '+d.error); return; }
        fillConfirm(d, token);
        showSection('sec4');
        updateSteps(2);
    }).catch(()=>showErr('❌ Network error'));
}

function fillConfirm(d, token){
    document.getElementById('fpName').textContent    = d.verified_name || '—';
    document.getElementById('fpDisplay').textContent = d.display_phone || '—';
    document.getElementById('fpPhoneId').textContent = d.phone_number_id || '—';
    document.getElementById('fpWabaId').textContent  = d.business_account_id || '—';
    document.getElementById('hToken').value          = token;
    document.getElementById('hPhoneId').value        = d.phone_number_id || '';
    document.getElementById('hWabaId').value         = d.business_account_id || '';
    document.getElementById('hMobile').value         = d.mobile || '';
    document.getElementById('hVerifiedName').value   = d.verified_name || '';
}

// ── Manual fetch ──
function doFetch(){
    const token = document.getElementById('sToken').value.trim();
    const waba  = document.getElementById('sWaba').value.trim();
    if(!token||!waba){ showErr('Please enter both Access Token and WABA ID.'); return; }
    const btn = document.getElementById('fetchBtn');
    btn.disabled=true; btn.innerHTML='<span class="spin"><i class="bi bi-arrow-repeat"></i></span> Fetching…';

    fetch('/embedded-signup/fetch-details', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({access_token:token, waba_id:waba})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-search"></i> Fetch My WhatsApp Details';
        if(d.error){ showErr('❌ '+d.error); return; }
        currentFromFb = false;
        if(d.all_phones && d.all_phones.length > 1){
            allPhones = d.all_phones;
            renderPhoneList(d.all_phones, token, waba);
            showSection('sec3'); updateSteps(2);
        } else {
            fillConfirm(d, token);
            showSection('sec4'); updateSteps(2);
        }
    }).catch(()=>{ btn.disabled=false; btn.innerHTML='<i class="bi bi-search"></i> Fetch My WhatsApp Details'; showErr('❌ Connection error.'); });
}

// ── Connect ──
function doConnect(){
    const btn = document.getElementById('connectBtn');
    btn.disabled=true; btn.innerHTML='<span class="spin"><i class="bi bi-arrow-repeat"></i></span> Connecting…';

    fetch('/embedded-signup/connect', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({
            access_token        : document.getElementById('hToken').value,
            phone_number_id     : document.getElementById('hPhoneId').value,
            business_account_id : document.getElementById('hWabaId').value,
            mobile              : document.getElementById('hMobile').value,
            verified_name       : document.getElementById('hVerifiedName').value,
        })
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-whatsapp"></i> Connect WhatsApp';
        if(d.error){ showErr('❌ '+d.error); return; }
        updateSteps(3);
        showSection('sec5');
    }).catch(()=>{ btn.disabled=false; btn.innerHTML='<i class="bi bi-whatsapp"></i> Connect WhatsApp'; showErr('❌ Network error.'); });
}

function escHtml(str){
    if(!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
</body>
</html>