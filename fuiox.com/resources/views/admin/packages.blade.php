@extends('layouts.admin')

@section('title', 'Packages')
@section('page_title', 'Package Management')

@push('styles')
<style>
    /* ── Package card ── */
    .pkg-card {
        border: 2px solid #e5e9f0;
        border-radius: 14px;
        transition: border-color .2s, box-shadow .2s;
        position: relative;
        height: 100%;
    }
    .pkg-card:hover        { border-color: #00e676; box-shadow: 0 4px 16px rgba(0,230,118,.12); }
    .pkg-card.inactive     { opacity: .6; }
    .pkg-price             { font-size: 28px; font-weight: 800; color: #00a040; line-height: 1; }
    .pkg-price small       { font-size: 13px; font-weight: 400; color: #888; }
    .pkg-yearly            { font-size: 12px; color: #aaa; margin-top: 2px; }

    .pkg-features          { list-style: none; padding: 0; margin: 0 0 14px; }
    .pkg-features li {
        font-size: 12px; color: #555;
        padding: 5px 0;
        border-bottom: 1px solid #f5f5f5;
        display: flex; align-items: center; gap: 6px;
    }
    .pkg-features li:last-child { border-bottom: none; }
    .pkg-features li::before    { content: '✓'; color: #00e676; font-weight: 700; flex-shrink: 0; }

    /* ── Feature row in modal ── */
    .feature-item { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .feature-item input { flex: 1; }
</style>
@endpush

@section('content')

    {{-- ══ HEADER ══ --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-box-seam-fill me-2 text-success"></i>All Packages
        </h5>
        <button class="btn btn-success fw-semibold" onclick="openCreateModal()">
            <i class="bi bi-plus-lg me-1"></i> Create Package
        </button>
    </div>

    {{-- ══ PACKAGE CARDS ══ --}}
    @if($plans->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-box fs-1 d-block mb-2 opacity-25"></i>
            No packages yet. Create your first one!
        </div>
    @else
    <div class="row g-3 mb-4">
        @foreach($plans as $plan)
        @php $features = array_filter(array_map('trim', explode(',', $plan->features ?? ''))); @endphp
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card pkg-card {{ $plan->is_active ? '' : 'inactive' }} p-3">

                {{-- Status + Free Trial badges --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @if($plan->is_free_trial)
                        <span class="badge rounded-pill" style="background:#e8eaf6;color:#3949ab;">
                            <i class="bi bi-gift-fill me-1"></i>Free Trial
                        </span>
                    @else
                        <span></span>
                    @endif
                    <span class="badge rounded-pill {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $plan->is_active ? '● Active' : '○ Inactive' }}
                    </span>
                </div>

                {{-- Name --}}
                <div class="fw-bold fs-5 text-dark mb-1">{{ $plan->name }}</div>

                {{-- Price --}}
                <div class="pkg-price">
                    ₹{{ number_format($plan->price) }}<small>/month</small>
                </div>
                @if($plan->yearly_price)
                    <div class="pkg-yearly">₹{{ number_format($plan->yearly_price) }}/year</div>
                @endif

                {{-- Description --}}
                @if($plan->description)
                    <p class="text-muted small mt-2 mb-2">{{ $plan->description }}</p>
                @endif

                {{-- Features --}}
                <ul class="pkg-features mt-2">
                    <li>{{ number_format($plan->messages_limit) }} messages/month</li>
                    <li>{{ $plan->contacts_limit >= 99999 ? 'Unlimited' : number_format($plan->contacts_limit) }} contacts</li>
                    <li>Up to {{ $plan->team_limit >= 99999 ? 'Unlimited' : $plan->team_limit }} team members</li>
                    @foreach($features as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>

                {{-- Actions --}}
                <div class="d-flex gap-2 flex-wrap mt-auto pt-2 border-top">
                    <button class="btn btn-sm btn-outline-primary py-1 px-2" onclick="openEditModal({{ $plan->id }})" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                        <span class="ms-1 d-none d-md-inline">Edit</span>
                    </button>
                    <button class="btn btn-sm {{ $plan->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} py-1 px-2"
                            onclick="togglePlan({{ $plan->id }})" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="bi bi-{{ $plan->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                        <span class="ms-1 d-none d-md-inline">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</span>
                    </button>
                    <button class="btn btn-sm btn-outline-danger py-1 px-2"
                     onclick="confirmDelete({{ $plan->id }})"
                        <i class="bi bi-trash-fill"></i>
                        <span class="ms-1 d-none d-md-inline">Delete</span>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

@endsection

@push('modals')
{{-- ══ CREATE / EDIT MODAL ══ --}}
<div class="modal fade" id="pkgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-3">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="bi bi-plus-circle-fill text-success me-2"></i>Create Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pkgForm" method="POST" action="{{ route('admin.packages.store') }}">
                    @csrf
                    <input type="hidden" id="pkgId" name="id">

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label for="pkgName" class="form-label fw-semibold small">Package Name *</label>
                            <input type="text" class="form-control" name="name" id="pkgName" placeholder="e.g. Starter, Pro" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgPrice" class="form-label fw-semibold small">Monthly Price (₹) *</label>
                            <input type="number" class="form-control" name="price" id="pkgPrice" autocomplete="off" placeholder="499" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgYearly" class="form-label fw-semibold small">Yearly Price (₹)</label>
                            <input type="number" class="form-control" name="yearly_price" id="pkgYearly" autocomplete="off" placeholder="4999">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgCurrency" class="form-label fw-semibold small">Currency</label>
                            <select class="form-select" name="currency" id="pkgCurrency" autocomplete="off">
                                <option value="INR">INR (₹)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgMessages" class="form-label fw-semibold small">Messages / Month *</label>
                            <input type="number" class="form-control" name="messages_limit" id="pkgMessages" autocomplete="off" placeholder="10000" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgContacts" class="form-label fw-semibold small">Contacts Limit *</label>
                            <input type="number" class="form-control" name="contacts_limit" id="pkgContacts" autocomplete="off" placeholder="500 or 99999 for unlimited" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgTeam" class="form-label fw-semibold small">Team Members *</label>
                            <input type="number" class="form-control" name="team_limit" id="pkgTeam" autocomplete="off" placeholder="5" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="pkgSort" class="form-label fw-semibold small">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="pkgSort" autocomplete="off" placeholder="1">
                        </div>
                        <div class="col-12">
                            <label for="pkgDesc" class="form-label fw-semibold small">Description</label>
                            <textarea class="form-control" name="description" id="pkgDesc" autocomplete="off" rows="2" placeholder="Brief description…"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_free_trial" id="pkgFreeTrial" value="1"
                                       style="accent-color:#00c853;">
                                <label class="form-check-label small fw-semibold" for="pkgFreeTrial">
                                    This is a Free Trial package
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-label fw-semibold small">
                                Features <span class="text-muted fw-normal">(shown as bullet points)</span>
                            </div>
                            <div id="featuresList" class="mb-2"></div>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addFeature()">
                                <i class="bi bi-plus-lg me-1"></i>Add Feature
                            </button>
                            <input type="hidden" name="features" id="pkgFeatures">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="pkgForm" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-floppy-fill me-1"></i>Save Package
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
const plansData  = @json($plans);
const bsModal    = new bootstrap.Modal(document.getElementById('pkgModal'));

function openCreateModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle-fill text-success me-2"></i>Create Package';
    document.getElementById('pkgForm').action = '{{ route("admin.packages.store") }}';
    document.getElementById('pkgId').value = '';
    ['pkgName','pkgPrice','pkgYearly','pkgMessages','pkgContacts','pkgTeam','pkgSort','pkgDesc'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('pkgFreeTrial').checked = false;
    document.getElementById('featuresList').innerHTML = '';
    bsModal.show();
}

function openEditModal(id) {
    const plan = plansData.find(p => p.id === id);
    if (!plan) return;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-fill text-primary me-2"></i>Edit Package';
    document.getElementById('pkgId').value          = plan.id;
    document.getElementById('pkgName').value        = plan.name;
    document.getElementById('pkgPrice').value       = plan.price;
    document.getElementById('pkgYearly').value      = plan.yearly_price || '';
    document.getElementById('pkgMessages').value    = plan.messages_limit;
    document.getElementById('pkgContacts').value    = plan.contacts_limit;
    document.getElementById('pkgTeam').value        = plan.team_limit;
    document.getElementById('pkgSort').value        = plan.sort_order || 0;
    document.getElementById('pkgDesc').value        = plan.description || '';
    document.getElementById('pkgFreeTrial').checked = plan.is_free_trial == 1;
    document.getElementById('featuresList').innerHTML = '';
    (plan.features || '').split(',').filter(f => f.trim()).forEach(f => addFeature(f.trim()));
    bsModal.show();
}

function addFeature(value = '') {
    const list = document.getElementById('featuresList');
    const div  = document.createElement('div');
    div.className = 'feature-item';
    div.innerHTML = `
        <input type="text" id="pkgSearch" name="pkg_search" autocomplete="off" class="form-control form-control-sm"
               placeholder="e.g. WhatsApp API Integration"
               value="${escHtml(value)}"
               oninput="updateFeatures()">
        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2"
                onclick="this.parentElement.remove(); updateFeatures()">
            <i class="bi bi-x-lg"></i>
        </button>`;
    list.appendChild(div);
    updateFeatures();
}

function updateFeatures() {
    const inputs = document.querySelectorAll('#featuresList input');
    document.getElementById('pkgFeatures').value =
        Array.from(inputs).map(i => i.value.trim()).filter(Boolean).join(',');
}

function togglePlan(id)  { adminPost('/admin/packages/' + id + '/toggle', {}); }
function deletePlan(id)  { adminPost('/admin/packages/' + id + '/delete', {}); }

function confirmDelete(id) {
    const modal = document.createElement('div');
    modal.innerHTML = `
        <div id="delOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
            <div style="background:#fff;border-radius:16px;padding:28px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;">
                <div style="width:56px;height:56px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-trash-fill" style="font-size:24px;color:#e53935;"></i>
                </div>
                <div style="font-size:18px;font-weight:700;color:#1a1a2e;margin-bottom:8px;">Delete Package?</div>
                <div style="font-size:13px;color:#888;margin-bottom:24px;">This action cannot be undone. The package will be permanently removed.</div>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button onclick="document.getElementById('delOverlay').remove()"
                            class="btn btn-light px-4">Cancel</button>
                    <button onclick="document.getElementById('delOverlay').remove(); deletePlan(${id})"
                            class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-trash-fill me-1"></i>Yes, Delete
                    </button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal.firstElementChild);
}

document.getElementById('pkgForm').addEventListener('submit', updateFeatures);
</script>
@endpush