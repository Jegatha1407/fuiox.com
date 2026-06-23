<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Fuiox Technologies — WhatsApp Business Platform</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">
<meta name="description" content="Fuiox is a powerful WhatsApp Business platform for bulk messages, campaigns and automation.">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root { --green:#25d366; --green-dark:#1fba58; --green-pale:#e8f5e9; --dark:#0d1117; --text:#1a1a2e; }
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Plus Jakarta Sans',sans-serif; color:var(--text); overflow-x:hidden; }
a { text-decoration:none; }

/* NAV */
.fu-nav { position:fixed; top:0; left:0; right:0; z-index:1050; background:rgba(255,255,255,.96); backdrop-filter:blur(12px); border-bottom:1px solid #f0f0f0; }
.fu-nav .navbar-brand { font-size:1.2rem; font-weight:800; color:var(--text); }
.fu-nav .navbar-brand span { color:var(--green); }
.fu-nav .nav-link { font-size:14px; font-weight:500; color:#555; transition:color .15s; padding:8px 14px !important; }
.fu-nav .nav-link:hover { color:var(--green); }
.btn-nav-login { font-size:14px; font-weight:600; color:var(--text); padding:8px 20px; border-radius:8px; border:1.5px solid #e5e5e5; transition:.15s; background:#fff; }
.btn-nav-login:hover { border-color:var(--green); color:var(--green); }
.btn-nav-cta { font-size:14px; font-weight:700; color:#fff !important; padding:9px 22px; border-radius:8px; background:var(--green); transition:.15s; }
.btn-nav-cta:hover { background:var(--green-dark); color:#fff; }

/* HERO — padding-top must be > navbar height (64px) */
.fu-hero {
    padding-top: 110px;   /* ← key fix: enough to clear the fixed navbar */
    padding-bottom: 80px;
    background:linear-gradient(135deg,#f0fdf4 0%,#e8f5e9 40%,#fff 100%);
    position:relative; overflow:hidden;
}
.fu-hero::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle at 30% 50%,rgba(37,211,102,.07) 0%,transparent 60%); pointer-events:none; }
.hero-badge { display:inline-flex; align-items:center; gap:6px; background:var(--green-pale); color:#2e7d32; font-size:13px; font-weight:600; padding:6px 14px; border-radius:20px; margin-bottom:20px; }
.fu-hero h1 { font-size:clamp(34px,6vw,66px); font-weight:900; line-height:1.1; }
.fu-hero h1 span { color:var(--green); }
.fu-hero p   { font-size:clamp(15px,2vw,18px); color:#555; line-height:1.7; max-width:580px; }
.hero-stat .num { font-size:30px; font-weight:900; color:var(--green); }
.hero-stat .lbl { font-size:12px; color:#888; margin-top:2px; }
.btn-hero-primary { background:var(--green); color:#fff; padding:13px 28px; border-radius:10px; font-size:15px; font-weight:700; transition:.15s; display:inline-flex; align-items:center; gap:8px; border:none; cursor:pointer; }
.btn-hero-primary:hover { background:var(--green-dark); color:#fff; transform:translateY(-1px); }
.btn-hero-outline { background:#fff; color:var(--text); border:2px solid #e5e5e5; padding:13px 28px; border-radius:10px; font-size:15px; font-weight:700; transition:.15s; display:inline-flex; align-items:center; gap:8px; }
.btn-hero-outline:hover { border-color:var(--green); color:var(--green); }

/* SECTION */
.fu-section { padding:80px 0; }
.section-tag   { font-size:12px; font-weight:700; color:var(--green); text-transform:uppercase; letter-spacing:1.2px; margin-bottom:10px; }
.section-title { font-size:clamp(24px,4vw,40px); font-weight:800; line-height:1.2; margin-bottom:14px; }
.section-sub   { font-size:16px; color:#666; line-height:1.7; max-width:560px; }

/* FEATURE CARDS */
.feature-card { background:#f9fafb; border-radius:16px; padding:26px; border:1px solid #f0f0f0; transition:.2s; height:100%; }
.feature-card:hover { border-color:var(--green); transform:translateY(-4px); box-shadow:0 8px 32px rgba(37,211,102,.12); }
.feature-icon { font-size:34px; margin-bottom:12px; }
.feature-card h5 { font-size:16px; font-weight:700; margin-bottom:8px; }
.feature-card p  { font-size:13px; color:#666; line-height:1.7; }

/* STEPS */
.step-num { width:50px; height:50px; border-radius:50%; background:var(--green); color:#fff; font-size:18px; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.step-card { text-align:center; padding:20px; }

/* PRICING */
.plan-card { border-radius:20px; padding:30px; border:2px solid #f0f0f0; position:relative; display:flex; flex-direction:column; height:100%; background:#f9fafb; transition:.2s; }
.plan-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.08); }
.plan-card.popular { background:var(--dark); border-color:var(--green); }
.popular-badge { position:absolute; top:-13px; left:50%; transform:translateX(-50%); background:var(--green); color:#fff; font-size:11px; font-weight:700; padding:4px 16px; border-radius:20px; white-space:nowrap; }
.plan-name  { font-size:13px; font-weight:700; color:#888; margin-bottom:6px; }
.plan-price { font-size:40px; font-weight:900; color:var(--text); margin-bottom:4px; }
.plan-price small { font-size:14px; font-weight:400; color:#888; }
.plan-card.popular .plan-name  { color:rgba(255,255,255,.5); }
.plan-card.popular .plan-price { color:#fff; }
.plan-card.popular .plan-price small { color:rgba(255,255,255,.45); }
.plan-desc  { font-size:12px; color:#888; margin-bottom:18px; }
.plan-card.popular .plan-desc { color:rgba(255,255,255,.4); }
.plan-features { list-style:none; padding:0; flex:1; margin-bottom:22px; }
.plan-features li { font-size:13px; color:#333; padding:7px 0; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; gap:8px; }
.plan-features li::before { content:'✓'; color:var(--green); font-weight:700; flex-shrink:0; }
.plan-card.popular .plan-features li { color:rgba(255,255,255,.75); border-color:rgba(255,255,255,.1); }

/* TESTIMONIALS */
.testi-card { background:#fff; border-radius:14px; padding:26px; box-shadow:0 2px 14px rgba(0,0,0,.06); height:100%; }
.testi-stars { color:#f59e0b; font-size:14px; margin-bottom:10px; }
.testi-text  { font-size:14px; color:#333; line-height:1.7; font-style:italic; margin-bottom:14px; }
.testi-avatar { width:38px; height:38px; border-radius:50%; background:var(--green); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }

/* CONTACT */
.contact-section { background:#f9fafb; }
.contact-card { background:#fff; border-radius:18px; padding:36px; box-shadow:0 4px 20px rgba(0,0,0,.07); border:1px solid #f0f0f0; }
.contact-info-item { display:flex; align-items:flex-start; gap:14px; margin-bottom:22px; }
.contact-info-icon { width:42px; height:42px; border-radius:10px; background:var(--green-pale); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:17px; color:var(--green); }
.fu-input { width:100%; padding:11px 14px; border:1.5px solid #e5e9f0; border-radius:10px; font-size:14px; outline:none; font-family:inherit; color:var(--text); background:#fafafa; transition:border-color .15s, box-shadow .15s; }
.fu-input:focus { border-color:var(--green); box-shadow:0 0 0 3px rgba(37,211,102,.1); background:#fff; }
.fu-label { font-size:11px; font-weight:700; color:#666; text-transform:uppercase; letter-spacing:.4px; display:block; margin-bottom:5px; }

/* CTA */
.fu-cta { background:linear-gradient(135deg,#0d1117,#1e2d3d); padding:90px 0; text-align:center; }

/* FOOTER */
.fu-footer { background:var(--dark); padding:56px 0 28px; color:rgba(255,255,255,.5); }
.fu-footer .brand { font-size:19px; font-weight:800; color:#fff; margin-bottom:10px; }
.fu-footer .brand span { color:var(--green); }
.fu-footer h6 { font-size:13px; font-weight:700; color:#fff; margin-bottom:12px; }
.fu-footer a  { font-size:13px; color:rgba(255,255,255,.4); display:block; margin-bottom:8px; transition:color .15s; }
.fu-footer a:hover { color:var(--green); }
.fu-footer .footer-bottom { border-top:1px solid rgba(255,255,255,.08); padding-top:20px; font-size:12px; }

/* WA FLOAT */
.wa-float { position:fixed; bottom:24px; right:24px; width:54px; height:54px; background:var(--green); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 18px rgba(37,211,102,.4); z-index:999; transition:transform .2s; }
.wa-float:hover { transform:scale(1.1); }
.wa-float svg { width:26px; height:26px; fill:#fff; }
/* REVIEWS */
.add-review-btn { display:inline-block; padding:13px 28px; border:none; border-radius:10px; background:var(--green); color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:.15s; box-shadow:0 4px 18px rgba(37,211,102,.3); }
.add-review-btn:hover { background:var(--green-dark); transform:translateY(-1px); }
.review-modal { display:none; position:fixed; inset:0; background:rgba(15,23,42,.45); backdrop-filter:blur(5px); justify-content:center; align-items:center; z-index:9999; }
.review-box { width:440px; max-width:92vw; background:#fff; padding:32px; border-radius:24px; box-shadow:0 25px 70px rgba(0,0,0,.15); }
.review-box input, .review-box select, .review-box textarea { width:100%; padding:12px 14px; margin-bottom:14px; border-radius:10px; border:1.5px solid #e2e8f0; font-size:14px; outline:none; font-family:inherit; transition:border-color .15s; }
.review-box input:focus, .review-box textarea:focus, .review-box select:focus { border-color:var(--green); }
.review-box textarea { height:110px; resize:none; }
.submit-review { width:100%; padding:13px; border:none; border-radius:10px; background:var(--green); color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:.15s; }
.submit-review:hover { background:var(--green-dark); }
@media(max-width:768px){
    .fu-hero { padding-top:90px; }
}
</style>
</head>
<body>

{{-- ══ NAVBAR ══ --}}
<nav class="fu-nav navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Fuiox <span>Technologies</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how">How it works</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>
            <div class="d-flex gap-2 mt-2 mt-lg-0 align-items-center">
                <a href="{{ route('login') }}" class="btn-nav-login">Login</a>
                <a href="{{ route('register') }}" class="btn-nav-cta">Get Started Free</a>
            </div>
        </div>
    </div>
</nav>

{{-- ══ HERO ══ --}}
<section class="fu-hero" id="home">
    <div class="container text-center">
        <div class="hero-badge">🚀 WhatsApp Business Platform</div>
        <h1 class="mb-3">Send WhatsApp Messages<br>at <span>Scale</span></h1>
        <p class="mx-auto mb-4">Fuiox helps businesses connect with customers via WhatsApp — bulk campaigns, auto-replies, contact management and real-time chat in one platform.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap mb-5">
            <a href="{{ route('register') }}" class="btn-hero-primary">🚀 Start Free Trial</a>
            <a href="#features" class="btn-hero-outline">See Features →</a>
        </div>
        <div class="row g-3 justify-content-center mt-2">
            <div class="col-6 col-sm-3 hero-stat"><div class="num">10K+</div><div class="lbl">Messages Sent Daily</div></div>
            <div class="col-6 col-sm-3 hero-stat"><div class="num">500+</div><div class="lbl">Happy Businesses</div></div>
            <div class="col-6 col-sm-3 hero-stat"><div class="num">98%</div><div class="lbl">Delivery Rate</div></div>
            <div class="col-6 col-sm-3 hero-stat"><div class="num">24/7</div><div class="lbl">Support Available</div></div>
        </div>
    </div>
</section>

{{-- ══ FEATURES ══ --}}
<section class="fu-section bg-white" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Features</div>
            <div class="section-title">Everything you need to grow on WhatsApp</div>
            <p class="section-sub mx-auto">From bulk messaging to AI-powered automation — Fuiox has all the tools your business needs.</p>
        </div>
        <div class="row g-4">
            @php $features = [
                ['💬','Real-time Chat','Manage all your WhatsApp conversations in one clean inbox. Reply, react, forward and manage messages with ease.'],
                ['📢','Bulk Campaigns','Send WhatsApp template messages to thousands of contacts at once. Track delivery, read rates and responses.'],
                ['🤖','Smart Automation','Set up keyword triggers, welcome messages and out-of-office replies. Never miss a customer enquiry again.'],
                ['👥','Contact Management','Import contacts from CSV, organize into groups, add tags and notes. Keep your customer data organized.'],
                ['📈','Analytics & Reports','Track message delivery rates, campaign performance and hourly activity with beautiful charts.'],
                ['👨‍💼','Team Management','Add agents and admins. Share your WhatsApp number across your entire support team.'],
                ['📝','Template Manager','Create and send WhatsApp approved templates. Support for marketing, utility and authentication.'],
                ['🔗','WhatsApp API','Powered by the official Meta WhatsApp Business API. Enterprise-grade reliability with 99.9% uptime.'],
                ['📱','Mobile Responsive','Access your dashboard from any device. Manage chats and send campaigns anywhere.'],
            ]; @endphp
            @foreach($features as $f)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">{{ $f[0] }}</div>
                    <h5>{{ $f[1] }}</h5>
                    <p>{{ $f[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ HOW IT WORKS ══ --}}
<section class="fu-section" style="background:#f9fafb;" id="how">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">How it works</div>
            <div class="section-title">Get started in minutes</div>
            <p class="section-sub mx-auto">No technical knowledge required. Connect your WhatsApp and start messaging.</p>
        </div>
        <div class="row g-4">
            @php $steps=[['Create Account','Sign up with your email. Verify with OTP. Takes less than 2 minutes.'],['Connect WhatsApp','Paste your WhatsApp Business API token. We auto-detect your phone number and business account.'],['Import Contacts','Upload your contact list via CSV or add contacts manually. Organize into groups and tags.'],['Start Messaging','Send bulk campaigns, set up automations and manage all chats from one dashboard.']]; @endphp
            @foreach($steps as $i=>$s)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="step-card">
                    <div class="step-num">{{ $i+1 }}</div>
                    <h5 class="fw-bold mb-2">{{ $s[0] }}</h5>
                    <p class="text-muted" style="font-size:14px;line-height:1.6;">{{ $s[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ PRICING ══ --}}
<section class="fu-section bg-white" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Pricing</div>
            <div class="section-title">Simple, transparent pricing</div>
            <p class="section-sub mx-auto">No hidden fees. Cancel anytime. All plans include WhatsApp API access.</p>
        </div>
        <div class="row g-4 align-items-stretch justify-content-center">
            <div class="col-12 col-md-4">
                <div class="plan-card">
                    <div class="plan-name">Starter</div>
                    <div class="plan-price">₹999<small>/month</small></div>
                    <div class="plan-desc">Perfect for small businesses</div>
                    <ul class="plan-features">
                        <li>1 WhatsApp Number</li><li>1,000 messages/month</li><li>500 contacts</li><li>Basic automation</li><li>Chat inbox</li><li>Email support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-dark w-100 fw-bold rounded-3 py-3">Get Started</a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="plan-card popular">
                    <div class="popular-badge">⭐ Most Popular</div>
                    <div class="plan-name">Professional</div>
                    <div class="plan-price">₹2,999<small>/month</small></div>
                    <div class="plan-desc">For growing businesses</div>
                    <ul class="plan-features">
                        <li>1 WhatsApp Number</li><li>10,000 messages/month</li><li>Unlimited contacts</li><li>Advanced automation</li><li>Bulk campaigns</li><li>Team members (5)</li><li>Analytics & reports</li><li>Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn w-100 fw-bold rounded-3 py-3" style="background:var(--green);color:#fff;">Start Free Trial</a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="plan-card">
                    <div class="plan-name">Enterprise</div>
                    <div class="plan-price">₹7,999<small>/month</small></div>
                    <div class="plan-desc">For large teams</div>
                    <ul class="plan-features">
                        <li>Multiple WhatsApp Numbers</li><li>Unlimited messages</li><li>Unlimited contacts</li><li>Full automation suite</li><li>Unlimited campaigns</li><li>Unlimited team members</li><li>Advanced analytics</li><li>Dedicated support</li><li>Custom integrations</li>
                    </ul>
                    <a href="#contact" class="btn btn-outline-dark w-100 fw-bold rounded-3 py-3">Contact Sales</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ TESTIMONIALS / REVIEWS ══ --}}
<section class="fu-section" style="background:#f9fafb;" id="reviews">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-tag">Reviews</div>
            <div class="section-title">Loved by businesses</div>
            <p class="section-sub mx-auto">Join hundreds of businesses already growing with Fuiox.</p>
        </div>

        {{-- Static + Dynamic reviews all in one grid --}}
        <div class="row g-4" id="reviewList">
            {{-- Static reviews --}}
            @php $testis=[
                ['"Fuiox transformed how we communicate with customers. Our response rate improved by 3x after switching to WhatsApp campaigns."','R','Rahul Sharma','Marketing Manager, TechCorp'],
                ['"The automation feature saves us 3 hours every day. Welcome messages and keyword replies work perfectly out of the box."','P','Priya Nair','Owner, Beauty Studio'],
                ['"Setting up was incredibly easy. We imported 2000 contacts and sent our first campaign within 30 minutes of signing up."','A','Arjun Patel','CEO, E-commerce Store']
            ]; @endphp
            @foreach($testis as $t)
            <div class="col-12 col-md-4 static-review">
                <div class="testi-card">
                    <div class="testi-stars">★★★★★</div>
                    <div class="testi-text">{{ $t[0] }}</div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="testi-avatar">{{ $t[1] }}</div>
                        <div>
                            <div class="fw-bold" style="font-size:14px;">{{ $t[2] }}</div>
                            <div class="text-muted" style="font-size:12px;">{{ $t[3] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            {{-- Dynamic reviews from DB load here via JS --}}
        </div>

        <div class="text-center mt-5">
            <button class="add-review-btn" onclick="openReviewForm()">
                ✍️ Add Your Review
            </button>
        </div>
    </div>
</section>

{{-- Review Form Modal --}}
<div class="review-modal" id="reviewModal">
    <div class="review-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="margin:0;font-size:20px;font-weight:800;color:#111827;">Share Your Experience</h3>
            <button onclick="closeReviewForm()" style="background:none;border:none;font-size:22px;color:#888;cursor:pointer;line-height:1;">×</button>
        </div>
        <input id="rev_name" type="text" placeholder="Your Name *">
        <select id="rev_rating">
            <option value="5">★★★★★ — Excellent</option>
            <option value="4">★★★★ — Very Good</option>
            <option value="3">★★★ — Good</option>
            <option value="2">★★ — Fair</option>
            <option value="1">★ — Poor</option>
        </select>
        <textarea id="rev_message" placeholder="Write your review… *"></textarea>
        <div id="rev_error" style="display:none;color:#e53935;font-size:13px;margin-bottom:10px;"></div>
        <button type="button" class="submit-review" id="rev_submit_btn" onclick="submitReview()">
            Submit Review
        </button>
    </div>
</div>

{{-- Success Modal --}}
<div id="reviewSuccessModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(5px);justify-content:center;align-items:center;z-index:9999;">
    <div style="background:#fff;border-radius:24px;padding:40px;width:380px;max-width:90vw;text-align:center;box-shadow:0 25px 70px rgba(0,0,0,.15);">
        <div style="font-size:52px;margin-bottom:12px;">🎉</div>
        <div style="font-size:20px;font-weight:800;color:#111827;margin-bottom:8px;">Thank You!</div>
        <div style="font-size:14px;color:#666;margin-bottom:24px;">Your review has been submitted successfully.</div>
        <button onclick="document.getElementById('reviewSuccessModal').style.display='none';" style="padding:12px 32px;background:#25d366;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;">Done</button>
    </div>
</div>
<!-- </section>
        <div class="row g-4">
            @php $testis=[['"Fuiox transformed how we communicate with customers. Our response rate improved by 3x after switching to WhatsApp campaigns."','R','Rahul Sharma','Marketing Manager, TechCorp'],['"The automation feature saves us 3 hours every day. Welcome messages and keyword replies work perfectly out of the box."','P','Priya Nair','Owner, Beauty Studio'],['"Setting up was incredibly easy. We imported 2000 contacts and sent our first campaign within 30 minutes of signing up."','A','Arjun Patel','CEO, E-commerce Store']]; @endphp
            @foreach($testis as $t)
            <div class="col-12 col-md-4">
                <div class="testi-card">
                    <div class="testi-stars">★★★★★</div>
                    <div class="testi-text">{{ $t[0] }}</div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="testi-avatar">{{ $t[1] }}</div>
                        <div><div class="fw-bold" style="font-size:14px;">{{ $t[2] }}</div><div class="text-muted" style="font-size:12px;">{{ $t[3] }}</div></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section> -->

{{-- ══ CONTACT ══ --}}
<section class="fu-section contact-section" id="contact">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="section-tag">Get in Touch</div>
                <div class="section-title">Have a question?<br>We're here to help</div>
                <p class="section-sub mb-4">Send us your query and our team will get back to you on WhatsApp within minutes.</p>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-whatsapp"></i></div>
                    <div><div class="fw-bold mb-1">WhatsApp Support</div><div class="text-muted" style="font-size:14px;">+91 73580 13530</div></div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div><div class="fw-bold mb-1">Email Us</div><div class="text-muted" style="font-size:14px;">fuioxtechnologies@gmail.com</div></div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-clock-fill"></i></div>
                    <div><div class="fw-bold mb-1">Response Time</div><div class="text-muted" style="font-size:14px;">Within 30 minutes on WhatsApp</div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card">
                    <h5 class="fw-bold mb-1">Send us a message</h5>
                    <p class="text-muted mb-4" style="font-size:13px;">Your query will be sent directly to our WhatsApp.</p>
                    <div id="contactAlert" class="alert mb-3" style="display:none;"></div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="fu-label">Your Name *</label>
                            <input type="text" class="fu-input" id="cName" placeholder="Full name">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="fu-label">Phone Number *</label>
                            <input type="tel" class="fu-input" id="cPhone" placeholder="+91 9876543210">
                        </div>
                        <div class="col-12">
                            <label class="fu-label">Email Address</label>
                            <input type="email" class="fu-input" id="cEmail" placeholder="you@company.com">
                        </div>
                        <div class="col-12">
                            <label class="fu-label">Subject *</label>
                            <select class="fu-input" id="cSubject">
                                <option value="">Select a topic</option>
                                <option>General Enquiry</option><option>Pricing & Plans</option>
                                <option>Technical Support</option><option>WhatsApp API Setup</option>
                                <option>Partnership</option><option>Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="fu-label">Message *</label>
                            <textarea class="fu-input" id="cMessage" rows="4" placeholder="Tell us how we can help you…" style="resize:vertical;"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn w-100 fw-bold py-3 rounded-3" style="background:var(--green);color:#fff;font-size:15px;" onclick="sendContactQuery()">
                                <i class="bi bi-whatsapp me-2"></i>Send via WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ CTA ══ --}}
<section class="fu-cta">
    <div class="container">
        <h2 class="text-white fw-bold mb-3" style="font-size:clamp(24px,4vw,42px);">Ready to grow your business on WhatsApp?</h2>
        <p style="color:rgba(255,255,255,.65);font-size:17px;" class="mb-4">Join 500+ businesses already using Fuiox. Start your free trial today.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="{{ route('register') }}" class="btn-hero-primary">🚀 Start Free Trial</a>
            <a href="#contact" class="btn-hero-outline" style="background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.3);">📧 Contact Sales</a>
        </div>
    </div>
</section>

{{-- ══ FOOTER ══ --}}
<footer class="fu-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-5">
                <div class="brand">Fuiox <span>Technologies</span></div>
                <p style="font-size:14px;line-height:1.7;">The most powerful WhatsApp Business platform for growing businesses.</p>
            </div>
            <div class="col-6 col-md-2"><h6>Product</h6><a href="#features">Features</a><a href="#pricing">Pricing</a><a href="#how">How it works</a><a href="{{ route('register') }}">Sign Up</a></div>
            <div class="col-6 col-md-2"><h6>Company</h6><a href="#">About Us</a><a href="mailto:fuioxtechnologies@gmail.com">Contact</a><a href="#">Blog</a></div>
            <div class="col-6 col-md-3"><h6>Legal</h6><a href="{{ route('privacy') }}">Privacy Policy</a><a href="{{ route('terms') }}">Terms of Service</a><a href="{{ route('data.deletion') }}">Data Deletion</a></div>
        </div>
        <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2">
            <div>© {{ date('Y') }} Fuiox Technologies. All rights reserved.</div>
            <div><a href="{{ route('privacy') }}" class="d-inline">Privacy</a> &nbsp;·&nbsp; <a href="{{ route('terms') }}" class="d-inline">Terms</a> &nbsp;·&nbsp; <a href="mailto:jegatheeshwari1407@gmail.com" class="d-inline">Contact</a></div>
        </div>
    </div>
</footer>

<a href="https://wa.me/917358013530" target="_blank" class="wa-float" title="Chat with us">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function sendContactQuery() {
    const name=document.getElementById('cName').value.trim();
    const phone=document.getElementById('cPhone').value.trim();
    const email=document.getElementById('cEmail').value.trim();
    const subject=document.getElementById('cSubject').value;
    const message=document.getElementById('cMessage').value.trim();
    const alertEl=document.getElementById('contactAlert');
    if(!name||!phone||!subject||!message){
        alertEl.className='alert alert-danger'; alertEl.textContent='⚠️ Please fill in all required fields.'; alertEl.style.display='block'; return;
    }
    const text=`*New Query from Fuiox Website* 🌐\n\n*Name:* ${name}\n*Phone:* ${phone}${email?'\n*Email:* '+email:''}\n*Subject:* ${subject}\n\n*Message:*\n${message}`;
    alertEl.className='alert alert-success'; alertEl.textContent='✅ Opening WhatsApp…'; alertEl.style.display='block';
    setTimeout(()=>window.open(`https://wa.me/917358013530?text=${encodeURIComponent(text)}`,'_blank'),600);
}
document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click',e=>{ const t=document.querySelector(a.getAttribute('href')); if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth'});} });
});function openReviewForm(){
    document.getElementById('rev_name').value = '';
    document.getElementById('rev_rating').value = '5';
    document.getElementById('rev_message').value = '';
    document.getElementById('rev_error').style.display = 'none';
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewForm(){
    document.getElementById('reviewModal').style.display = 'none';
}

async function submitReview(){
    const name = document.getElementById('rev_name').value.trim();
    const rating = document.getElementById('rev_rating').value;
    const message = document.getElementById('rev_message').value.trim();
    const errDiv = document.getElementById('rev_error');
    const btn = document.getElementById('rev_submit_btn');

    if(!name || !message){
        errDiv.textContent = '⚠️ Please fill in your name and review.';
        errDiv.style.display = 'block';
        return;
    }

    btn.textContent = 'Submitting…';
    btn.disabled = true;

    try {
        const res = await fetch('/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({name, rating, message})
        });

        if(res.ok){
            document.getElementById('reviewModal').style.display = 'none';
            document.getElementById('reviewSuccessModal').style.display = 'flex';
            loadDynamicReviews();
        } else {
            errDiv.textContent = '❌ Something went wrong. Please try again.';
            errDiv.style.display = 'block';
        }
    } catch(e) {
        errDiv.textContent = '❌ Network error. Please try again.';
        errDiv.style.display = 'block';
    } finally {
        btn.textContent = 'Submit Review';
        btn.disabled = false;
    }
}

async function loadDynamicReviews(){
    try {
        const res = await fetch('/reviews');
        const reviews = await res.json();
        // Remove previously loaded dynamic cards
        document.querySelectorAll('.dynamic-review').forEach(el => el.remove());
        const grid = document.getElementById('reviewList');
        reviews.forEach(review => {
            const stars = '★'.repeat(parseInt(review.rating)) + '☆'.repeat(5 - parseInt(review.rating));
            const initial = review.name.charAt(0).toUpperCase();
            const col = document.createElement('div');
            col.className = 'col-12 col-md-4 dynamic-review';
            col.innerHTML = `
                <div class="testi-card">
                    <div class="testi-stars">${stars}</div>
                    <div class="testi-text">"${review.message}"</div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="testi-avatar">${initial}</div>
                        <div>
                            <div class="fw-bold" style="font-size:14px;">${review.name}</div>
                            <div class="text-muted" style="font-size:12px;">Verified User</div>
                        </div>
                    </div>
                </div>`;
            grid.appendChild(col);
        });
    } catch(e) {}
}

// Load dynamic reviews on page load
loadDynamicReviews();

// Close review modal on backdrop click
document.getElementById('reviewModal').addEventListener('click', function(e){
    if(e.target === this) closeReviewForm();
});
</script>
</body>
</html>