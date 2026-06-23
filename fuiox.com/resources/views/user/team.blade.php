@extends('layouts.app')

@section('title', 'Team')
@section('page_title', 'Team Management')

@section('page_styles')
/* Stats */
.team-stat { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border-left:4px solid #25d366; display:flex; align-items:center; gap:14px; }
.team-stat.blue   { border-left-color:#1976d2; }
.team-stat.orange { border-left-color:#f57c00; }
.team-stat.purple { border-left-color:#7b1fa2; }
.team-stat-icon  { font-size:26px; }
.team-stat-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px; }
.team-stat-value { font-size:24px; font-weight:800; color:#1a1a2e; }

/* Member cards */
.member-card { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:1.5px solid #f0f0f0; transition:0.2s; height:100%; }
.member-card:hover { border-color:#25d366; box-shadow:0 4px 20px rgba(37,211,102,0.1); transform:translateY(-2px); }
.member-card-body { padding:22px 20px; text-align:center; }
.member-avatar { width:64px; height:64px; border-radius:50%; background:#25d366; color:#fff; font-size:22px; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; position:relative; }
.member-avatar.agent   { background:#1976d2; }
.member-avatar.manager { background:#f57c00; }
.member-avatar.admin   { background:#7b1fa2; }
.member-online-dot { position:absolute; bottom:2px; right:2px; width:12px; height:12px; border-radius:50%; background:#25d366; border:2px solid #fff; }
.member-online-dot.offline { background:#bbb; }
.member-name { font-size:15px; font-weight:700; color:#1a1a2e; margin-bottom:4px; }
.member-email { font-size:12px; color:#888; margin-bottom:10px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.member-role-badge { font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; display:inline-block; margin-bottom:12px; }
.member-role-badge.agent   { background:#e3f2fd; color:#1565c0; }
.member-role-badge.manager { background:#fff3e0; color:#e65100; }
.member-role-badge.admin   { background:#f3e5f5; color:#6a1b9a; }
.member-role-badge.owner { background:#e8f5e9; color:#2e7d32; }
.member-status-badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
.member-status-badge.active   { background:#e8f5e9; color:#2e7d32; }
.member-status-badge.inactive { background:#fdecea; color:#c62828; }
.member-card-footer { padding:12px 16px; border-top:1px solid #f5f5f5; display:flex; align-items:center; justify-content:space-between; gap:8px; }
.member-last-seen { font-size:11px; color:#aaa; }

/* Table view */
.team-table th { font-size:12px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.3px; background:#fafafa; border-bottom:2px solid #f0f0f0; padding:11px 16px; white-space:nowrap; }
.team-table td { font-size:13px; color:#333; padding:13px 16px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.team-table tbody tr:hover td { background:#fafafa; }
.member-av-sm { width:36px; height:36px; border-radius:50%; color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* Role select */
.role-select { font-size:12px; font-weight:600; border:1px solid #e5e5e5; border-radius:6px; padding:3px 8px; background:#fff; cursor:pointer; }

/* View toggle */
.view-toggle-btn { width:34px; height:34px; border:1.5px solid #e5e5e5; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; background:#fff; color:#888; transition:0.15s; font-size:16px; }
.view-toggle-btn.active, .view-toggle-btn:hover { border-color:#25d366; color:#25d366; background:#f0fdf4; }

/* Invite section */
.invite-card { background:linear-gradient(135deg,#f0fdf4,#e8f5e9); border-radius:14px; border:1.5px solid #c8e6c9; padding:22px; }
.invite-title { font-size:15px; font-weight:700; color:#1a1a2e; margin-bottom:6px; }
.invite-sub { font-size:13px; color:#555; margin-bottom:16px; }

/* Activity item */
.activity-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f5f5f5; }
.activity-item:last-child { border-bottom:none; }
.activity-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.activity-dot.online { background:#25d366; }
.activity-dot.offline { background:#bbb; }

/* Empty state */
.empty-state { padding:60px 20px; text-align:center; }
.empty-state-icon { font-size:60px; opacity:0.2; margin-bottom:16px; }
.empty-state p { color:#aaa; font-size:14px; }
@endsection

@section('content')

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="team-stat">
            <div class="team-stat-icon">👥</div>
            <div><div class="team-stat-label">Total Members</div><div class="team-stat-value" id="tmStatTotal">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="team-stat blue">
            <div class="team-stat-icon">🟢</div>
            <div><div class="team-stat-label">Online Now</div><div class="team-stat-value" id="tmStatOnline">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="team-stat orange">
            <div class="team-stat-icon">🤝</div>
            <div><div class="team-stat-label">Agents</div><div class="team-stat-value" id="tmStatAgents">—</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="team-stat purple">
            <div class="team-stat-icon">👑</div>
            <div><div class="team-stat-label">Admins</div><div class="team-stat-value" id="tmStatAdmins">—</div></div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- ── LEFT: Members List ── -->
    <div class="col-lg-8">
        <div class="card fu-card">
            <!-- Toolbar -->
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <span class="fw-bold me-auto">Team Members</span>
                <!-- Search -->
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px;"></i>
                    <input type="text" id="tmSearch" class="form-control form-control-sm" placeholder="Search members…"
                        style="padding-left:30px;width:180px;border-radius:8px;" oninput="tmFilter()">
                </div>
                <!-- Role filter -->
                <select id="tmRoleFilter" class="form-select form-select-sm" style="width:120px;border-radius:8px;" onchange="tmFilter()">
                    <option value="">All Roles</option>
                    <option value="agent">Agent</option>
                    <option value="manager">Manager</option>
                    <!-- <option value="admin">Admin</option> -->
                </select>
                <!-- View toggle -->
                <div class="d-flex gap-1">
                    <button class="view-toggle-btn active" id="tmViewCard" onclick="tmSetView('card')" title="Card view"><i class="bi bi-grid-3x2-gap-fill"></i></button>
                    <button class="view-toggle-btn" id="tmViewTable" onclick="tmSetView('table')" title="Table view"><i class="bi bi-table"></i></button>
                </div>
                <!-- Add -->
                <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="tmOpenAdd()">
                    <i class="bi bi-person-plus-fill me-1"></i>Add Member
                </button>
            </div>

            <!-- Card view -->
            <div class="card-body" id="tmCardView">
                <div class="row g-3" id="tmCardGrid">
                    <div class="col-12 text-center text-muted py-5">
                        <div class="spinner-border text-success mb-3" role="status"></div>
                        <div>Loading team members…</div>
                    </div>
                </div>
            </div>

            <!-- Table view -->
            <div id="tmTableView" style="display:none;">
                <div class="table-responsive">
                    <table class="table team-table mb-0">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Seen</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tmTableBody">
                            <tr><td colspan="6"><div class="text-center text-muted py-4">Loading…</div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: Add Member + Activity ── -->
    <div class="col-lg-4">
        <!-- Invite Card -->
        <div class="invite-card mb-3">
            <div class="invite-title"><i class="bi bi-person-plus-fill me-2 text-success"></i>Add Team Member</div>
            <div class="invite-sub">Add an agent or admin to your WhatsApp team. They can access the chat inbox and manage conversations.</div>
            <button class="btn btn-fu-primary rounded-3 w-100" onclick="tmOpenAdd()">
                <i class="bi bi-plus-lg me-1"></i>Add New Member
            </button>
        </div>

        <!-- Online Activity -->
        <div class="card fu-card">
            <div class="card-header"><i class="bi bi-activity me-2 text-success"></i>Online Activity</div>
            <div class="card-body p-0 px-3">
                <div id="tmActivity">
                    <div class="text-center text-muted py-4" style="font-size:13px;">Loading activity…</div>
                </div>
            </div>
        </div>

        <!-- Permissions Info -->
        <div class="card fu-card mt-3">
            <div class="card-header"><i class="bi bi-shield-check me-2 text-primary"></i>Role Permissions</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="fw-bold mb-1" style="font-size:13px;color:#1976d2;"><i class="bi bi-person-fill me-1"></i>Agent</div>
                    <ul class="mb-0 ps-3" style="font-size:12px;color:#555;">
                        <li>Access chat inbox</li>
                        <li>Send and receive messages</li>
                        <li>View assigned conversations</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <div class="fw-bold mb-1" style="font-size:13px;color:#f57c00;"><i class="bi bi-person-fill me-1"></i>Manager</div>
                    <ul class="mb-0 ps-3" style="font-size:12px;color:#555;">
                        <li>All chats (not just assigned)</li>
                   
                        <li>Own dashboard stats</li>
                        <li>Change password</li>
                    </ul>
                </div>
                <div>
                    <div class="fw-bold mb-1" style="font-size:13px;color:#7b1fa2;"><i class="bi bi-person-badge-fill me-1"></i>Admin</div>
                    <ul class="mb-0 ps-3" style="font-size:12px;color:#555;">
                        <li>Full access to everything</li>
                        <li>Manage team members</li>
                        <li>Billing & settings</li>
                        <li>Flow builder & automation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Add/Edit Member Modal -->
<div class="modal fade" id="tmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="tmModalTitle">Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <input type="hidden" id="tmEditId">
                <div class="mb-3">
                    <label for="tmName" class="form-label fw-semibold" style="font-size:12px;">Full Name *</label>
                    <input type="text" id="tmName" class="form-control rounded-3" placeholder="Member's full name">
                </div>
                <div class="mb-3">
                    <label for="tmEmail" class="form-label fw-semibold" style="font-size:12px;">Email Address *</label>
                    <input type="email" id="tmEmail" class="form-control rounded-3" placeholder="member@example.com">
                </div>
                <div class="mb-3">
                    <label for="tmPassword" class="form-label fw-semibold" style="font-size:12px;">Password * <span class="text-muted fw-normal" id="tmPwdHint">(min 6 characters)</span></label>
                    <div class="input-group">
                        <input type="password" id="tmPassword" class="form-control rounded-start-3" placeholder="Set a password">
                        <button type="button" class="btn btn-outline-secondary" onclick="tmTogglePwd()" style="border-radius:0 10px 10px 0;"><i class="bi bi-eye" id="tmPwdEye"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="tmRole" class="form-label fw-semibold" style="font-size:12px;">Role *</label>
                    <select id="tmRole" class="form-select rounded-3">
                        <option value="agent">Agent — Chat inbox (assigned only) + Dashboard</option>
                        <option value="manager">Manager — All chats + Contacts + Campaigns + Templates</option>
                        <!-- <option value="admin">Admin — Full access to all tools</option> -->
                    </select>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tmIsActive" checked>
                    <label class="form-check-label fw-semibold" for="tmIsActive" style="font-size:13px;">Active (can login)</label>
                </div>
                <div id="tmModalErr" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-fu-primary rounded-3" id="tmSaveBtn" onclick="tmSave()">
                    <i class="bi bi-check-lg me-1"></i>Save Member
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm -->
<div class="modal fade" id="tmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
                <h6 class="fw-bold mb-2">Remove Member?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">They will lose access to the platform.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger rounded-3 px-4" id="tmConfirmDelBtn" onclick="tmConfirmDelete()">Remove</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let tmAll = [], tmFiltered = [], tmView = 'card', tmDeleteId = null;
let tmModal, tmDeleteModal;

document.addEventListener('DOMContentLoaded', () => {
    tmModal       = new bootstrap.Modal(document.getElementById('tmModal'));
    tmDeleteModal = new bootstrap.Modal(document.getElementById('tmDeleteModal'));
    tmLoad();
});

/* ── LOAD ── */
function tmLoad() {
    fetch('/team/list').then(r => r.json()).then(data => {
        tmAll = data.members || [];
        tmFiltered = [...tmAll];
        tmUpdateStats();
        tmRender();
        tmRenderActivity();
    }).catch(() => {
        document.getElementById('tmCardGrid').innerHTML = `<div class="col-12"><div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load team members.</p></div></div>`;
    });
}

/* ── STATS ── */
function tmUpdateStats() {
    document.getElementById('tmStatTotal').textContent  = tmAll.length;
    document.getElementById('tmStatOnline').textContent = tmAll.filter(m=>m.is_online).length;
    document.getElementById('tmStatAgents').textContent = tmAll.filter(m=>m.team_role==='agent').length;
    document.getElementById('tmStatAdmins').textContent = tmAll.filter(m=>m.team_role==='admin').length;
}

/* ── FILTER ── */
function tmFilter() {
    const q = document.getElementById('tmSearch').value.toLowerCase();
    const r = document.getElementById('tmRoleFilter').value;
    tmFiltered = tmAll.filter(m => {
        const mQ = !q || (m.name||'').toLowerCase().includes(q) || (m.email||'').toLowerCase().includes(q);
        const mR = !r || m.team_role === r;
        return mQ && mR;
    });
    tmRender();
}

/* ── VIEW TOGGLE ── */
function tmSetView(v) {
    tmView = v;
    document.getElementById('tmCardView').style.display  = v==='card'  ? 'block' : 'none';
    document.getElementById('tmTableView').style.display = v==='table' ? 'block' : 'none';
    document.getElementById('tmViewCard').classList.toggle('active',  v==='card');
    document.getElementById('tmViewTable').classList.toggle('active', v==='table');
    tmRender();
}

/* ── RENDER ── */
function tmRender() {
    if (tmView === 'card') tmRenderCards();
    else tmRenderTable();
}

function tmRenderCards() {
    const grid = document.getElementById('tmCardGrid');
    if (!tmFiltered.length) {
        grid.innerHTML = `<div class="col-12"><div class="empty-state"><div class="empty-state-icon">👥</div><p>No team members found.</p><button class="btn btn-fu-primary btn-sm rounded-pill mt-2" onclick="tmOpenAdd()"><i class="bi bi-person-plus-fill me-1"></i>Add First Member</button></div></div>`;
        return;
    }
    grid.innerHTML = tmFiltered.map(m => {
        const roleClass = m.team_role==='admin' ? 'admin' : m.team_role==='manager' ? 'manager' : 'agent';
        const initials  = (m.name||m.email||'?').slice(0,2).toUpperCase();
        const onlineDot = m.is_online ? '' : 'offline';
        const lastSeen  = m.last_seen ? tmFormatDate(m.last_seen) : 'Never';
        return `<div class="col-sm-6 col-xl-4">
            <div class="member-card">
                <div class="member-card-body">
                    <div class="member-avatar ${roleClass}">
                        ${escHtml(initials)}
                        <div class="member-online-dot ${onlineDot}"></div>
                    </div>
                    <div class="member-name">${escHtml(m.name||'—')}</div>
                    <div class="member-email" title="${escHtml(m.email)}">${escHtml(m.email||'—')}</div>
                    <span class="member-role-badge ${roleClass}">${m.team_role==='admin'?'👑 Admin':m.team_role==='manager'?'🎯 Manager':'🤝 Agent'}</span>
                    <br>
                    <span class="member-status-badge ${m.is_active?'active':'inactive'}">
                        <i class="bi bi-circle-fill" style="font-size:7px;"></i>
                        ${m.is_active?'Active':'Inactive'}
                    </span>
                </div>
                <div class="member-card-footer">
                    <span class="member-last-seen"><i class="bi bi-clock me-1"></i>${lastSeen}</span>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary rounded-2" onclick="tmOpenEdit(${m.id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-outline-danger rounded-2" onclick="tmAskDelete(${m.id})" title="Remove"><i class="bi bi-person-x-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function tmRenderTable() {
    const tbody = document.getElementById('tmTableBody');
    if (!tmFiltered.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state py-4"><div class="empty-state-icon">👥</div><p>No members found.</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = tmFiltered.map(m => {
        const roleClass = m.team_role==='admin' ? 'admin' : m.team_role==='manager' ? 'manager' : 'agent';
        const bgColor   = m.team_role==='admin' ? '#7b1fa2' : '#1976d2';
        const initials  = (m.name||m.email||'?').slice(0,2).toUpperCase();
        const lastSeen  = m.last_seen ? tmFormatDate(m.last_seen) : '—';
        const joined    = (m.created_at||'').substring(0,10);
        return `<tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="member-av-sm" style="background:${bgColor};">${escHtml(initials)}</div>
                    <div>
                        <div class="fw-semibold" style="font-size:14px;">${escHtml(m.name||'—')}</div>
                        <div class="text-muted" style="font-size:11px;">${escHtml(m.email||'—')}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="member-role-badge ${roleClass}" style="font-size:11px;">${m.team_role==='admin'?'👑 Admin':m.team_role==='manager'?'🎯 Manager':'🤝 Agent'}</span>
            </td>
            <td>
                <span class="member-status-badge ${m.is_active?'active':'inactive'}">
                    <i class="bi bi-circle-fill" style="font-size:7px;"></i>
                    ${m.is_active?'Active':'Inactive'}
                </span>
            </td>
            <td class="text-muted" style="font-size:12px;">${lastSeen}</td>
            <td class="text-muted" style="font-size:12px;">${joined}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary rounded-2 me-1" onclick="tmOpenEdit(${m.id})"><i class="bi bi-pencil-fill"></i></button>
                <button class="btn btn-sm btn-outline-danger rounded-2" onclick="tmAskDelete(${m.id})"><i class="bi bi-person-x-fill"></i></button>
            </td>
        </tr>`;
    }).join('');
}

/* ── ACTIVITY ── */
function tmRenderActivity() {
    const box = document.getElementById('tmActivity');
    if (!tmAll.length) {
        box.innerHTML = '<div class="text-center text-muted py-4" style="font-size:13px;">No members yet.</div>';
        return;
    }
    const sorted = [...tmAll].sort((a,b) => b.is_online - a.is_online);
    box.innerHTML = sorted.slice(0,8).map(m => {
        const initials = (m.name||m.email||'?').slice(0,2).toUpperCase();
        const lastSeen = m.is_online ? 'Online now' : (m.last_seen ? tmFormatDate(m.last_seen) : 'Never');
        return `<div class="activity-item">
            <div class="activity-dot ${m.is_online?'online':'offline'}"></div>
            <div class="member-av-sm" style="width:30px;height:30px;font-size:11px;background:${m.team_role==='admin'?'#7b1fa2':m.team_role==='manager'?'#f57c00':'#1976d2'};">${escHtml(initials)}</div>
            <div style="flex:1;overflow:hidden;">
                <div style="font-size:13px;font-weight:600;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(m.name||m.email||'—')}</div>
                <div style="font-size:11px;color:#888;">${lastSeen}</div>
            </div>
        </div>`;
    }).join('');
}

/* ── FORMAT DATE ── */
function tmFormatDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    const now = new Date();
    const diff = Math.floor((now - dt) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

/* ── ADD / EDIT ── */
function tmOpenAdd() {
    document.getElementById('tmModalTitle').textContent = 'Add Team Member';
    document.getElementById('tmEditId').value = '';
    ['tmName','tmEmail','tmPassword'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('tmRole').value = 'agent';
    document.getElementById('tmIsActive').checked = true;
    document.getElementById('tmModalErr').classList.add('d-none');
    document.getElementById('tmPwdHint').textContent = '(min 6 characters)';
    document.getElementById('tmSaveBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Member';
    const pwdRow = document.getElementById('tmPassword').closest('.mb-3');
    pwdRow.style.display = 'block';
    tmModal.show();
}
function tmOpenEdit(id) {
    const m = tmAll.find(x => x.id == id); if (!m) return;
    document.getElementById('tmModalTitle').textContent = 'Edit Member';
    document.getElementById('tmEditId').value   = m.id;
    document.getElementById('tmName').value     = m.name  || '';
    document.getElementById('tmEmail').value    = m.email || '';
    document.getElementById('tmPassword').value = '';
    document.getElementById('tmRole').value     = m.team_role || 'agent';
    document.getElementById('tmIsActive').checked = !!m.is_active;
    document.getElementById('tmModalErr').classList.add('d-none');
    document.getElementById('tmPwdHint').textContent = '(leave blank to keep current)';
    document.getElementById('tmSaveBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Update Member';
    tmModal.show();
}
function tmTogglePwd() {
    const inp = document.getElementById('tmPassword');
    const ico = document.getElementById('tmPwdEye');
    inp.type = inp.type==='password' ? 'text' : 'password';
    ico.className = inp.type==='password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
function tmSave() {
    const id       = document.getElementById('tmEditId').value;
    const name     = document.getElementById('tmName').value.trim();
    const email    = document.getElementById('tmEmail').value.trim();
    const password = document.getElementById('tmPassword').value.trim();
    const role     = document.getElementById('tmRole').value;
    const isActive = document.getElementById('tmIsActive').checked ? 1 : 0;
    const err      = document.getElementById('tmModalErr');
    if (!name)  { err.textContent='Name is required.';  err.classList.remove('d-none'); return; }
    if (!email) { err.textContent='Email is required.'; err.classList.remove('d-none'); return; }
    if (!id && (!password || password.length < 6)) { err.textContent='Password must be at least 6 characters.'; err.classList.remove('d-none'); return; }
    err.classList.add('d-none');
    const btn = document.getElementById('tmSaveBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/team/${id}` : '/team';
    const body   = { name, email, team_role:role, is_active:isActive };
    if (password) body.password = password;
    fetch(url, {
        method, credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify(body)
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg me-1"></i>'+(id?'Update':'Save')+' Member';
        if (d.error) { err.textContent=d.error; err.classList.remove('d-none'); return; }
        tmModal.hide(); tmLoad(); showToast(id?'✅ Member updated':'✅ Member added','success');
    }).catch(()=>{ btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg me-1"></i>Save Member'; err.textContent='Something went wrong.'; err.classList.remove('d-none'); });
}

/* ── DELETE ── */
function tmAskDelete(id) { tmDeleteId=id; tmDeleteModal.show(); }
function tmConfirmDelete() {
    if (!tmDeleteId) return;
    const btn=document.getElementById('tmConfirmDelBtn'); btn.disabled=true; btn.textContent='Removing…';
    fetch(`/team/${tmDeleteId}`,{
        method:'DELETE', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}
    }).then(r=>r.json()).then(()=>{
        btn.disabled=false; btn.textContent='Remove';
        tmDeleteModal.hide(); tmDeleteId=null; tmLoad(); showToast('✅ Member removed','success');
    }).catch(()=>{ btn.disabled=false; btn.textContent='Remove'; showToast('❌ Failed','error'); });
}

/* ── AUTO REFRESH ONLINE STATUS ── */
setInterval(() => {
    fetch('/team/stats').then(r=>r.json()).then(d=>{
        document.getElementById('tmStatOnline').textContent = d.online ?? '—';
    }).catch(()=>{});
}, 30000);
</script>
@endpush