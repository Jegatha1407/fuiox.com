@extends('employee.layout')
@section('title', 'Change Password')

@section('content')
<div style="padding:32px 24px;max-width:420px;margin:0 auto;">
    <div style="font-size:22px;font-weight:900;color:#1a1a2e;margin-bottom:24px;">🔐 Change Password</div>

    @if(session('success'))
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div style="background:#fdecea;color:#c62828;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('apps.employee.update-password') }}">
        @csrf
        <div style="margin-bottom:16px;">
            <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;">Current Password</label>
            <input type="password" name="current_password" style="width:100%;padding:11px 14px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;box-sizing:border-box;" required>
        </div>
        <div style="margin-bottom:16px;">
            <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;">New Password</label>
            <input type="password" name="password" style="width:100%;padding:11px 14px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;box-sizing:border-box;" required>
        </div>
        <div style="margin-bottom:24px;">
            <label style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;">Confirm New Password</label>
            <input type="password" name="password_confirmation" style="width:100%;padding:11px 14px;border:1.5px solid #e5e9f0;border-radius:9px;font-size:14px;outline:none;box-sizing:border-box;" required>
        </div>
        <button type="submit" style="width:100%;padding:12px;background:#25d366;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;">Update Password</button>
    </form>
</div>
@endsection
