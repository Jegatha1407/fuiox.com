@extends('layouts.app')
@section('title', 'AI Settings')
@section('page_title', 'AI Chat Settings')

@push('styles')
<style>
.ai-card { background:#fff; border:1.5px solid #e5e9f0; border-radius:16px; padding:28px; margin-bottom:20px; }
.ai-card-title { font-size:16px; font-weight:800; color:#1a1a2e; margin-bottom:4px; }
.ai-card-sub { font-size:13px; color:#888; margin-bottom:20px; }
.fu-label { font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:5px; display:block; }
.fu-inp { width:100%; padding:11px 14px; border:1.5px solid #e5e9f0; border-radius:10px; font-size:14px; outline:none; font-family:inherit; transition:.2s; }
.fu-inp:focus { border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,0.1); }
.test-bubble { background:#f0f2f5; border-radius:12px; padding:14px; margin-bottom:10px; font-size:14px; color:#1a1a2e; line-height:1.6; }
.test-bubble.ai { background:#e8f5e9; border-left:3px solid #25d366; }
.ai-badge { display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; margin-bottom:16px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">

@if(session('success'))
<div class="alert alert-success rounded-3 mb-4">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">

        {{-- AI Status --}}
        <div class="ai-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="ai-card-title">🤖 Fuiox AI Assistant</div>
                    <div class="ai-card-sub"> Automatically replies to customer messages when bot is ON</div>
                </div>
              
            </div>
            <div class="alert alert-info rounded-3 py-2" style="font-size:13px;">
                <i class="bi bi-info-circle me-1"></i>
                To enable AI replies — go to <strong>Chat</strong> page and turn ON the <strong>Bot toggle</strong> for a conversation.
            </div>
        </div>

        {{-- API Key Form --}}
        <div class="ai-card">
            <div class="ai-card-title">Your Claude API Key</div>
            <div class="ai-card-sub">Bring your own Anthropic API key &mdash; you are billed directly by Anthropic for usage, not by Fuiox.</div>

            @if($errors->has('claude_api_key'))
            <div class="alert alert-danger rounded-3 py-2 mb-3" style="font-size:13px;">{{ $errors->first('claude_api_key') }}</div>
            @endif

            <form method="POST" action="{{ route('ai.settings.save') }}" id="apiKeyForm">
                @csrf
                <input type="hidden" name="business_name" value="{{ $settings->business_name ?? '' }}">
                <input type="hidden" name="business_description" value="{{ $settings->business_description ?? '' }}">
                <input type="hidden" name="tone" value="{{ $settings->tone ?? 'friendly and professional' }}">
                <input type="hidden" name="language" value="{{ $settings->language ?? 'English' }}">
                <input type="hidden" name="custom_prompt" value="{{ $settings->custom_prompt ?? '' }}">

                <div class="mb-3">
                    <label for="claude_api_key" class="fu-label">Anthropic API Key *</label>
                    <input type="text" id="claude_api_key" name="claude_api_key" class="fu-inp"
                        value="{{ ($settings->api_key_last4 ?? null) ? '____PLACEHOLDER____' . $settings->api_key_last4 : '' }}"
                        placeholder="sk-ant-api03-..." autocomplete="off"
                        onfocus="if(this.value.indexOf('____PLACEHOLDER____')===0) this.value=''">
                    <div style="font-size:11px;color:#aaa;margin-top:4px;">
                        Get your key from <a href="https://console.anthropic.com" target="_blank">console.anthropic.com</a> &rarr; API Keys.
                        @if($settings->api_key_valid ?? false)
                            <span style="color:#2e7d32;font-weight:700;">Currently valid</span>
                        @elseif($settings->claude_api_key ?? false)
                            <span style="color:#c62828;font-weight:700;">Currently invalid</span>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-fu-primary rounded-3 px-4">
                    <i class="bi bi-check-circle me-1"></i> Save and Validate Key
                </button>
            </form>
        </div>

        {{-- AI Settings Form --}}
        <div class="ai-card">
            <div class="ai-card-title">Business Configuration</div>
            <div class="ai-card-sub">Tell the AI exactly what your business does so it can answer customers accurately</div>

            <form method="POST" action="{{ route('ai.settings.save') }}">
                @csrf
                <input type="hidden" name="claude_api_key" value="">

                <div class="mb-3">
                    <label for="business_name" class="fu-label">Business Name *</label>
                    <input type="text" id="business_name" name="business_name" class="fu-inp"
                        value="{{ $settings->business_name ?? $user->organisation ?? '' }}"
                        placeholder="e.g. Novelx Technologies" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="business_description" class="fu-label">Short Business Overview</label>
                    <textarea id="business_description" name="business_description" class="fu-inp" rows="2"
                        placeholder="One or two lines about what your business is, in general.">{{ $settings->business_description ?? '' }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="fu-label">Business Purpose *</label>
                    <div class="d-flex gap-3">
                        @php $pt = $settings->purpose_type ?? 'service'; @endphp
                        <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                            <input type="radio" name="purpose_type" value="sales" {{ $pt=='sales'?'checked':'' }} onchange="toggleAiPurposeBlocks()"> Sales
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                            <input type="radio" name="purpose_type" value="service" {{ $pt=='service'?'checked':'' }} onchange="toggleAiPurposeBlocks()"> Service
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                            <input type="radio" name="purpose_type" value="both" {{ $pt=='both'?'checked':'' }} onchange="toggleAiPurposeBlocks()"> Both
                        </label>
                    </div>
                </div>

                <div class="mb-3" id="aiSalesBlock" style="display:none;">
                    <label for="sales_details" class="fu-label">What do you sell? (Products, pricing, offers)</label>
                    <textarea id="sales_details" name="sales_details" class="fu-inp" rows="4"
                        placeholder="e.g. We sell premium WhatsApp Business SaaS subscriptions. Plans start at ₹499/month. We offer a 30-day free trial. Yearly plans get 20% discount.">{{ $settings->sales_details ?? '' }}</textarea>
                </div>

                <div class="mb-3" id="aiServiceBlock" style="display:none;">
                    <label for="service_details" class="fu-label">What services do you provide? (Be detailed)</label>
                    <textarea id="service_details" name="service_details" class="fu-inp" rows="4"
                        placeholder="e.g. We provide WhatsApp API integration, automated chatbot setup, bulk campaign management, and 24/7 customer support for all subscribers.">{{ $settings->service_details ?? '' }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="phone_number" class="fu-label">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" class="fu-inp"
                            value="{{ $settings->phone_number ?? '' }}" placeholder="+91 98765 43210" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label for="facebook_link" class="fu-label">Facebook Page Link</label>
                        <input type="text" id="facebook_link" name="facebook_link" class="fu-inp"
                            value="{{ $settings->facebook_link ?? '' }}" placeholder="facebook.com/yourpage" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label for="instagram_link" class="fu-label">Instagram Link</label>
                        <input type="text" id="instagram_link" name="instagram_link" class="fu-inp"
                            value="{{ $settings->instagram_link ?? '' }}" placeholder="instagram.com/yourpage" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label for="google_business_link" class="fu-label">Google Business URL</label>
                        <input type="text" id="google_business_link" name="google_business_link" class="fu-inp"
                            value="{{ $settings->google_business_link ?? '' }}" placeholder="g.page/yourbusiness" autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tone" class="fu-label">Tone</label>
                    <select id="tone" name="tone" class="fu-inp">
                        <option value="friendly and professional" {{ ($settings->tone ?? '') == 'friendly and professional' ? 'selected' : '' }}>Friendly & Professional</option>
                        <option value="formal" {{ ($settings->tone ?? '') == 'formal' ? 'selected' : '' }}>Formal</option>
                        <option value="casual and friendly" {{ ($settings->tone ?? '') == 'casual and friendly' ? 'selected' : '' }}>Casual & Friendly</option>
                        <option value="helpful and empathetic" {{ ($settings->tone ?? '') == 'helpful and empathetic' ? 'selected' : '' }}>Helpful & Empathetic</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="fu-label">Languages (AI auto-detects and replies in the customer's language)</label>
                    @php
                        $savedLangs = [];
                        if (!empty($settings->languages)) { $decoded = json_decode($settings->languages, true); if (is_array($decoded)) $savedLangs = $decoded; }
                        if (empty($savedLangs)) $savedLangs = ['English'];
                        $allLangs = ['English','Tamil','Hindi','Telugu','Malayalam','Kannada'];
                    @endphp
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($allLangs as $lang)
                        <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                            <input type="checkbox" name="languages[]" value="{{ $lang }}" {{ in_array($lang,$savedLangs)?'checked':'' }}> {{ $lang }}
                        </label>
                        @endforeach
                    </div>
                    <div style="font-size:11px;color:#aaa;margin-top:4px;">First checked language is used as the default if the customer's language can't be detected.</div>
                </div>

                <div class="mb-4">
                    <label for="custom_prompt" class="fu-label">Custom Instructions (Optional)</label>
                    <textarea id="custom_prompt" name="custom_prompt" class="fu-inp" rows="4"
                        placeholder="e.g. Always greet customers by name. Never discuss pricing directly — ask them to contact our sales team. Our working hours are 9AM-6PM IST.">{{ $settings->custom_prompt ?? '' }}</textarea>
                </div>

                <button type="submit" class="btn btn-fu-primary rounded-3 px-4">
                    <i class="bi bi-save me-1"></i> Save Settings
                </button>
            </form>
        </div>

    </div>

    <div class="col-lg-5">

        {{-- Test AI --}}
        <div class="ai-card" style="position:sticky;top:80px;">
            <div class="ai-card-title">🧪 Test AI Reply</div>
            <div class="ai-card-sub">See how the AI responds before enabling it for customers</div>

            <div id="testChat" style="min-height:200px;max-height:400px;overflow-y:auto;margin-bottom:16px;">
                <div class="test-bubble" style="color:#888;text-align:center;">Send a test message to see AI response</div>
            </div>

            <div class="d-flex gap-2">
                <input type="text" id="testMsg" class="fu-inp" placeholder="Type a test message…" autocomplete="off"
                    onkeydown="if(event.key==='Enter') testAI()">
                <button onclick="testAI()" class="btn btn-fu-primary rounded-3 px-3" id="testBtn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>

            <div class="mt-3">
                <div style="font-size:11px;color:#aaa;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Quick Test Messages</div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['Hello', 'What services do you offer?', 'What are your prices?', 'How can I contact you?', 'Thank you'] as $q)
                    <button onclick="document.getElementById('testMsg').value='{{ $q }}';testAI()"
                        style="padding:5px 12px;border:1px solid #e5e9f0;border-radius:20px;background:#f9f9f9;font-size:12px;cursor:pointer;font-family:inherit;">
                        {{ $q }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

function toggleAiPurposeBlocks(){
    const pt = document.querySelector('input[name="purpose_type"]:checked')?.value || 'service';
    document.getElementById('aiSalesBlock').style.display = (pt==='sales'||pt==='both') ? 'block' : 'none';
    document.getElementById('aiServiceBlock').style.display = (pt==='service'||pt==='both') ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleAiPurposeBlocks);

function testAI(){
    const msg = document.getElementById('testMsg').value.trim();
    if(!msg) return;
    const chat = document.getElementById('testChat');
    const btn  = document.getElementById('testBtn');

    // Show user message
    chat.innerHTML += `<div class="test-bubble">${escHtml(msg)}</div>`;
    chat.innerHTML += `<div class="test-bubble ai" id="aiTyping"><span class="spinner-border spinner-border-sm me-1"></span> AI is thinking…</div>`;
    chat.scrollTop = chat.scrollHeight;

    document.getElementById('testMsg').value = '';
    btn.disabled = true;

    fetch('/ai/test', {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ message: msg })
    }).then(r => r.json()).then(d => {
        btn.disabled = false;
        document.getElementById('aiTyping')?.remove();
        if(d.error){
            chat.innerHTML += `<div class="test-bubble" style="color:#c62828;">❌ ${escHtml(d.error)}</div>`;
        } else {
            chat.innerHTML += `<div class="test-bubble ai"><i class="bi bi-stars me-1" style="color:#764ba2;"></i>${escHtml(d.reply)}</div>`;
        }
        chat.scrollTop = chat.scrollHeight;
    }).catch(() => {
        btn.disabled = false;
        document.getElementById('aiTyping')?.remove();
        chat.innerHTML += `<div class="test-bubble" style="color:#c62828;">❌ Network error</div>`;
    });
}

function escHtml(str){
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
