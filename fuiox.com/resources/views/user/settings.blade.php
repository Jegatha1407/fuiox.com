@extends('layouts.app')

@section('title', 'Settings')
@section('page_title', 'Account Settings')

@section('page_styles')
/* Tab nav */
.settings-nav .nav-link { color:#666; font-weight:500; font-size:14px; padding:10px 18px; border-bottom:2px solid transparent; border-radius:0; transition:0.15s; }
.settings-nav .nav-link:hover { color:#25d366; }
.settings-nav .nav-link.active { color:#25d366; border-bottom-color:#25d366; font-weight:600; }

/* Avatar */
.user-avatar-lg { width:72px; height:72px; border-radius:50%; background:#25d366; color:#fff; font-size:26px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* Info rows */
.info-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f5f5f5; font-size:14px; }
.info-row:last-child { border-bottom:none; }
.info-row .label { color:#888; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.3px; }
.info-row .value { color:#1a1a2e; font-weight:500; text-align:right; max-width:60%; word-break:break-all; }

/* Connection status */
.conn-status { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; }
.conn-status.connected    { background:#e8f5e9; color:#2e7d32; }
.conn-status.disconnected { background:#fdecea; color:#c62828; }

/* Toggle switch */
.fu-switch { position:relative; width:42px; height:23px; display:inline-block; }
.fu-switch input { display:none; }
.fu-switch-slider { position:absolute; inset:0; background:#ddd; border-radius:23px; cursor:pointer; transition:0.3s; }
.fu-switch-slider::before { content:''; position:absolute; width:17px; height:17px; left:3px; top:3px; background:#fff; border-radius:50%; transition:0.3s; }
.fu-switch input:checked + .fu-switch-slider { background:#25d366; }
.fu-switch input:checked + .fu-switch-slider::before { transform:translateX(19px); }

/* Masked token */
.token-mask { letter-spacing:3px; color:#aaa; font-family:monospace; }
.copy-btn { background:none; border:1px solid #e5e5e5; cursor:pointer; color:#666; font-size:11px; padding:3px 9px; border-radius:5px; font-family:inherit; transition:0.15s; }
.copy-btn:hover { background:#f0f0f0; }

/* API key box */
.api-key-box { background:#f6f8fa; border:1px solid #e5e5e5; border-radius:10px; padding:13px 16px; font-family:monospace; font-size:13px; word-break:break-all; color:#1a1a2e; }

/* Request boxes */
.req-box { border-radius:10px; padding:14px 16px; }
.req-box.pending  { background:#fff3e0; border:1.5px solid #f57c00; }
.req-box.rejected { background:#fdecea; border:1.5px solid #e53935; }
.req-box.default  { background:#fff8e1; border:1.5px solid #ffe082; }

/* Fetch preview */
.fetch-preview { background:#e8f5e9; border:1.5px solid #c8e6c9; border-radius:10px; padding:14px; display:none; margin-top:14px; }
.fetch-preview.show { display:block; }
.fetch-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #c8e6c9; font-size:13px; }
.fetch-row:last-child { border-bottom:none; }
.fetch-key { color:#666; }
.fetch-val { font-weight:600; color:#1a1a2e; font-family:monospace; }

/* Plan card */
.plan-card { background:linear-gradient(135deg,#1a1a2e,#2d2d4e); border-radius:14px; padding:22px; color:#fff; }
.plan-name-lg { font-size:22px; font-weight:800; color:#25d366; }
.plan-sub { font-size:13px; color:rgba(255,255,255,0.5); margin-top:3px; }
.plan-detail-box { background:rgba(255,255,255,0.08); border-radius:8px; padding:12px; text-align:center; }
.plan-detail-label { font-size:10px; color:rgba(255,255,255,0.5); text-transform:uppercase; font-weight:700; }
.plan-detail-value { font-size:20px; font-weight:800; color:#fff; margin-top:3px; }
@endsection

@section('content')

<!-- Tab Nav -->
<div class="card fu-card mb-4 overflow-hidden">
    <div class="d-flex border-bottom settings-nav overflow-auto" id="settingsTabNav" style="gap:0;">
        <button class="nav-link active" onclick="settTab('profile',this)"><i class="bi bi-person me-2"></i>Profile</button>
        <button class="nav-link" onclick="settTab('channel',this)"><i class="bi bi-whatsapp me-2"></i>Channel</button>
        <button class="nav-link" onclick="settTab('security',this)"><i class="bi bi-shield-lock me-2"></i>Security</button>
        <button class="nav-link" onclick="settTab('billing',this)"><i class="bi bi-credit-card me-2"></i>Billing</button>
    </div>
</div>

<!-- ── PROFILE TAB ── -->
<div id="settTabProfile">
    <div class="card fu-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Personal Information</span>
            <button class="btn btn-sm btn-outline-secondary rounded-pill" id="editProfileBtn" onclick="settToggleEdit()">
                <i class="bi bi-pencil me-1"></i>Edit
            </button>
        </div>
        <div class="card-body">
            <!-- Avatar + name -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="user-avatar-lg">{{ substr($user->name,0,1) }}</div>
                <div>
                    <div class="fw-bold" style="font-size:18px;color:#1a1a2e;">{{ $user->name }}</div>
                    <div class="text-muted" style="font-size:13px;">{{ $user->email }}</div>
                    <div class="text-muted" style="font-size:13px;">{{ $user->organisation }}</div>
                </div>
            </div>

            <!-- Read-only view -->
            <div id="profileView">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row"><span class="label">Full Name</span><span class="value">{{ $user->name }}</span></div>
                        <div class="info-row"><span class="label">Email</span><span class="value">{{ $user->email }}</span></div>
                        <div class="info-row"><span class="label">Organisation</span><span class="value">{{ $user->organisation ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Mobile</span><span class="value">{{ $user->mobile ? '+91 '.$user->mobile : '—' }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row"><span class="label">Address</span><span class="value">{{ $user->address ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">City</span><span class="value">{{ $user->city ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">State</span><span class="value">{{ $user->state ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Country</span><span class="value">{{ $user->country ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Pincode</span><span class="value">{{ $user->pincode ?? '—' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Edit form -->
            <form id="profileForm" action="{{ route('settings.update') }}" method="POST" style="display:none;">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="settName" class="form-label fw-semibold" style="font-size:12px;">Full Name *</label>
                        <input type="text" id="settName" name="name" value="{{ $user->name }}" class="form-control rounded-3" required autocomplete="name">
                    </div>
                    <div class="col-md-6">
                        <label for="sEmail" class="form-label fw-semibold" style="font-size:12px;">Email <span class="text-muted fw-normal">(read only)</span></label>
                        <input type="email" id="sEmail" value="{{ $user->email }}" class="form-control rounded-3" disabled autocomplete="email">
                    </div>
                    <div class="col-md-6">
                        <label for="settOrg" class="form-label fw-semibold" style="font-size:12px;">Organisation</label>
                        <input type="text" id="settOrg" name="organisation" value="{{ $user->organisation }}" class="form-control rounded-3" placeholder="Business name" autocomplete="organization">
                    </div>
                    <div class="col-md-6">
                        <label for="settMobile" class="form-label fw-semibold" style="font-size:12px;">Mobile</label>
                        <input type="text" id="settMobile" name="mobile" value="{{ $user->mobile }}" class="form-control rounded-3" placeholder="9876543210" autocomplete="tel">
                    </div>
                    <div class="col-12">
                        <label for="settAddress" class="form-label fw-semibold" style="font-size:12px;">Address</label>
                        <textarea id="settAddress" name="address" class="form-control rounded-3" rows="2" placeholder="Street address" autocomplete="street-address">{{ $user->address }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="settCity" class="form-label fw-semibold" style="font-size:12px;">City</label>
                        <input type="text" id="settCity" name="city" value="{{ $user->city }}" class="form-control rounded-3" placeholder="City" autocomplete="address-level2">
                    </div>
                    <div class="col-md-6">
                        <label for="settState" class="form-label fw-semibold" style="font-size:12px;">State</label>
                        <input type="text" id="settState" name="state" value="{{ $user->state }}" class="form-control rounded-3" placeholder="State" autocomplete="address-level1">
                    </div>
                    <div class="col-md-6">
                        <label for="settCountry" class="form-label fw-semibold" style="font-size:12px;">Country</label>
                        <input type="text" id="settCountry" name="country" value="{{ $user->country }}" class="form-control rounded-3" placeholder="Country" autocomplete="country-name">
                    </div>
                    <div class="col-md-6">
                        <label for="settPincode" class="form-label fw-semibold" style="font-size:12px;">Pincode</label>
                        <input type="text" id="settPincode" name="pincode" value="{{ $user->pincode }}" class="form-control rounded-3" placeholder="Pincode" autocomplete="postal-code">
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-fu-primary rounded-3"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                        <button type="button" class="btn btn-light rounded-3" onclick="settToggleEdit()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── CHANNEL TAB ── -->
<div id="settTabChannel" style="display:none;">
    <!-- Status card -->
    <div class="card fu-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>WhatsApp Connection</span>
            @if($user->phone_number_id && $user->access_token)
                <span class="conn-status connected"><i class="bi bi-check-circle-fill"></i>Connected</span>
            @else
                <span class="conn-status disconnected"><i class="bi bi-x-circle-fill"></i>Not Connected</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="label">WhatsApp Number</span>
                        <span class="value">{{ $user->mobile ? '+91 '.$user->mobile : '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone Number ID</span>
                        <span class="value d-flex align-items-center gap-2">
                            <span>{{ $user->phone_number_id ? substr($user->phone_number_id,0,8).'…' : '—' }}</span>
                            @if($user->phone_number_id)
                                <button class="copy-btn" onclick="settCopy('{{ $user->phone_number_id }}')">Copy</button>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Business Account ID</span>
                        <span class="value d-flex align-items-center gap-2">
                            <span>{{ $user->business_account_id ? substr($user->business_account_id,0,8).'…' : '—' }}</span>
                            @if($user->business_account_id)
                                <button class="copy-btn" onclick="settCopy('{{ $user->business_account_id }}')">Copy</button>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="label">Access Token</span>
                        <span class="value d-flex align-items-center gap-2">
                            @if($user->access_token)
                                <span class="token-mask">••••••••••••••••</span>
                                <button class="copy-btn" onclick="settCopy('{{ $user->access_token }}')">Copy</button>
                            @else
                                <span>—</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Webhook URL</span>
                        <span class="value d-flex align-items-center gap-2">
                            <span style="font-family:monospace;font-size:12px;">{{ url('/webhook') }}</span>
                            <button class="copy-btn" onclick="settCopy('{{ url('/webhook') }}')">Copy</button>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Verify Token</span>
                        <span class="value d-flex align-items-center gap-2">
                            <span style="font-family:monospace;">fuiox_webhook_verify</span>
                            <button class="copy-btn" onclick="settCopy('fuiox_webhook_verify')">Copy</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Credentials -->
    <div class="card fu-card">
        <div class="card-header">Update Credentials</div>
        <div class="card-body">
            @php
                $credRequest = \Illuminate\Support\Facades\DB::table('credential_requests')
                    ->where('user_id',$user->id)->orderByDesc('created_at')->first();
                $canEdit    = !$user->phone_number_id || ($credRequest && $credRequest->status === 'accepted');
                $hasPending = $credRequest && $credRequest->status === 'pending';
                $wasRejected= $credRequest && $credRequest->status === 'rejected';
            @endphp

            @if($canEdit)
            <!-- Fetch form -->
            <div class="p-3 rounded-3 mb-3" style="background:#f9fafb;border:1.5px dashed #e5e5e5;">
                <div class="fw-bold mb-1" style="font-size:14px;">🔗 Connect WhatsApp</div>
                <p class="text-muted mb-3" style="font-size:13px;">Enter your Access Token and WABA ID to auto-fetch details.</p>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="settToken" class="form-label fw-semibold" style="font-size:12px;">Access Token *</label>
                        <input type="text" id="settToken" class="form-control rounded-3" placeholder="EAAxxxxxxxxxxxxxxx…" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="settWaba" class="form-label fw-semibold" style="font-size:12px;">WABA ID *</label>
                        <input type="text" id="settWaba" class="form-control rounded-3" placeholder="e.g. 1175723170960075" autocomplete="off">
                        <div class="form-text">Find in business.facebook.com → Settings</div>
                    </div>
                </div>
                <button class="btn btn-fu-primary rounded-3" id="settFetchBtn" onclick="settFetch()">
                    <i class="bi bi-search me-1"></i>Fetch Details
                </button>
                <div id="settFetchError" class="alert alert-danger d-none rounded-3 py-2 mt-2" style="font-size:13px;"></div>

                <div class="fetch-preview" id="settFetchPreview">
                    <div class="fw-bold text-success mb-2" style="font-size:13px;"><i class="bi bi-check-circle me-1"></i>Account found — confirm details</div>
                    <div class="fetch-row"><span class="fetch-key">Display Number</span><span class="fetch-val" id="fpDisplay">—</span></div>
                    <div class="fetch-row"><span class="fetch-key">Business Name</span><span class="fetch-val" id="fpName">—</span></div>
                    <div class="fetch-row"><span class="fetch-key">Phone Number ID</span><span class="fetch-val" id="fpPhoneId">—</span></div>
                    <div class="fetch-row"><span class="fetch-key">Business Account ID</span><span class="fetch-val" id="fpWabaId">—</span></div>
                    <form method="POST" action="{{ route('setup.update') }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="access_token" id="fpHToken">
                        <input type="hidden" name="phone_number_id" id="fpHPhoneId">
                        <input type="hidden" name="business_account_id" id="fpHWabaId">
                        <input type="hidden" name="mobile" id="fpHMobile">
                        <button type="submit" class="btn btn-fu-primary rounded-3">
                            <i class="bi bi-check-lg me-1"></i>Confirm & Connect
                        </button>
                    </form>
                </div>
            </div>

            @elseif($hasPending)
            <div class="req-box pending">
                <div class="fw-bold" style="font-size:14px;color:#e65100;"><i class="bi bi-clock me-1"></i>Update Request Pending</div>
                <div class="text-muted mt-1" style="font-size:13px;">Your request is waiting for admin approval.</div>
            </div>

            @elseif($wasRejected)
            <div class="req-box rejected mb-3">
                <div class="fw-bold" style="font-size:14px;color:#c62828;"><i class="bi bi-x-circle me-1"></i>Request Rejected</div>
                <div class="text-muted mt-1" style="font-size:13px;">Your previous request was rejected.</div>
            </div>
            <button class="btn btn-outline-primary btn-sm rounded-pill mb-2" onclick="document.getElementById('settRetryForm').style.display='block';this.style.display='none'">
                <i class="bi bi-arrow-clockwise me-1"></i>Send New Request
            </button>
            <form id="settRetryForm" method="POST" action="{{ route('credential.request.submit') }}" style="display:none;">
                @csrf
                <div class="mb-2">
                    <label for="settReason1" class="form-label fw-semibold" style="font-size:12px;">Reason *</label>
                    <textarea id="settReason1" name="reason" class="form-control rounded-3" rows="3" placeholder="Why do you need to update your credentials?" required></textarea>
                </div>
                <button type="submit" class="btn btn-fu-primary rounded-3"><i class="bi bi-send me-1"></i>Send Request</button>
            </form>

            @else
            <div class="req-box default mb-3">
                <div class="fw-semibold" style="font-size:14px;color:#e65100;">Want to update credentials?</div>
                <div class="text-muted mt-1 mb-3" style="font-size:13px;">Send a request to admin with reason for update.</div>
                <form method="POST" action="{{ route('credential.request.submit') }}">
                    @csrf
                    <div class="mb-2">
                        <label for="settReason2" class="form-label fw-semibold" style="font-size:12px;">Reason *</label>
                        <textarea id="settReason2" name="reason" class="form-control rounded-3" rows="3" placeholder="e.g. My token expired, changed WhatsApp Business account…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-fu-primary rounded-3"><i class="bi bi-send me-1"></i>Request Update Permission</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ── SECURITY TAB ── -->
<div id="settTabSecurity" style="display:none;">
    <div class="row g-4">
        <div class="col-lg-6">
            <!-- Change Password -->
            <div class="card fu-card">
                <div class="card-header">Change Password</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="settNewPwd" class="form-label fw-semibold" style="font-size:12px;">New Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="settNewPwd" class="form-control rounded-start-3" placeholder="Min 6 characters">
                                <button type="button" class="btn btn-outline-secondary" onclick="settTogglePwd('settNewPwd',this)" style="border-radius:0 10px 10px 0;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="settConfPwd" class="form-label fw-semibold" style="font-size:12px;">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="settConfPwd" class="form-control rounded-start-3" placeholder="Repeat new password">
                                <button type="button" class="btn btn-outline-secondary" onclick="settTogglePwd('settConfPwd',this)" style="border-radius:0 10px 10px 0;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-fu-primary rounded-3 w-100"><i class="bi bi-shield-check me-1"></i>Update Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Security Settings -->
            <div class="card fu-card mb-3">
                <div class="card-header">Security Settings</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            <div class="fw-semibold" style="font-size:14px;">Two-Factor Authentication</div>
                            <div class="text-muted" style="font-size:12px;">Extra security with OTP on login</div>
                        </div>
                        <label class="fu-switch" aria-label="Toggle"><input type="checkbox" checked><span class="fu-switch-slider"></span></label>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-semibold" style="font-size:14px;">Login Notifications</div>
                            <div class="text-muted" style="font-size:12px;">Email alerts when account is accessed</div>
                        </div>
                        <label class="fu-switch" aria-label="Toggle"><input type="checkbox" checked><span class="fu-switch-slider"></span></label>
                    </div>
                </div>
            </div>

            <!-- API Key -->
            <div class="card fu-card">
                <div class="card-header">API Key</div>
                <div class="card-body">
                    @if($user->api_key)
                        <div class="api-key-box mb-3">
                            <span class="token-mask">{{ str_repeat('•',24) }}</span>{{ substr($user->api_key,-8) }}
                            <button class="copy-btn ms-2" onclick="settCopy('{{ $user->api_key }}')">Copy</button>
                        </div>
                    @else
                        <div class="text-muted mb-3" style="font-size:13px;">No API key generated yet.</div>
                    @endif
                    <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="settRegenKey()">
                        <i class="bi bi-arrow-clockwise me-1"></i>{{ $user->api_key ? 'Regenerate' : 'Generate' }} API Key
                    </button>
                    <div class="text-muted mt-2" style="font-size:12px;">Use this key to integrate Fuiox with your own applications.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── BILLING TAB ── -->
<div id="settTabBilling" style="display:none;">
    <div id="settBillingContent">
        <div class="text-center text-muted py-5">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <div>Loading billing details…</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── TABS ── */
function settTab(name, el) {
    ['Profile','Channel','Security','Billing'].forEach(t => document.getElementById('settTab'+t).style.display='none');
    document.querySelectorAll('.settings-nav .nav-link').forEach(b => b.classList.remove('active'));
    document.getElementById('settTab'+name.charAt(0).toUpperCase()+name.slice(1)).style.display='block';
    el.classList.add('active');
    if (name === 'billing') settLoadBilling();
}

/* ── PROFILE EDIT ── */
function settToggleEdit() {
    const form = document.getElementById('profileForm');
    const view = document.getElementById('profileView');
    const btn  = document.getElementById('editProfileBtn');
    const showing = form.style.display !== 'none';
    form.style.display = showing ? 'none' : 'block';
    view.style.display = showing ? 'block' : 'none';
    btn.innerHTML = showing ? '<i class="bi bi-pencil me-1"></i>Edit' : '<i class="bi bi-x me-1"></i>Cancel';
}

/* ── FETCH WHATSAPP ── */
function settFetch() {
    const token = document.getElementById('settToken').value.trim();
    const waba  = document.getElementById('settWaba').value.trim();
    const err   = document.getElementById('settFetchError');
    err.classList.add('d-none');
    if (!token) { err.textContent='Please enter your access token.'; err.classList.remove('d-none'); return; }
    if (!waba)  { err.textContent='Please enter your WABA ID.';      err.classList.remove('d-none'); return; }
    const btn = document.getElementById('settFetchBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fetching…';
    document.getElementById('settFetchPreview').classList.remove('show');
    fetch('/setup/fetch-from-token', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({ access_token:token, waba_id:waba })
    }).then(r=>r.json()).then(data => {
        btn.disabled=false; btn.innerHTML='<i class="bi bi-search me-1"></i>Fetch Details';
        if (data.error) { err.textContent='❌ '+data.error; err.classList.remove('d-none'); return; }
        document.getElementById('fpDisplay').textContent = data.display_phone;
        document.getElementById('fpName').textContent    = data.verified_name || '—';
        document.getElementById('fpPhoneId').textContent = data.phone_number_id;
        document.getElementById('fpWabaId').textContent  = data.business_account_id;
        document.getElementById('fpHToken').value        = token;
        document.getElementById('fpHPhoneId').value      = data.phone_number_id;
        document.getElementById('fpHWabaId').value       = data.business_account_id;
        document.getElementById('fpHMobile').value       = data.mobile;
        document.getElementById('settFetchPreview').classList.add('show');
    }).catch(() => {
        btn.disabled=false; btn.innerHTML='<i class="bi bi-search me-1"></i>Fetch Details';
        err.textContent='❌ Connection error. Please try again.'; err.classList.remove('d-none');
    });
}

/* ── PASSWORD TOGGLE ── */
function settTogglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

/* ── REGEN API KEY ── */
function settRegenKey() {
    fetch('/api/generate-key', {
        method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
    }).then(r=>r.json()).then(d => {
        if (d.key) { showToast('✅ API key generated!','success'); setTimeout(()=>location.reload(),1200); }
        else showToast('❌ Failed to generate key','error');
    });
}

/* ── BILLING ── */
function settLoadBilling() {
    fetch('/billing/current').then(r=>r.json()).then(data => {
        const wrap = document.getElementById('settBillingContent');
        if (!data.plan) {
            wrap.innerHTML = `<div class="card fu-card"><div class="card-body text-center py-5">
                <div style="font-size:56px;margin-bottom:16px;">💳</div>
                <h5 class="fw-bold mb-2">No Active Plan</h5>
                <p class="text-muted mb-4" style="font-size:14px;">Subscribe to a plan to unlock all features.</p>
                <a href="${window.location.origin}/billing" class="btn btn-fu-primary rounded-pill px-4"><i class="bi bi-credit-card me-1"></i>View Plans</a>
            </div></div>`; return;
        }
        const expires = (data.expires_at||'').substring(0,10);
        const days    = data.days_left || 0;
        wrap.innerHTML = `
        <div class="plan-card mb-4">
            <div class="plan-name-lg">${escHtml(data.plan.name)} Plan</div>
            <div class="plan-sub">Status: ${(data.status||'').toUpperCase()} · Expires: ${expires} · ${days} days remaining</div>
            <div class="row g-3 mt-2">
                <div class="col-4"><div class="plan-detail-box"><div class="plan-detail-label">Messages/mo</div><div class="plan-detail-value">${data.plan.messages_limit>=99999?'∞':parseInt(data.plan.messages_limit).toLocaleString()}</div></div></div>
                <div class="col-4"><div class="plan-detail-box"><div class="plan-detail-label">Contacts</div><div class="plan-detail-value">${data.plan.contacts_limit>=99999?'∞':parseInt(data.plan.contacts_limit).toLocaleString()}</div></div></div>
                <div class="col-4"><div class="plan-detail-box"><div class="plan-detail-label">Team</div><div class="plan-detail-value">${data.plan.team_limit>=99999?'∞':data.plan.team_limit}</div></div></div>
            </div>
        </div>
        <div class="card fu-card">
            <div class="card-header">Invoice History</div>
            <div class="card-body p-0" id="settInvoiceList"><div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading…</div></div>
        </div>`;
        fetch('/billing/invoices').then(r=>r.json()).then(d=>{
            const invs = d.invoices||[];
            const box  = document.getElementById('settInvoiceList');
            if (!invs.length) { box.innerHTML='<div class="text-center text-muted py-4" style="font-size:13px;">No invoices yet.</div>'; return; }
            box.innerHTML = `<div class="table-responsive"><table class="table mb-0" style="font-size:13px;">
                <thead><tr style="background:#fafafa;"><th class="px-4 py-3" style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Plan</th><th class="px-4 py-3" style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Amount</th><th class="px-4 py-3" style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Status</th><th class="px-4 py-3" style="font-size:11px;color:#888;font-weight:700;text-transform:uppercase;">Date</th></tr></thead>
                <tbody>${invs.map(i=>`<tr><td class="px-4 py-3">${escHtml(i.plan_name)}</td><td class="px-4 py-3">₹${parseFloat(i.amount||0).toLocaleString()}</td><td class="px-4 py-3"><span class="badge rounded-pill ${i.status==='active'?'bg-success':'bg-secondary'}">${escHtml(i.status)}</span></td><td class="px-4 py-3 text-muted">${(i.created_at||'').substring(0,10)}</td></tr>`).join('')}</tbody>
            </table></div>`;
        }).catch(()=>{});
    }).catch(()=>{});
}

/* ── COPY ── */
function settCopy(text) {
    navigator.clipboard.writeText(text).then(()=>showToast('✅ Copied!','success'));
}
</script>
@endpush