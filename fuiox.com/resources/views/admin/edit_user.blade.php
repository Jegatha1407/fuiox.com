@extends('layouts.admin')

@section('title', 'Edit User')
@section('page_title', 'Edit User — ' . $user->name)

@push('styles')
<style>
    .form-check-input:checked { background-color: #00e676; border-color: #00e676; }
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .toggle-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('admin.users.update', $user->id) }}">
    @csrf

    <div class="row g-3">

        {{-- ══ BASIC INFO ══ --}}
        <div class="col-12 col-md-6">
            <div class="card shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="bi bi-person-fill me-2 text-primary"></i>Basic Information
                    </h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Organisation</label>
                        <input type="text" class="form-control" name="organisation" value="{{ $user->organisation }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mobile</label>
                        <input type="text" class="form-control" name="mobile" value="{{ $user->mobile }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">
                            New Password
                            <span class="text-muted fw-normal">(leave blank to keep current)</span>
                        </label>
                        <input type="password" class="form-control" name="password" placeholder="New password">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ SETTINGS + TRIAL ══ --}}
        <div class="col-12 col-md-6 d-flex flex-column gap-3">

            {{-- Account Settings --}}
            <div class="card shadow-sm rounded-3">
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="bi bi-gear-fill me-2 text-secondary"></i>Account Settings
                    </h6>
                    <div class="toggle-row">
                        <div>
                            <div class="fw-semibold small">Account Active</div>
                            <div class="text-muted" style="font-size:11px;">User can login and use platform</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" value="1"
                                   {{ $user->is_active ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="fw-semibold small">Blocked</div>
                            <div class="text-muted" style="font-size:11px;">Block user from accessing platform</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_blocked" value="1"
                                   {{ $user->is_blocked ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Free Trial --}}
            <div class="card shadow-sm rounded-3">
                <div class="card-body">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="bi bi-hourglass-split me-2 text-warning"></i>Free Trial
                    </h6>
                    <div class="toggle-row">
                        <div>
                            <div class="fw-semibold small">Enable Free Trial</div>
                            <div class="text-muted" style="font-size:11px;">User gets access until trial ends</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="free_trial_enabled" value="1"
                                   {{ $user->free_trial_enabled ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Trial End Date</label>
                        <input type="datetime-local" class="form-control" name="trial_ends_at"
                               value="{{ $user->trial_ends_at ? \Carbon\Carbon::parse($user->trial_ends_at)->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    @if($user->trial_ends_at)
                    <div class="mt-2 small">
                        @if(\Carbon\Carbon::parse($user->trial_ends_at)->isFuture())
                            <span class="text-primary fw-semibold">
                                <i class="bi bi-clock me-1"></i>
                                {{ \Carbon\Carbon::now()->diffInDays($user->trial_ends_at) }} days remaining
                            </span>
                        @else
                            <span class="text-danger fw-semibold">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Trial expired {{ \Carbon\Carbon::parse($user->trial_ends_at)->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ══ FORM ACTIONS ══ --}}
    <div class="d-flex gap-2 mt-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-light px-4">
            <i class="bi bi-arrow-left me-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-success fw-bold px-4">
            <i class="bi bi-floppy-fill me-1"></i>Save Changes
        </button>
    </div>

</form>

@endsection