@extends('layouts.app')

@section('title', 'Change Password')
@section('page_title', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card fu-card">
            <div class="card-header"><i class="bi bi-shield-lock-fill me-2 text-success"></i>Change Password</div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('agent.password.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="cur" class="form-label fw-semibold" style="font-size:12px;">Current Password *</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="cur" class="form-control rounded-start-3" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('cur',this)" style="border-radius:0 10px 10px 0;"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="npw" class="form-label fw-semibold" style="font-size:12px;">New Password * <span class="text-muted fw-normal">(min 6 characters)</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="npw" class="form-control rounded-start-3" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('npw',this)" style="border-radius:0 10px 10px 0;"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="cpw" class="form-label fw-semibold" style="font-size:12px;">Confirm New Password *</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="cpw" class="form-control rounded-start-3" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('cpw',this)" style="border-radius:0 10px 10px 0;"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-fu-primary rounded-3 w-100">
                        <i class="bi bi-shield-check me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd(id,btn){
    const inp=document.getElementById(id);
    inp.type=inp.type==='password'?'text':'password';
    btn.innerHTML=inp.type==='password'?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';
}
</script>
@endpush