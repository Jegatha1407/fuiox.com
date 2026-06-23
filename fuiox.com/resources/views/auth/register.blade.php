<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — Fuiox Technologies</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --green:#25d366; --green-dark:#1fba58; --dark:#0d1117; }
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Plus Jakarta Sans',sans-serif; min-height:100vh; display:flex; }

.auth-left {
    width:420px; flex-shrink:0;
    background:linear-gradient(145deg,#0d2818,#1a4a2e);
    display:flex; flex-direction:column; justify-content:center;
    padding:56px 48px; position:relative; overflow:hidden;
}
.auth-left::before { content:''; position:absolute; top:-80px; right:-80px; width:300px; height:300px; border-radius:50%; background:rgba(37,211,102,.08); pointer-events:none; }
.auth-left::after  { content:''; position:absolute; bottom:-60px; left:-60px; width:220px; height:220px; border-radius:50%; background:rgba(37,211,102,.06); pointer-events:none; }
.auth-left .brand { font-size:20px; font-weight:800; color:#fff; margin-bottom:40px; }
.auth-left .brand span { color:var(--green); }
.auth-left .tagline { font-size:28px; font-weight:800; color:#fff; line-height:1.2; margin-bottom:14px; }
.auth-left .tagline span { color:var(--green); }
.auth-left p { font-size:14px; color:rgba(255,255,255,.5); line-height:1.7; margin-bottom:32px; }
.auth-feature { display:flex; align-items:center; gap:10px; margin-bottom:12px; font-size:14px; color:rgba(255,255,255,.65); }
.auth-feature i { color:var(--green); font-size:15px; }

.auth-right { flex:1; background:#fff; display:flex; align-items:center; justify-content:center; padding:40px 24px; overflow-y:auto; }
.auth-card { width:100%; max-width:460px; }
.back-link { font-size:13px; color:#aaa; display:inline-flex; align-items:center; gap:5px; margin-bottom:24px; transition:color .15s; text-decoration:none; }
.back-link:hover { color:var(--green); }
.auth-card h2 { font-size:24px; font-weight:800; color:#0d1117; margin-bottom:4px; }
.auth-card .sub { font-size:14px; color:#888; margin-bottom:24px; }

.fu-group { margin-bottom:14px; }
.fu-group label { display:block; font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; }
.fu-input { width:100%; padding:11px 14px; border:1.5px solid #e5e9f0; border-radius:10px; font-size:14px; outline:none; font-family:inherit; color:#0d1117; background:#fafafa; transition:border-color .15s, box-shadow .15s; }
.fu-input:focus { border-color:var(--green); box-shadow:0 0 0 3px rgba(37,211,102,.1); background:#fff; }
.pw-wrap { position:relative; }
.pw-wrap .fu-input { padding-right:44px; }
.pw-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#aaa; font-size:16px; }

.btn-fu { width:100%; padding:13px; background:var(--green); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700; font-family:inherit; cursor:pointer; transition:.15s; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn-fu:hover { background:var(--green-dark); }
.btn-fu:disabled { opacity:.6; cursor:not-allowed; }

.fu-alert { font-size:13px; padding:11px 14px; border-radius:9px; margin-bottom:14px; }
.fu-alert.err { background:#fdecea; color:#c62828; border-left:3px solid #e53935; }
.fu-alert.suc { background:#e8f5e9; color:#2e7d32; border-left:3px solid var(--green); }

.pw-hint { font-size:11px; color:#aaa; margin-top:4px; }
.pw-strength { display:flex; gap:4px; margin-top:6px; }
.pw-strength span { flex:1; height:3px; border-radius:2px; background:#eee; transition:.3s; }

.otp-info { background:#e8f5e9; color:#2e7d32; font-size:13px; padding:11px 14px; border-radius:9px; border-left:3px solid var(--green); margin-bottom:16px; }
.otp-row { display:flex; gap:8px; justify-content:center; margin:14px 0 8px; }
.otp-box { width:46px; height:54px; border-radius:10px; border:2px solid #e5e9f0; text-align:center; font-size:22px; font-weight:700; background:#fafafa; outline:none; transition:.2s; font-family:inherit; color:#0d1117; }
.otp-box:focus { border-color:var(--green); background:#fff; box-shadow:0 0 0 3px rgba(37,211,102,.1); }
.otp-box.filled { border-color:var(--green); }

.auth-switch { font-size:13px; color:#888; text-align:center; margin-top:18px; }
.auth-switch a { color:var(--green); font-weight:600; text-decoration:none; }

@media(max-width:900px){ .auth-left { display:none !important; } }
@media(max-width:480px){ .auth-right { padding:24px 16px; } }
</style>
</head>
<body>

<div class="auth-left d-none d-md-flex flex-column">
    <div class="brand">Fuiox <span>Technologies</span></div>
    <div class="tagline">Start growing on <span>WhatsApp</span> today</div>
    <p>Join thousands of businesses using Fuiox to send bulk campaigns, automate replies and manage customer chats.</p>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Free 14-day trial, no credit card</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Official Meta WhatsApp API</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Setup in under 5 minutes</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> Bulk campaigns to thousands</div>
    <div class="auth-feature"><i class="bi bi-check-circle-fill"></i> AI-powered auto replies</div>
</div>

<div class="auth-right">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to home</a>

        {{-- STEP 1: Register Form --}}
        <div id="step1">
            <h2>Create your account</h2>
            <p class="sub">Start your free trial — no credit card required</p>

            @if($errors->any())
                <div class="fu-alert err">{{ $errors->first() }}</div>
            @endif
            @if(session('success'))
                <div class="fu-alert suc">{{ session('success') }}</div>
            @endif

            <div id="jsAlert" style="display:none;" class="fu-alert err"></div>

            <form method="POST" action="{{ route('register') }}" id="regForm">
                @csrf
                <div class="fu-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="fu-input" value="{{ old('name') }}" placeholder="Your full name" required autocomplete="off">
                </div>
                <div class="fu-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="fu-input" value="{{ old('email') }}" placeholder="you@company.com" required autocomplete="off">
                </div>
                <div class="fu-group">
                    <label>Organisation Name *</label>
                    <input type="text" name="organisation" class="fu-input" value="{{ old('organisation') }}" placeholder="Your company or org name" required autocomplete="off">
                </div>
                <div class="fu-group">
                    <label>Password * <span style="font-weight:400;text-transform:none;letter-spacing:0;">(min 8 chars, must include upper, lower, number, symbol)</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="regPwd" class="fu-input" placeholder="Create a strong password" required autocomplete="new-password" oninput="checkPwStrength(this.value)">
                        <button type="button" class="pw-eye" onclick="togglePw('regPwd',this)"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="pw-strength" id="pwStrength">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="pw-hint" id="pwHint">Use uppercase, lowercase, number and special character</div>
                </div>
                <div class="fu-group">
                    <label>Confirm Password *</label>
                    <div class="pw-wrap">
                        <input type="password" name="password_confirmation" id="regPwd2" class="fu-input" placeholder="Repeat your password" required autocomplete="new-password">
                        <button type="button" class="pw-eye" onclick="togglePw('regPwd2',this)"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="fu-group d-flex align-items-start gap-2">
                    <input type="checkbox" name="accept_terms" id="terms" required style="margin-top:3px;width:16px;height:16px;cursor:pointer;accent-color:var(--green);">
                    <label for="terms" style="font-size:13px;color:#666;line-height:1.5;font-weight:400;text-transform:none;letter-spacing:0;">
                        I agree to the <a href="{{ route('terms') }}" target="_blank" style="color:var(--green);font-weight:600;">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" style="color:var(--green);font-weight:600;">Privacy Policy</a>
                    </label>
                </div>
                <button type="submit" class="btn-fu" id="regBtn">
                    Create Account <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>

        {{-- STEP 2: OTP Verification (shown after redirect) --}}
        @if(session('otp_user_id') && request()->routeIs('otp.show'))
        <div id="step2">
            <h2>Verify your email</h2>
            <p class="sub">We sent a 6-digit OTP to your email</p>
            <div class="otp-info">📧 Check your inbox and enter the OTP below</div>
            <form method="POST" action="{{ route('otp.verify') }}">
                @csrf
                <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:4px;">Enter 6-digit OTP</label>
                <input type="text" name="otp" class="fu-input text-center" maxlength="6" placeholder="000000" style="font-size:24px;font-weight:700;letter-spacing:8px;" required>
                <button type="submit" class="btn-fu mt-3">Verify Email <i class="bi bi-shield-check"></i></button>
            </form>
        </div>
        @endif

        <div class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.innerHTML = inp.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
}

function checkPwStrength(pw) {
    const bars = document.querySelectorAll('#pwStrength span');
    const hint = document.getElementById('pwHint');
    let score = 0;
    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[@$!%*?&_#^()\-+=]/.test(pw)) score++;
    const colors = ['#e53935','#f57c00','#fbc02d','#25d366'];
    const labels = ['Weak','Fair','Good','Strong'];
    bars.forEach((b, i) => { b.style.background = i < score ? colors[score-1] : '#eee'; });
    hint.textContent = score === 0 ? 'Use uppercase, lowercase, number and special character' : labels[score-1];
    hint.style.color = score > 0 ? colors[score-1] : '#aaa';
}

document.getElementById('regForm')?.addEventListener('submit', function(e) {
    const pwd = document.getElementById('regPwd').value;
    const pwd2 = document.getElementById('regPwd2').value;
    const alert = document.getElementById('jsAlert');
    if (pwd !== pwd2) {
        e.preventDefault();
        alert.textContent = 'Passwords do not match.';
        alert.style.display = 'block';
        return;
    }
    const btn = document.getElementById('regBtn');
    btn.innerHTML = 'Creating account… <i class="bi bi-hourglass-split"></i>';
    btn.disabled = true;
});

window.onload = () => document.querySelectorAll('input').forEach(el => el.value = '');
</script>
</body>
</html>