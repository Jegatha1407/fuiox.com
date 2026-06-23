<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — Fuiox Technologies</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --gold: #70966a; --dark: #f8f1f1; --dark-2: #b7dbb3;
            --dark-3: #6c6b52ca; --dark-4: #bfbf8bdf;
            --text: #171817; --text-muted: #141414;
            --success: #1fe847; --error: #E05A5A;
        }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--dark);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem; position: relative; overflow-x: hidden;
        }
        body::before {
            content: ''; position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(201,168,76,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,168,76,0.04) 1px, transparent 1px);
            background-size: 60px 60px; pointer-events: none;
        }
        body::after {
            content: ''; position: fixed;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.07) 0%, transparent 70%);
            top: -250px; right: -250px; pointer-events: none;
        }
        .page-wrapper { width: 100%; max-width: 540px; position: relative; z-index: 1; }
        .brand { text-align: center; margin-bottom: 2.5rem; animation: fadeDown 0.6s ease both; }
        .brand-logo { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 0.6rem; }
        .brand-icon { width: 40px; height: 40px; border: 1.5px solid var(--gold); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .brand-icon svg { width: 20px; height: 20px; fill: var(--gold); }
        .brand-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--text); }
        .brand-name span { color: var(--gold); }
        .brand-tagline { font-size: 0.75rem; color: var(--text-muted); letter-spacing: 0.18em; text-transform: uppercase; }
        .card {
            background: var(--dark-2); border: 1px solid rgba(201,168,76,0.15);
            border-radius: 20px; padding: 2.5rem;
            position: relative; overflow: hidden; animation: fadeUp 0.6s ease 0.1s both;
        }
        .card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }
        .card-header { margin-bottom: 2rem; }
        .card-header h1 { font-family: 'Playfair Display', serif; font-size: 1.75rem; color: var(--text); font-weight: 600; margin-bottom: 0.4rem; }
        .card-header p { font-size: 0.875rem; color: var(--text-muted); font-weight: 300; }
        .gold-line { width: 40px; height: 2px; background: var(--gold); border-radius: 2px; margin: 0.75rem 0 0; }
        .alert { padding: 0.875rem 1rem; border-radius: 10px; font-size: 0.875rem; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 10px; }
        .alert-success { background: rgba(76,175,130,0.1); border: 1px solid rgba(76,175,130,0.3); color: var(--success); }
        .alert-error   { background: rgba(224,90,90,0.1);  border: 1px solid rgba(224,90,90,0.3);  color: var(--error); }
        .alert-icon    { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; font-size: 0.72rem; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; opacity: 0.4; pointer-events: none; fill: var(--gold); }
        .textarea-wrap .input-icon { top: 14px; transform: none; }
        input[type="text"], input[type="email"], input[type="tel"], textarea {
            width: 100%; background: var(--dark-3);
            border: 1px solid rgba(201,168,76,0.15); border-radius: 10px;
            padding: 0.75rem 0.875rem 0.75rem 2.5rem;
            color: var(--text); font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem; outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        textarea { resize: vertical; min-height: 110px; line-height: 1.6; }
        input::placeholder, textarea::placeholder { color: var(--text-muted); opacity: 0.5; }
        input:focus, textarea:focus { border-color: rgba(71, 250, 101, 0.5); background: var(--dark-4); }
        .field-error { font-size: 0.75rem; color: var(--error); margin-top: 0.35rem; padding-left: 2px; }
        .btn-submit {
            width: 100%; padding: 0.9rem;
            background: linear-gradient(135deg, #3ad475, #86a383);
            border: none; border-radius: 10px; color: #beedc8;
            font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
            font-weight: 500; letter-spacing: 0.05em; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 0.5rem; transition: opacity 0.2s, transform 0.15s;
        }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit svg { width: 16px; height: 16px; fill: #0D0D0D; }
        .card-footer {
            margin-top: 1.75rem; padding-top: 1.25rem;
            border-top: 1px solid rgba(105, 219, 102, 0.1);
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-size: 0.75rem; color: var(--text-muted);
        }
        .card-footer svg { width: 13px; height: 13px; fill: var(--gold); opacity: 0.7; }
        @keyframes fadeDown { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeUp   { from { opacity: 0; transform: translateY(20px);  } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 480px) {
            .card { padding: 1.75rem 1.25rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <div class="brand">
        <div class="brand-logo">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <span class="brand-name">Fuiox <span>Technologies</span></span>
        </div>
        <p class="brand-tagline">Get In Touch With Us</p>
    </div>

    <div class="card">

        @if(session('success'))
        <div class="alert alert-success">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

        <div class="card-header">
            <h1>Let's Connect</h1>
            <p>Fill in your details and we'll reach out via WhatsApp & Email</p>
            <div class="gold-line"></div>
        </div>

        <form action="{{ route('appointment.submit') }}" method="POST">
            @csrf
            <div class="form-row">

                <div class="form-group full">
                    <label for="name">Full Name</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="John Smith">
                    </div>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="phone">WhatsApp Number</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 6.29 6.29l.87-.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="919876543210">
                    </div>
                    @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="john@example.com">
                    </div>
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group full">
                    <label for="requirements">Your Requirements</label>
                    <div class="input-wrap textarea-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <textarea id="requirements" name="requirements" placeholder="Tell us what you're looking for...">{{ old('requirements') }}</textarea>
                    </div>
                    @error('requirements') <span class="field-error">{{ $message }}</span> @enderror
                </div>

            </div>

            <button type="submit" class="btn-submit">
                {{-- <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> --}}
                Submit
            </button>
        </form>

        <div class="card-footer">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Your details are safe and secure with Fuiox Technologies
        </div>
    </div>
</div>
</body>
</html>