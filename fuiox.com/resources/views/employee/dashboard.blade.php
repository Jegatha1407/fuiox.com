@extends('employee.layout')
@section('title', $appInfo['name'] . ' Dashboard')

@section('content')
<div style="padding:32px 24px;max-width:900px;margin:0 auto;">
    <div style="font-size:28px;font-weight:900;color:#1a1a2e;margin-bottom:6px;">{{ $appInfo['icon'] }} {{ $appInfo['name'] }}</div>
    <div style="font-size:14px;color:#888;margin-bottom:32px;">Welcome, {{ $employee->name }}</div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
        @if($assignment->can_flow_builder)
        <a href="{{ route('apps.employee.flow-builder', $appType) }}" style="background:#fff;border:1.5px solid #e5e9f0;border-radius:16px;padding:24px;text-decoration:none;display:block;transition:.2s;" onmouseover="this.style.borderColor='#25d366'" onmouseout="this.style.borderColor='#e5e9f0'">
            <div style="font-size:28px;margin-bottom:10px;">⚡</div>
            <div style="font-size:15px;font-weight:800;color:#1a1a2e;">Flow Builder</div>
            <div style="font-size:12px;color:#888;margin-top:4px;">Build conversation flows</div>
        </a>
        @endif

        @if($assignment->can_resources)
        <a href="{{ route('apps.employee.resources', $appType) }}" style="background:#fff;border:1.5px solid #e5e9f0;border-radius:16px;padding:24px;text-decoration:none;display:block;transition:.2s;" onmouseover="this.style.borderColor='#25d366'" onmouseout="this.style.borderColor='#e5e9f0'">
            <div style="font-size:28px;margin-bottom:10px;">{{ $config['resource_icon'] }}</div>
            <div style="font-size:15px;font-weight:800;color:#1a1a2e;">Manage {{ $config['resource_label'] }}s</div>
            <div style="font-size:12px;color:#888;margin-top:4px;">Add and manage resources</div>
        </a>
        @endif

        @if($assignment->can_records)
        <a href="{{ route('apps.employee.records', $appType) }}" style="background:#fff;border:1.5px solid #e5e9f0;border-radius:16px;padding:24px;text-decoration:none;display:block;transition:.2s;" onmouseover="this.style.borderColor='#25d366'" onmouseout="this.style.borderColor='#e5e9f0'">
            <div style="font-size:28px;margin-bottom:10px;">📋</div>
            <div style="font-size:15px;font-weight:800;color:#1a1a2e;">{{ $config['record_label'] }}s</div>
            <div style="font-size:12px;color:#888;margin-top:4px;">View all bookings & orders</div>
        </a>
        @endif

        <a href="{{ route('apps.employee.change-password') }}" style="background:#fff;border:1.5px solid #e5e9f0;border-radius:16px;padding:24px;text-decoration:none;display:block;transition:.2s;" onmouseover="this.style.borderColor='#25d366'" onmouseout="this.style.borderColor='#e5e9f0'">
            <div style="font-size:28px;margin-bottom:10px;">🔐</div>
            <div style="font-size:15px;font-weight:800;color:#1a1a2e;">Change Password</div>
            <div style="font-size:12px;color:#888;margin-top:4px;">Update your credentials</div>
        </a>
    </div>
</div>
@endsection
