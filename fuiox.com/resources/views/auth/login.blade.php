<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Fuiox Technologies</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --green:#25d366; --green-dark:#1fba58; --dark:#0d1117; }
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Plus Jakarta Sans',sans-serif; min-height:100vh; display:flex; }

/* Left decorative panel */
.auth-left {
    width:420px; flex-shrink:0;
    background:linear-gradient(145deg,#0d2818,#1a4a2e);
    display:flex; flex-direction:column; justify-content:center;
    padding:56px 48px; position:relative; overflow:hidden;
}
.auth-left::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:300px; height:300px; border-radius:50%;
    background:rgba(37,211,102,.08); pointer-events:none;
}
.auth-left::after {
    content:''; position:absolute; bottom:-60px; left:-60px;
    width:220px; height:220px; border-radius:50%;
    background:rgba(37,211,102,.06); pointer-events:none;
}
.auth-left .brand { font-size:20px; font-weight:800; color:#fff; margin-bottom:40px; }
.auth-left .brand span { color:var(--green); }
.auth-left .tagline { font-size:30px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:14px; }
.auth-left .tagline span { color:var(--green); }
.auth-left p { font-size:14px; color:rgba(255,255,255,.5); line-height:1.7; margin-bottom:32px; }
.auth-feature { display:flex; align-items:center; gap:10px; margin-bottom:12px; font-size:14px; color:rgba(255,255,255,.65); }
.auth-feature i { color:var(--green); font-size:15px; }

/* Right form panel */
.auth-right {
    flex:1; background:#fff;
    display:flex; align-items:center; justify-content:center;
    padding:40px 24px;
}
.auth-card { width:100%; max-width:420px; }
.auth-card .back-link { font-size:13px; color:#aaa; display:inline-flex; align-items:center; gap:5px; margin-bottom:28px; transition:color .15s; }
.auth-card .back-link:hover { color:var(--green); text-decoration:none; }
.auth-card h2 { font-size:26px; font-weight:800; color:#0d1117; margin-bottom:4px; }
.auth-card .sub { font-size:14px; color:#888; margin-bottom:28px; }

/* Inputs */
.fu-group { margin-bottom:16px; }
.fu-group label { display:block; font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; }
.fu-group .fu-input {
    width:100%; padding:12px 14px;
    border:1.5px solid #e5e9f0; border-radius:10px;
    font-size:14px; outline:none; font-family:inherit;
    color:#0d1117; background:#fafafa;
    transition:border-color .15s, box-shadow .15s;
}
.fu-group .fu-input:focus { border-color:var(--green); box-shadow:0 0 0 3px rgba(37,211,102,.1); background:#fff; }
.fu-hint { font-size:11px; color:#bbb; margin-top:4px; }

/* Buttons */
.btn-fu { width:100%; padding:13px; background:var(--green); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700; font-family:inherit; cursor:pointer; transition:.15s; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn-fu:hover { background:var(--green-dark); transform:translateY(-1px); }
.btn-fu:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* Alerts */
.fu-alert { font-size:13px; padding:11px 14px; border-radius:9px; margin-bottom:16px; display:none; }
.fu-alert.err { background:#fdecea; color:#c62828; border-left:3px solid #e53935; }
.fu-alert.suc { background:#e8f5e9; color:#2e7d32; border-left:3px solid var(--green); }
.fu-alert.warn { background:#fff3e0; color:#e65100; border-left:3px solid #f57c00; }

/* OTP */
.otp-info { background:#e8f5e9; color:#2e7d32; font-size:13px; padding:11px 14px; border-radius:9px; border-left:3px solid var(--green); margin-bottom:16px; }
.otp-row { display:flex; gap:8px; justify-content:center; margin:14px 0 8px; }
.otp-box { width:46px; height:54px; border-radius:10px; border:2px solid #e5e9f0; text-align:center; font-size:22px; font-weight:700; background:#fafafa; outline:none; transition:.2s; font-family:inherit; color:#0d1117; }
.otp-box:focus { border-color:var(--green); background:#fff; box-shadow:0 0 0 3px rgba(37,211,102,.1); }
.otp-box.filled { border-color:var(--green); }

.auth-switch { font-size:13px; color:#888; text-align:center; margin-top:20px; }
.auth-switch a { color:var(--green); font-weight:600; }

@media(max-width:900px){ .auth-left { display:none !important; } }
@media(max-width:480px){ .auth-right { padding:24px 16px; } }
</style>
</head>
<body>

{{-- Left panel --}}
<div class="auth-left d-none d-md-flex flex-column">
    <div class="brand">Fuiox <span>Technologies</span></div>
    <div class="tagline">Welcome back to <span>Fuiox</span></div>
    <p>Sign in to manage your WhatsApp Business campaigns, contacts and automation from one powerful dashboard.</p>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Official Meta WhatsApp API</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Bulk campaigns to thousands</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> AI-powered auto replies</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Real-time chat inbox</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> 98% message delivery rate</div>
</div>

{{-- Right panel --}}
<div class="auth-right">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to home</a>
        <h2>Welcome back</h2>
        <p class="sub">Sign in to your Fuiox account</p>

        {{-- Blade alerts --}}
        @if($errors->any())
            <div class="fu-alert err" style="display:block;">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="fu-alert suc" style="display:block;">{{ session('success') }}</div>
        @endif

        {{-- JS alerts --}}
        <div class="fu-alert err" id="jsErr"></div>
        <div class="fu-alert suc" id="jsSuc"></div>
        <div class="fu-alert warn" id="jsWarn"></div>

        {{-- Step 1: Email + Password --}}
        <div id="step1">
            <div class="fu-group">
                <label>Email Address</label>
                <input type="email" class="fu-input" id="lEmail" placeholder="you@example.com" autocomplete="off">
            </div>
            <div class="fu-group">
                <label>Password</label>
                <input type="password" class="fu-input" id="lPassword" placeholder="Your password" autocomplete="new-password">
            </div>
            <button class="btn-fu" id="btnLogin" onclick="submitLogin()">
                Sign in <i class="bi bi-arrow-right"></i>
            </button>
        </div>

        {{-- Step 2: OTP --}}
        <div id="step2" style="display:none;">
            <div class="otp-info">
                📧 OTP sent to <strong id="lOtpEmail"></strong><br>
                Expires in <strong id="lOtpTimer" style="color:#e53935;">01:00</strong>
            </div>
            <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Enter 6-digit OTP</label>
            <div class="otp-row" id="lOtpRow"></div>
            <button class="btn-fu mt-3" id="btnVerify" onclick="submitOtp()">
                Verify OTP <i class="bi bi-shield-check"></i>
            </button>
            <div class="text-center mt-3">
                <button id="lResendBtn" onclick="resendOtp()" disabled
                    style="background:none;border:none;color:var(--green);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:underline;">
                    Resend OTP <span id="lResendTimer"></span>
                </button>
            </div>
            <div class="text-center mt-2">
                <a href="#" onclick="backToStep1();return false;" style="font-size:12px;color:#aaa;">← Back to login</a>
            </div>
        </div>

        <div class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Sign up free</a></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const csrf = '{{ csrf_token() }}';
let otpInterval=null, resendInterval=null, attemptCount=0, lastAttempt=0;

function showErr(m){ const e=document.getElementById('jsErr'); e.textContent=m; e.style.display='block'; document.getElementById('jsSuc').style.display='none'; document.getElementById('jsWarn').style.display='none'; }
function showSuc(m){ const s=document.getElementById('jsSuc'); s.textContent=m; s.style.display='block'; document.getElementById('jsErr').style.display='none'; }
function hideAll(){ ['jsErr','jsSuc','jsWarn'].forEach(id=>document.getElementById(id).style.display='none'); }

function submitLogin() {
    const now=Date.now();
    if(attemptCount>=3&&now-lastAttempt<30000){
        const w=document.getElementById('jsWarn'); w.textContent=`⚠️ Too many attempts. Please wait ${Math.ceil((30000-(now-lastAttempt))/1000)}s.`; w.style.display='block'; return;
    }
    const email=document.getElementById('lEmail').value.trim();
    const password=document.getElementById('lPassword').value.trim();
    if(!email||!password){ showErr('Please enter email and password.'); return; }
    const btn=document.getElementById('btnLogin'); btn.innerHTML='Sending OTP… <i class="bi bi-hourglass-split"></i>'; btn.disabled=true;
    fetch('/login/send-otp',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({email,password})})
    .then(r=>r.json()).then(data=>{
        btn.innerHTML='Sign in <i class="bi bi-arrow-right"></i>'; btn.disabled=false;
        attemptCount++; lastAttempt=Date.now();
        if(data.error){ showErr(data.error); return; }
        document.getElementById('step1').style.display='none';
        document.getElementById('step2').style.display='block';
        document.getElementById('lOtpEmail').textContent=email;
        buildOtp(); showSuc('OTP sent! Check your email.');
        startOtpTimer(60); startResendTimer(60);
    }).catch(()=>{ btn.innerHTML='Sign in <i class="bi bi-arrow-right"></i>'; btn.disabled=false; showErr('Network error.'); });
}

function submitOtp() {
    const otp=getOtp();
    if(otp.length!==6){ showErr('Enter all 6 digits.'); return; }
    const btn=document.getElementById('btnVerify'); btn.innerHTML='Verifying… <i class="bi bi-hourglass-split"></i>'; btn.disabled=true;
    fetch('/login/verify-otp',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({otp})})
    .then(r=>r.json()).then(data=>{
        btn.innerHTML='Verify OTP <i class="bi bi-shield-check"></i>'; btn.disabled=false;
        if(data.error){ showErr(data.error); return; }
        showSuc('✅ Verified! Redirecting…');
        // Use form redirect to ensure session cookie persists
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = data.redirect || '/dashboard';
        document.body.appendChild(form);
        setTimeout(()=>form.submit(), 300);
    }).catch(()=>{ btn.innerHTML='Verify OTP <i class="bi bi-shield-check"></i>'; btn.disabled=false; showErr('Network error.'); });
}

function resendOtp(){
    const email=document.getElementById('lEmail').value.trim(), password=document.getElementById('lPassword').value.trim();
    fetch('/login/send-otp',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({email,password})})
    .then(r=>r.json()).then(d=>{ if(d.error){showErr(d.error);return;} showSuc('OTP resent!'); startOtpTimer(60); startResendTimer(60); });
}

function backToStep1(){
    clearInterval(otpInterval); clearInterval(resendInterval);
    document.getElementById('step1').style.display='block';
    document.getElementById('step2').style.display='none';
    hideAll(); attemptCount=0;
}

function buildOtp(){
    const c=document.getElementById('lOtpRow'); c.innerHTML='';
    for(let i=0;i<6;i++){
        const inp=document.createElement('input');
        inp.className='otp-box'; inp.type='text'; inp.inputMode='numeric'; inp.maxLength=1; inp.autocomplete='off';
        inp.addEventListener('input',function(){ this.value=this.value.replace(/\D/g,''); if(this.value&&i<5)c.children[i+1].focus(); this.classList.toggle('filled',!!this.value); });
        inp.addEventListener('keydown',function(e){ if(e.key==='Backspace'&&!this.value&&i>0)c.children[i-1].focus(); });
        inp.addEventListener('paste',function(e){ e.preventDefault(); const p=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,''); [...c.children].forEach((b,bi)=>{ if(p[bi]){b.value=p[bi];b.classList.add('filled');} }); });
        c.appendChild(inp);
    }
    c.children[0]?.focus();
}

function getOtp(){ return [...document.getElementById('lOtpRow').children].map(b=>b.value).join(''); }

function startOtpTimer(secs){
    clearInterval(otpInterval); const el=document.getElementById('lOtpTimer'); let s=secs;
    el.textContent='01:00'; el.style.color='#e53935';
    otpInterval=setInterval(()=>{ s--; el.textContent=`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`; if(s<=0){clearInterval(otpInterval);el.textContent='Expired';} },1000);
}

function startResendTimer(secs){
    clearInterval(resendInterval); const btn=document.getElementById('lResendBtn'); const te=document.getElementById('lResendTimer');
    btn.disabled=true; let s=secs;
    resendInterval=setInterval(()=>{ s--; te.textContent=`(${s}s)`; if(s<=0){clearInterval(resendInterval);btn.disabled=false;te.textContent='';} },1000);
}

document.addEventListener('keydown',e=>{ if(e.key==='Enter'){ if(document.getElementById('step2').style.display!=='none') submitOtp(); else submitLogin(); } });
window.onload=()=>document.querySelectorAll('input').forEach(el=>el.value='');
</script>
</body>
</html>