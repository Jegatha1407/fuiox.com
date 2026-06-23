@extends('layouts.app')

@section('title', 'Billing')
@section('page_title', 'Billing & Plans')

@section('page_styles')
/* Plan cards */
.plan-card { background:#fff; border-radius:16px; box-shadow:0 1px 4px rgba(0,0,0,0.06); border:2px solid #f0f0f0; padding:28px; display:flex; flex-direction:column; transition:0.2s; height:100%; }
.plan-card:hover { border-color:#25d366; box-shadow:0 8px 32px rgba(37,211,102,0.1); }
.plan-card.popular { background:#1a1a2e; border-color:#25d366; }
.popular-label { position:absolute; top:-14px; left:50%; transform:translateX(-50%); background:#25d366; color:#fff; font-size:12px; font-weight:700; padding:4px 16px; border-radius:20px; white-space:nowrap; }
.plan-name { font-size:14px; font-weight:700; color:#888; margin-bottom:8px; }
.plan-card.popular .plan-name { color:rgba(255,255,255,0.5); }
.plan-price { font-size:46px; font-weight:900; color:#1a1a2e; margin-bottom:4px; line-height:1; }
.plan-card.popular .plan-price { color:#fff; }
.plan-price span { font-size:16px; font-weight:400; color:#888; }
.plan-card.popular .plan-price span { color:rgba(255,255,255,0.5); }
.plan-desc { font-size:13px; color:#888; margin-bottom:20px; }
.plan-card.popular .plan-desc { color:rgba(255,255,255,0.4); }
.plan-features { list-style:none; flex:1; margin-bottom:24px; padding:0; }
.plan-features li { font-size:13px; color:#333; padding:8px 0; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:8px; }
.plan-card.popular .plan-features li { color:rgba(255,255,255,0.75); border-color:rgba(255,255,255,0.08); }
.plan-features li::before { content:'✓'; color:#25d366; font-weight:700; flex-shrink:0; }

/* Current plan banner */
.current-plan-banner { background:linear-gradient(135deg,#1a1a2e,#2d2d4e); border-radius:16px; padding:24px; color:#fff; }
.current-plan-name { font-size:24px; font-weight:800; color:#25d366; }
.current-plan-status { font-size:13px; color:rgba(255,255,255,0.5); margin-top:4px; }
.current-plan-detail { background:rgba(255,255,255,0.08); border-radius:8px; padding:12px; text-align:center; }
.current-plan-detail .label { font-size:10px; color:rgba(255,255,255,0.4); text-transform:uppercase; font-weight:700; }
.current-plan-detail .val   { font-size:20px; font-weight:800; color:#fff; margin-top:3px; }

/* Days progress */
.days-progress { height:8px; border-radius:4px; background:rgba(255,255,255,0.15); overflow:hidden; margin-top:8px; }
.days-progress-fill { height:100%; border-radius:4px; background:#25d366; transition:width 0.5s; }

/* Invoice table */
.inv-table th { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; background:#fafafa; border-bottom:2px solid #f0f0f0; padding:10px 14px; }
.inv-table td { font-size:13px; padding:12px 14px; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.inv-table tbody tr:hover td { background:#fafafa; }

/* Toggle */
.billing-toggle { display:flex; align-items:center; gap:10px; background:#f0f0f0; border-radius:30px; padding:4px; }
.billing-toggle button { border:none; padding:8px 20px; border-radius:24px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:0.2s; background:transparent; color:#666; }
.billing-toggle button.active { background:#fff; color:#1a1a2e; box-shadow:0 2px 8px rgba(0,0,0,0.1); }

/* Payment modal */
.razorpay-branding { display:flex; align-items:center; gap:8px; font-size:12px; color:#888; margin-top:12px; }

.empty-state { padding:40px 20px; text-align:center; }
.empty-state-icon { font-size:56px; opacity:0.2; margin-bottom:14px; }
.empty-state p { color:#aaa; font-size:14px; }
@endsection

@section('content')

<!-- Current Plan (if active) -->
<div id="billCurrentWrap" class="mb-4" style="display:none;">
    <div class="current-plan-banner">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.4);text-transform:uppercase;margin-bottom:6px;">Current Plan</div>
                <div class="current-plan-name" id="billCurrName">—</div>
                <div class="current-plan-status" id="billCurrStatus">—</div>
                <div class="days-progress mt-3"><div class="days-progress-fill" id="billDaysBar" style="width:0%"></div></div>
                <div style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:4px;" id="billDaysLeft">—</div>
            </div>
            <div class="col-md-7">
                <div class="row g-2">
                    <div class="col-4"><div class="current-plan-detail"><div class="label">Messages</div><div class="val" id="billCurrMsgs">—</div></div></div>
                    <div class="col-4"><div class="current-plan-detail"><div class="label">Contacts</div><div class="val" id="billCurrCon">—</div></div></div>
                    <div class="col-4"><div class="current-plan-detail"><div class="label">Team</div><div class="val" id="billCurrTeam">—</div></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plan Toggle -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1">Choose a Plan</h5>
        <p class="text-muted mb-0" style="font-size:13px;">Simple pricing. Cancel anytime. All plans include WhatsApp API access.</p>
    </div>
    <div class="billing-toggle">
        <button class="active" id="billToggleMonthly" onclick="billSetPeriod('monthly')">Monthly</button>
        <button id="billToggleYearly" onclick="billSetPeriod('yearly')">
            Yearly <span class="badge bg-success ms-1 rounded-pill" style="font-size:10px;">Save 20%</span>
        </button>
    </div>
</div>

<!-- Plans Grid -->
<div class="row g-4 mb-5" id="billPlansGrid">
    <div class="col-12 text-center py-5">
        <div class="spinner-border text-success mb-3" role="status"></div>
        <div class="text-muted">Loading plans…</div>
    </div>
</div>

<!-- Invoice History -->
<div class="card fu-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-receipt me-2"></i>Invoice History</span>
        <button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="billLoadInvoices()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>
    <div class="card-body p-0" id="billInvoices">
        <div class="text-center text-muted py-5">
            <div class="spinner-border spinner-border-sm text-success me-2"></div>Loading…
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Payment Modal -->
<div class="modal fade" id="billPayModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Confirm Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Plan summary -->
                <div class="p-3 rounded-3 mb-3" style="background:#f6f8fa;border:1px solid #e5e5e5;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="font-size:13px;">Plan</span>
                        <span class="fw-bold" style="font-size:13px;" id="payPlanName">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted" style="font-size:13px;">Period</span>
                        <span class="fw-semibold" style="font-size:13px;" id="payPeriod">Monthly</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted" style="font-size:13px;">Amount</span>
                        <span class="fw-bold text-success" style="font-size:18px;" id="payAmount">₹—</span>
                    </div>
                </div>

                <!-- Features -->
                <ul class="list-unstyled mb-3" id="payFeatures" style="font-size:13px;color:#555;"></ul>

                <!-- Razorpay note -->
                <div class="alert alert-info rounded-3 py-2 mb-0" style="font-size:12px;">
                    <i class="bi bi-info-circle me-1"></i>
                    You'll be redirected to Razorpay's secure payment page to complete your purchase.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-fu-primary rounded-3 px-4" id="payConfirmBtn" onclick="billPay()">
                    <i class="bi bi-lock-fill me-1"></i>Pay Securely
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirm -->
<div class="modal fade" id="billCancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center py-4 px-4">
                <div style="font-size:48px;margin-bottom:12px;">⚠️</div>
                <h6 class="fw-bold mb-2">Cancel Subscription?</h6>
                <p class="text-muted mb-0" style="font-size:13px;">You'll lose access to premium features at the end of your billing period.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button class="btn btn-fu-primary rounded-3 px-4" data-bs-dismiss="modal">Keep Plan</button>
                <button class="btn btn-outline-danger rounded-3 px-4" onclick="billConfirmCancel()">Cancel Plan</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let billPeriod='monthly', billAllPlans=[], billSelPlan=null;
let billPayModal, billCancelModal;

document.addEventListener('DOMContentLoaded',()=>{
    billPayModal    = new bootstrap.Modal(document.getElementById('billPayModal'));
    billCancelModal = new bootstrap.Modal(document.getElementById('billCancelModal'));
    billLoadCurrent();
    billLoadPlans();
    billLoadInvoices();
});

/* ── CURRENT PLAN ── */
function billLoadCurrent(){
    fetch('/billing/current').then(r=>r.json()).then(data=>{
        if(!data.plan){document.getElementById('billCurrentWrap').style.display='none';return;}
        document.getElementById('billCurrentWrap').style.display='block';
        document.getElementById('billCurrName').textContent   = data.plan.name+' Plan';
        document.getElementById('billCurrStatus').textContent = 'Status: '+(data.status||'').toUpperCase()+' · Expires: '+(data.expires_at||'').substring(0,10);
        document.getElementById('billCurrMsgs').textContent   = data.plan.messages_limit>=99999?'∞':parseInt(data.plan.messages_limit).toLocaleString();
        document.getElementById('billCurrCon').textContent    = data.plan.contacts_limit>=99999?'∞':parseInt(data.plan.contacts_limit).toLocaleString();
        document.getElementById('billCurrTeam').textContent   = data.plan.team_limit>=99999?'∞':data.plan.team_limit;
        const days=data.days_left||0; const total=30;
        const pct=Math.max(0,Math.min(100,Math.round(days/total*100)));
        document.getElementById('billDaysBar').style.width=pct+'%';
        document.getElementById('billDaysLeft').textContent=days+' days remaining';
        if(days<7)document.getElementById('billDaysBar').style.background='#e53935';
        else if(days<15)document.getElementById('billDaysBar').style.background='#f57c00';
    }).catch(()=>{});
}

/* ── PLANS ── */
function billSetPeriod(p){
    billPeriod=p;
    document.getElementById('billToggleMonthly').classList.toggle('active',p==='monthly');
    document.getElementById('billToggleYearly').classList.toggle('active',p==='yearly');
    billRenderPlans();
}

function billLoadPlans(){
    fetch('/billing/plans').then(r=>r.json()).then(data=>{
        billAllPlans=data.plans||[];billRenderPlans();
    }).catch(()=>{
        document.getElementById('billPlansGrid').innerHTML=`<div class="col-12"><div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load plans.</p></div></div>`;
    });
}

function billRenderPlans(){
    const grid=document.getElementById('billPlansGrid');

    if(!billAllPlans.length){ grid.innerHTML=`<div class="col-12 text-center text-muted py-5"><div style="font-size:48px;opacity:0.2;">💳</div><p>No plans available yet.</p></div>`; return; }
    grid.innerHTML=billAllPlans.map((p,i)=>{
        const price=billPeriod==='yearly'?(p.yearly_price||Math.round((p.price||0)*0.8)):(p.price||0);
        const isPopular=p.is_popular||(i===1);
        const features=p.features?(typeof p.features==='string'?p.features.split(','):[p.features]):(p.description?p.description.split(','):['Full access']);
        return `<div class="col-md-4">
            <div class="plan-card ${isPopular?'popular':''} position-relative">
                ${isPopular?'<div class="popular-label">⭐ Most Popular</div>':''}
                <div class="plan-name">${escHtml(p.name)}</div>
                <div class="plan-price">₹${parseInt(price).toLocaleString()}<span>/mo</span></div>
                ${billPeriod==='yearly'?`<div style="font-size:11px;color:#25d366;font-weight:600;margin-bottom:4px;">Save 20% with yearly billing</div>`:''}
                <div class="plan-desc">${escHtml(p.desc||p.description||'')}</div>
                <ul class="plan-features">
                    ${features.map(f=>`<li>${escHtml(String(f).trim())}</li>`).join('')}
                </ul>
                <button class="btn ${p.is_free_trial||p.price==0?'btn-fu-outline':'btn-fu-primary'} rounded-3 w-100"
                    onclick="billOpenPayment(${JSON.stringify({id:p.id,name:p.name,price:price,is_free_trial:p.is_free_trial||p.price==0,features:features,desc:p.desc||p.description||''}).replace(/"/g,'&quot;')})">
                    ${p.is_free_trial||p.price==0?'🎁 Start Free Trial':'🚀 Subscribe'}
                </button>
            </div>
        </div>`;
    }).join('');
}

/* ── PAYMENT ── */
function billOpenPayment(plan){
    if(typeof plan==='string')plan=JSON.parse(plan.replace(/&quot;/g,'"'));
    billSelPlan=plan;
    document.getElementById('payPlanName').textContent = plan.name+' Plan';
    const isFree = plan.is_free_trial || plan.price == 0;
    document.getElementById('payConfirmBtn').innerHTML = isFree ? '<i class="bi bi-gift me-1"></i>Activate Free Trial' : '<i class="bi bi-lock-fill me-1"></i>Pay Securely';
    document.getElementById('payAmount').textContent = isFree ? 'FREE (30 days)' : '₹'+parseInt(plan.price).toLocaleString();
    document.getElementById('payPeriod').textContent   = billPeriod==='yearly'?'Yearly (20% off)':'Monthly';
    document.getElementById('payAmount').textContent   = '₹'+parseInt(plan.price).toLocaleString();
    document.getElementById('payFeatures').innerHTML   = (plan.features||[]).map(f=>`<li><i class="bi bi-check-circle-fill text-success me-2"></i>${escHtml(String(f).trim())}</li>`).join('');
    billPayModal.show();
}

function billPay(){
    if(!billSelPlan)return;
    const btn=document.getElementById('payConfirmBtn');
    btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Processing…';
    fetch('/billing/create-order',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify({plan_id:billSelPlan.id,period:billPeriod})
    }).then(r=>r.json()).then(data=>{
        // Free trial activated
        if(data.free_trial){
            btn.disabled=false;btn.innerHTML='<i class="bi bi-lock-fill me-1"></i>Activate';
            billPayModal.hide();
            showToast('✅ '+data.message,'success');
            setTimeout(()=>location.reload(),1500);
            return;
        }
        if(data.error){btn.disabled=false;btn.innerHTML='<i class="bi bi-lock-fill me-1"></i>Pay Securely';showToast('❌ '+data.error,'error');return;}
        btn.disabled=false;btn.innerHTML='<i class="bi bi-lock-fill me-1"></i>Pay Securely';
        if(data.error){showToast('❌ '+data.error,'error');return;}
        // Razorpay
        const options={
            key:'{{ config("services.razorpay.key_id","rzp_test_SxV2T8cJjYU3Ef") }}',
            amount:data.amount,currency:data.currency||'INR',
            name:'Fuiox Technologies',description:billSelPlan.name+' Plan',
            order_id:data.order_id,
            handler:function(response){billVerify(response);},
            prefill:{email:'{{ $user->email }}',contact:'{{ $user->mobile }}'},
            theme:{color:'#25d366'}
        };
        billPayModal.hide();
        const rzp=new Razorpay(options);rzp.open();
    }).catch(()=>{btn.disabled=false;btn.innerHTML='<i class="bi bi-lock-fill me-1"></i>Pay Securely';showToast('❌ Could not create order','error');});
}

function billVerify(response){
    const payload = Object.assign({}, response, {
        plan_id: billSelPlan.id,
        billing_cycle: billPeriod
    });
    fetch('/billing/verify-payment',{
        method:'POST',credentials:'same-origin',
        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body:JSON.stringify(payload)
    }).then(r=>r.json()).then(data=>{
        if(data.error){showToast('❌ '+data.error,'error');return;}
        showToast('🎉 Payment successful! '+data.plan+' plan activated.','success');
        setTimeout(()=>location.reload(),1500);
    }).catch(()=>showToast('❌ Verification error','error'));
}

/* ── INVOICES ── */
function billLoadInvoices(){
    const box=document.getElementById('billInvoices');
    box.innerHTML='<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm text-success me-2"></div>Loading…</div>';
    fetch('/billing/invoices').then(r=>r.json()).then(data=>{
        const invs=data.invoices||[];
        if(!invs.length){box.innerHTML='<div class="empty-state"><div class="empty-state-icon">🧾</div><p>No invoices yet. Subscribe to a plan to get started.</p></div>';return;}
        box.innerHTML=`<div class="table-responsive"><table class="table inv-table mb-0">
            <thead><tr>
                <th>Plan</th><th>Amount</th><th>Period</th><th>Status</th><th>Date</th><th class="text-end">Invoice</th>
            </tr></thead>
            <tbody>${invs.map(i=>`<tr>
                <td class="fw-semibold">${escHtml(i.plan_name||'—')}</td>
                <td class="fw-bold text-success">₹${parseFloat(i.amount||0).toLocaleString()}</td>
                <td class="text-muted text-capitalize">${escHtml(i.period||'monthly')}</td>
                <td><span class="badge rounded-pill ${i.status==='active'?'bg-success':'bg-secondary'} px-3">${escHtml(i.status||'—')}</span></td>
                <td class="text-muted" style="font-size:12px;">${(i.created_at||'').substring(0,10)}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-secondary rounded-pill" onclick="showToast('Invoice #${i.id} — Download coming soon','success')"><i class="bi bi-download me-1"></i>PDF</button></td>
            </tr>`).join('')}</tbody>
        </table></div>`;
    }).catch(()=>{box.innerHTML='<div class="empty-state"><div class="empty-state-icon">⚠️</div><p>Could not load invoices.</p></div>';});
}

/* ── CANCEL ── */
function billConfirmCancel(){
    fetch('/billing/cancel',{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}})
    .then(r=>r.json()).then(()=>{
        document.getElementById('billCancelModal').querySelector('[data-bs-dismiss]').click();
        showToast('✅ Subscription cancelled','success');billLoadCurrent();
    }).catch(()=>showToast('❌ Failed','error'));
}
</script>
@endpush