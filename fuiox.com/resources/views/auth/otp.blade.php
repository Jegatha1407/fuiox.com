<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Email — Fuiox Technologies</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --green:#25d366; --green-dark:#1fba58; }
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Plus Jakarta Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#f0fdf4,#e8f5e9); padding:24px; }
.card { background:#fff; border-radius:20px; padding:40px 36px; width:100%; max-width:440px; box-shadow:0 8px 40px rgba(0,0,0,0.08); text-align:center; }
.icon-wrap { width:64px; height:64px; border-radius:50%; background:#e8f5e9; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:28px; }
h2 { font-size:24px; font-weight:800; color:#0d1117; margin-bottom:6px; }
.sub { font-size:14px; color:#888; margin-bottom:24px; }
.otp-info { background:#e8f5e9; color:#2e7d32; font-size:13px; padding:11px 14px; border-radius:9px; border-left:3px solid var(--green); margin-bottom:20px; text-align:left; }
.otp-row { display:flex; gap:10px; justify-content:center; margin-bottom:8px; }
.otp-box { width:50px; height:58px; border-radius:12px; border:2px solid #e5e9f0; text-align:center; font-size:24px; font-weight:700; background:#fafafa; outline:none; transition:.2s; font-family:inherit; color:#0d1117; }
.otp-box:focus { border-color:var(--green); background:#fff; box-shadow:0 0 0 3px rgba(37,211,102,.1); }
.otp-box.filled { border-color:var(--green); }
.timer { font-size:13px; color:#aaa; margin-bottom:16px; }
.timer span { color:#e53935; font-weight:700; }
.btn-fu { width:100%; padding:13px; background:var(--green); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700; font-family:inherit; cursor:pointer; transition:.15s; margin-bottom:12px; }
.btn-fu:hover { background:var(--green-dark); }
.btn-fu:disabled { opacity:.6; cursor:not-allowed; }
.fu-alert { font-size:13px; padding:11px 14px; border-radius:9px; margin-bottom:14px; }
.fu-alert.err { background:#fdecea; color:#c62828; border-left:3px solid #e53935; }
.fu-alert.suc { background:#e8f5e9; color:#2e7d32; border-left:3px solid var(--green); }
.resend-btn { background:none; border:none; color:var(--green); font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; text-decoration:underline; }
.resend-btn:disabled { color:#aaa; cursor:not-allowed; text-decoration:none; }
.back-link { font-size:13px; color:#aaa; display:inline-flex; align-items:center; gap:4px; margin-top:14px; text-decoration:none; }
.back-link:hover { color:var(--green); }
</style>
</head>
<body>
<div class="card">
    <div class="icon-wrap">📧</div>
    <h2>Verify your email</h2>
    <p class="sub">Enter the 6-digit OTP sent to <strong>{{ $email ?? 'your email' }}</strong></p>

    @if($errors->any())
        <div class="fu-alert err">{{ $errors->first() }}</div>
    @endif
    @if(session('resent'))
        <div class="fu-alert suc">{{ session('resent') }}</div>
    @endif

    <div class="otp-info">
        ✉️ Check your inbox (and spam folder). OTP expires in <span id="otpTimer" style="color:#e53935;font-weight:700;">1:00</span>
    </div>

    <form method="POST" action="{{ route('otp.verify') }}" id="otpForm">
        @csrf
        <div class="otp-row" id="otpRow"></div>
        <input type="hidden" name="otp" id="otpHidden">
        <button type="submit" class="btn-fu" id="btnVerify" disabled>
            Verify Email <i class="bi bi-shield-check"></i>
        </button>
    </form>

    <div>
        <form method="POST" action="{{ route('otp.resend') }}" id="resendForm" style="display:inline;">
            @csrf
            <button type="submit" class="resend-btn" id="resendBtn" disabled>
                Resend OTP <span id="resendTimer"></span>
            </button>
        </form>
    </div>

    <a href="{{ route('register') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to register</a>
</div>

<script>
// Build OTP boxes
const otpRow = document.getElementById('otpRow');
for(let i=0;i<6;i++){
    const inp = document.createElement('input');
    inp.className='otp-box'; inp.type='text'; inp.inputMode='numeric'; inp.maxLength=1; inp.autocomplete='off';
    inp.addEventListener('input',function(){
        this.value=this.value.replace(/\D/g,'');
        if(this.value&&i<5) otpRow.children[i+1].focus();
        this.classList.toggle('filled',!!this.value);
        updateHidden();
    });
    inp.addEventListener('keydown',function(e){
        if(e.key==='Backspace'&&!this.value&&i>0) otpRow.children[i-1].focus();
    });
    inp.addEventListener('paste',function(e){
        e.preventDefault();
        const p=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'');
        [...otpRow.children].forEach((b,bi)=>{ if(p[bi]){b.value=p[bi];b.classList.add('filled');} });
        updateHidden();
    });
    otpRow.appendChild(inp);
}
otpRow.children[0].focus();

function updateHidden(){
    const otp=[...otpRow.children].map(b=>b.value).join('');
    document.getElementById('otpHidden').value=otp;
    document.getElementById('btnVerify').disabled=otp.length!==6;
}

// OTP countdown timer
let secs=60;
const timerEl=document.getElementById('otpTimer');
const t=setInterval(()=>{
    secs--;
    const m=Math.floor(secs/60), s=secs%60;
    timerEl.textContent=`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    if(secs<=0){clearInterval(t);timerEl.textContent='Expired';}
},1000);

// Resend timer
let resendSecs=60;
const resendBtn=document.getElementById('resendBtn');
const resendTimer=document.getElementById('resendTimer');
const rt=setInterval(()=>{
    resendSecs--;
    resendTimer.textContent=`(${resendSecs}s)`;
    if(resendSecs<=0){clearInterval(rt);resendBtn.disabled=false;resendTimer.textContent='';}
},1000);
</script>
</body>
</html>