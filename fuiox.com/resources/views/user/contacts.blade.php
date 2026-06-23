@extends('layouts.app')

@section('title', 'Contacts')
@section('page_title', 'Contacts')

@section('page_styles')
.contacts-stats .stat-box {
    background:#fff; border-radius:14px;
    padding:18px 20px;
    box-shadow:0 1px 4px rgba(0,0,0,0.06);
    border-left:4px solid #25d366;
    display:flex; align-items:center; gap:14px;
}
.contacts-stats .stat-box.blue  { border-left-color:#1976d2; }
.contacts-stats .stat-box.orange{ border-left-color:#f57c00; }
.contacts-stats .stat-box.purple{ border-left-color:#7b1fa2; }
.stat-box-icon { font-size:26px; }
.stat-box-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.5px; }
.stat-box-value { font-size:24px; font-weight:800; color:#1a1a2e; }

.contacts-table th { font-size:12px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.3px; background:#fafafa; border-bottom:2px solid #f0f0f0; padding:11px 14px; white-space:nowrap; }
.contacts-table td { font-size:13px; color:#333; padding:12px 14px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.contacts-table tbody tr:hover td { background:#fafafa; }
.contacts-table .contact-av { width:36px; height:36px; border-radius:50%; background:#e8f5e9; color:#2e7d32; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

.group-pill { font-size:11px; font-weight:600; padding:3px 10px; border-radius:20px; background:#f0f0f0; color:#555; display:inline-block; }
.action-btn { width:30px; height:30px; border:none; background:transparent; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:15px; transition:0.15s; }
.action-btn.edit:hover { background:#e3f2fd; color:#1976d2; }
.action-btn.delete:hover { background:#fdecea; color:#e53935; }

.search-bar { position:relative; }
.search-bar .bi-search { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#aaa; font-size:14px; }
.search-bar input { padding-left:36px; }

.empty-state { padding:60px 20px; text-align:center; }
.empty-state-icon { font-size:60px; opacity:0.2; margin-bottom:16px; }
.empty-state p { color:#aaa; font-size:14px; }
@endsection

@section('content')

<!-- Stats Row -->
<div class="row g-3 mb-4 contacts-stats">
    <div class="col-6 col-md-3">
        <div class="stat-box">
            <div class="stat-box-icon">👥</div>
            <div>
                <div class="stat-box-label">Total Contacts</div>
                <div class="stat-box-value" id="statTotal">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box blue">
            <div class="stat-box-icon">📋</div>
            <div>
                <div class="stat-box-label">Groups</div>
                <div class="stat-box-value" id="statGroups">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box orange">
            <div class="stat-box-icon">🆕</div>
            <div>
                <div class="stat-box-label">Added This Week</div>
                <div class="stat-box-value" id="statWeek">—</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-box purple">
            <div class="stat-box-icon">📤</div>
            <div>
                <div class="stat-box-label">Messaged</div>
                <div class="stat-box-value" id="statMessaged">—</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card fu-card">
    <!-- Toolbar -->
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <span class="me-auto fw-bold">All Contacts</span>

        <!-- Search -->
        <div class="search-bar">
            <i class="bi bi-search"></i>
            <input type="text" id="contactSearch" class="form-control form-control-sm" placeholder="Search contacts…" style="width:200px;border-radius:8px;" oninput="conFilterTable()">
        </div>

        <!-- Group filter -->
        <select id="groupFilter" class="form-select form-select-sm" style="width:140px;border-radius:8px;" onchange="conFilterTable()">
            <option value="">All Groups</option>
        </select>

        <!-- Import -->
        <label class="btn btn-sm btn-outline-secondary rounded-pill mb-0" style="cursor:pointer;">
            <i class="bi bi-upload me-1"></i>Import CSV
            <input type="file" id="csvImportInput" accept=".csv" style="display:none;" onchange="conImportCSV(this)">
        </label>

        <!-- Export -->
        <a href="/contacts/export" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="bi bi-download me-1"></i>Export
        </a>

        <!-- Add Contact -->
        <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="conOpenAdd()">
            <i class="bi bi-plus-lg me-1"></i>Add Contact
        </button>
    </div>

    <!-- Table -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table contacts-table mb-0">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" onchange="conSelectAll(this)"></th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Group</th>
                        <th>Tags</th>
                        <th>Added</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="contactsTableBody">
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">👥</div><p>Loading contacts…</p></div></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top" id="conPaginationWrap" style="display:none!important;">
            <div style="font-size:13px;color:#888;" id="conPageInfo"></div>
            <div class="d-flex gap-2" id="conPagination"></div>
        </div>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div id="bulkActionsBar" class="d-none position-fixed bottom-0 start-50 translate-middle-x mb-3 px-4 py-2 bg-dark text-white rounded-pill d-flex align-items-center gap-3 shadow-lg" style="z-index:1050;">
    <span id="bulkCount" style="font-size:13px;"></span>
    <button class="btn btn-sm btn-danger rounded-pill" onclick="conBulkDelete()"><i class="bi bi-trash me-1"></i>Delete Selected</button>
    <button class="btn btn-sm btn-outline-light rounded-pill" onclick="conClearSelection()"><i class="bi bi-x me-1"></i>Cancel</button>
</div>

@endsection

@push('modals')
<!-- Add/Edit Contact Modal -->
<div class="modal fade" id="conModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="conModalTitle">Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <input type="hidden" id="conEditId">
                <div class="mb-3">
                    <label for="conName" class="form-label fw-semibold" style="font-size:12px;">Name *</label>
                    <input type="text" id="conName" class="form-control rounded-3" placeholder="Full name">
                </div>
                <div class="mb-3">
                    <label for="conPhone" class="form-label fw-semibold" style="font-size:12px;">Phone Number * <span class="text-muted fw-normal">(with country code)</span></label>
                    <input type="text" id="conPhone" class="form-control rounded-3" placeholder="e.g. 919876543210">
                </div>
                <div class="mb-3">
                    <label for="conGroup" class="form-label fw-semibold" style="font-size:12px;">Group</label>
                    <input type="text" id="conGroup" class="form-control rounded-3" placeholder="e.g. Customers, Leads">
                </div>
                <div class="mb-3">
                    <label for="conTags" class="form-label fw-semibold" style="font-size:12px;">Tags <span class="text-muted fw-normal">(comma separated)</span></label>
                    <input type="text" id="conTags" class="form-control rounded-3" placeholder="e.g. vip, active">
                </div>
                <div id="conModalError" class="alert alert-danger d-none rounded-3 py-2" style="font-size:13px;"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-fu-primary rounded-3" id="conSaveBtn" onclick="conSave()">
                    <i class="bi bi-check-lg me-1"></i>Save Contact
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="conDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
                <h6 class="fw-bold mb-2">Delete Contact?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger rounded-3 px-4" id="conConfirmDeleteBtn" onclick="conConfirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let conAllContacts = [], conFilteredContacts = [], conGroups = [];
let conCurrentPage = 1, conPerPage = 15;
let conDeleteId = null, conSelectedIds = new Set();
let conModal, conDeleteModal;

document.addEventListener('DOMContentLoaded', () => {
    conModal = new bootstrap.Modal(document.getElementById('conModal'));
    conDeleteModal = new bootstrap.Modal(document.getElementById('conDeleteModal'));
    conLoadStats();
    conLoadGroups();
    conLoadContacts();
});

/* ── STATS ── */
function conLoadStats() {
    fetch('/contacts/stats').then(r => r.json()).then(d => {
        document.getElementById('statTotal').textContent    = d.total    ?? 0;
        document.getElementById('statGroups').textContent   = d.groups   ?? 0;
        document.getElementById('statWeek').textContent     = d.this_week ?? 0;
        document.getElementById('statMessaged').textContent = d.messaged ?? 0;
    }).catch(() => {});
}

/* ── GROUPS ── */
function conLoadGroups() {
    fetch('/contacts/groups').then(r => r.json()).then(data => {
        conGroups = data.groups || [];
        const sel = document.getElementById('groupFilter');
        conGroups.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g; opt.textContent = g;
            sel.appendChild(opt);
        });
    }).catch(() => {});
}

/* ── LOAD CONTACTS ── */
function conLoadContacts() {
    fetch('/contacts/list').then(r => r.json()).then(data => {
        conAllContacts = data.contacts || data || [];
        conFilteredContacts = [...conAllContacts];
        conRenderTable();
    }).catch(() => {
        document.getElementById('contactsTableBody').innerHTML =
            `<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load contacts.</p></div></td></tr>`;
    });
}

/* ── FILTER ── */
function conFilterTable() {
    const q = document.getElementById('contactSearch').value.toLowerCase();
    const g = document.getElementById('groupFilter').value;
    conFilteredContacts = conAllContacts.filter(c => {
        const matchQ = !q || (c.name||'').toLowerCase().includes(q) || (c.phone||'').includes(q) || (c.tags||'').toLowerCase().includes(q);
        const matchG = !g || c.group === g;
        return matchQ && matchG;
    });
    conCurrentPage = 1;
    conRenderTable();
}

/* ── RENDER TABLE ── */
function conRenderTable() {
    const tbody = document.getElementById('contactsTableBody');
    if (!conFilteredContacts.length) {
        tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">👥</div><p>No contacts found.</p><button class="btn btn-fu-primary btn-sm rounded-pill mt-2" onclick="conOpenAdd()"><i class="bi bi-plus-lg me-1"></i>Add First Contact</button></div></td></tr>`;
        document.getElementById('conPaginationWrap').style.display = 'none';
        return;
    }
    const start = (conCurrentPage - 1) * conPerPage;
    const paged = conFilteredContacts.slice(start, start + conPerPage);
    tbody.innerHTML = paged.map(c => {
        const initials = (c.name || c.phone || '?').slice(0, 2).toUpperCase();
        const tags = (c.tags || '').split(',').filter(Boolean).map(t => `<span class="group-pill me-1">${escHtml(t.trim())}</span>`).join('');
        const checked = conSelectedIds.has(String(c.id)) ? 'checked' : '';
        const date = c.created_at ? c.created_at.substring(0, 10) : '—';
        return `<tr>
            <td><input type="checkbox" value="${c.id}" ${checked} onchange="conToggleSelect(this,'${c.id}')"></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="contact-av">${escHtml(initials)}</div>
                    <div>
                        <div class="fw-semibold" style="font-size:14px;color:#1a1a2e;">${escHtml(c.name||'—')}</div>
                        <div class="text-muted" style="font-size:11px;">${c.group?escHtml(c.group):''}</div>
                    </div>
                </div>
            </td>
            <td>${escHtml(c.phone)}</td>
            <td>${c.group ? `<span class="group-pill">${escHtml(c.group)}</span>` : '<span class="text-muted">—</span>'}</td>
            <td>${tags || '<span class="text-muted">—</span>'}</td>
            <td class="text-muted" style="font-size:12px;">${date}</td>
            <td class="text-end">
                <button class="action-btn edit" onclick="conOpenEdit(${c.id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                <button class="action-btn delete ms-1" onclick="conAskDelete(${c.id})" title="Delete"><i class="bi bi-trash-fill"></i></button>
            </td>
        </tr>`;
    }).join('');

    // Pagination
    const totalPages = Math.ceil(conFilteredContacts.length / conPerPage);
    const wrap = document.getElementById('conPaginationWrap');
    if (totalPages <= 1) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'flex';
    document.getElementById('conPageInfo').textContent = `Showing ${start + 1}–${Math.min(start + conPerPage, conFilteredContacts.length)} of ${conFilteredContacts.length}`;
    let pages = '';
    for (let i = 1; i <= totalPages; i++) {
        pages += `<button class="btn btn-sm ${i === conCurrentPage ? 'btn-fu-primary' : 'btn-outline-secondary'} rounded-2" onclick="conGoPage(${i})" style="min-width:32px;">${i}</button>`;
    }
    document.getElementById('conPagination').innerHTML =
        `<button class="btn btn-sm btn-outline-secondary rounded-2" onclick="conGoPage(${conCurrentPage - 1})" ${conCurrentPage === 1 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>
        ${pages}
        <button class="btn btn-sm btn-outline-secondary rounded-2" onclick="conGoPage(${conCurrentPage + 1})" ${conCurrentPage === totalPages ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>`;
}

function conGoPage(p) {
    const total = Math.ceil(conFilteredContacts.length / conPerPage);
    if (p < 1 || p > total) return;
    conCurrentPage = p;
    conRenderTable();
}

/* ── SELECTION ── */
function conToggleSelect(cb, id) {
    if (cb.checked) conSelectedIds.add(String(id));
    else conSelectedIds.delete(String(id));
    conUpdateBulkBar();
}
function conSelectAll(cb) {
    const page = conFilteredContacts.slice((conCurrentPage - 1) * conPerPage, conCurrentPage * conPerPage);
    page.forEach(c => { if (cb.checked) conSelectedIds.add(String(c.id)); else conSelectedIds.delete(String(c.id)); });
    conRenderTable();
    conUpdateBulkBar();
}
function conUpdateBulkBar() {
    const bar = document.getElementById('bulkActionsBar');
    if (conSelectedIds.size > 0) { bar.classList.remove('d-none'); document.getElementById('bulkCount').textContent = conSelectedIds.size + ' selected'; }
    else bar.classList.add('d-none');
}
function conClearSelection() { conSelectedIds.clear(); conRenderTable(); conUpdateBulkBar(); }

/* ── ADD / EDIT ── */
function conOpenAdd() {
    document.getElementById('conModalTitle').textContent = 'Add Contact';
    document.getElementById('conEditId').value = '';
    ['conName','conPhone','conGroup','conTags'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('conModalError').classList.add('d-none');
    document.getElementById('conSaveBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Contact';
    conModal.show();
}
function conOpenEdit(id) {
    const c = conAllContacts.find(x => x.id == id); if (!c) return;
    document.getElementById('conModalTitle').textContent = 'Edit Contact';
    document.getElementById('conEditId').value = c.id;
    document.getElementById('conName').value  = c.name  || '';
    document.getElementById('conPhone').value = c.phone || '';
    document.getElementById('conGroup').value = c.group || '';
    document.getElementById('conTags').value  = c.tags  || '';
    document.getElementById('conModalError').classList.add('d-none');
    document.getElementById('conSaveBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Update Contact';
    conModal.show();
}
function conSave() {
    const id    = document.getElementById('conEditId').value;
    const name  = document.getElementById('conName').value.trim();
    const phone = document.getElementById('conPhone').value.trim().replace(/\D/g,'');
    const group = document.getElementById('conGroup').value.trim();
    const tags  = document.getElementById('conTags').value.trim();
    const errBox = document.getElementById('conModalError');
    if (!name) { errBox.textContent = 'Name is required.'; errBox.classList.remove('d-none'); return; }
    if (!phone || phone.length < 8) { errBox.textContent = 'Enter a valid phone number.'; errBox.classList.remove('d-none'); return; }
    errBox.classList.add('d-none');
    const btn = document.getElementById('conSaveBtn'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    const method = id ? 'PUT' : 'POST';
    const url    = id ? `/contacts/${id}` : '/contacts';
    fetch(url, {
        method, credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ name, phone, group, tags })
    }).then(r => r.json()).then(d => {
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>' + (id ? 'Update' : 'Save') + ' Contact';
        if (d.error) { errBox.textContent = d.error; errBox.classList.remove('d-none'); return; }
        conModal.hide(); conLoadContacts(); conLoadStats(); showToast(id ? '✅ Contact updated' : '✅ Contact added', 'success');
    }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Contact'; errBox.textContent = 'Something went wrong.'; errBox.classList.remove('d-none'); });
}

/* ── DELETE ── */
function conAskDelete(id) { conDeleteId = id; conDeleteModal.show(); }
function conConfirmDelete() {
    if (!conDeleteId) return;
    const btn = document.getElementById('conConfirmDeleteBtn'); btn.disabled = true; btn.textContent = 'Deleting…';
    fetch(`/contacts/${conDeleteId}`, {
        method: 'DELETE', credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(() => {
        btn.disabled = false; btn.textContent = 'Delete';
        conDeleteModal.hide(); conDeleteId = null; conLoadContacts(); conLoadStats(); showToast('✅ Contact deleted', 'success');
    }).catch(() => { btn.disabled = false; btn.textContent = 'Delete'; showToast('❌ Failed to delete', 'error'); });
}

/* ── BULK DELETE ── */
function conBulkDelete() {
    if (!conSelectedIds.size || !confirm(`Delete ${conSelectedIds.size} selected contacts?`)) return;
    const ids = [...conSelectedIds];
    Promise.all(ids.map(id => fetch(`/contacts/${id}`, {
        method: 'DELETE', credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }))).then(() => { conSelectedIds.clear(); conUpdateBulkBar(); conLoadContacts(); conLoadStats(); showToast(`✅ ${ids.length} contacts deleted`, 'success'); });
}

/* ── IMPORT CSV ── */
function conImportCSV(input) {
    const file = input.files[0]; if (!file) return;
    const fd = new FormData(); fd.append('file', file);
    const btn = input.parentElement;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Importing…';
    fetch('/contacts/import', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: fd
    }).then(r => r.json()).then(d => {
        btn.innerHTML = '<i class="bi bi-upload me-1"></i>Import CSV';
        input.value = '';
        showToast(d.message || '✅ Import complete', 'success');
        conLoadContacts(); conLoadStats(); conLoadGroups();
    }).catch(() => { btn.innerHTML = '<i class="bi bi-upload me-1"></i>Import CSV'; showToast('❌ Import failed', 'error'); });
}
</script>
@endpush