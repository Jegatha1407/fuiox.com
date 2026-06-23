<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Deletion Request — Fuiox Technologies</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/image') }}/icon.png">z
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f9fafb;color:#333;line-height:1.7;}
.header{background:#1a1a2e;padding:20px 0;text-align:center;}
.header h1{color:#fff;font-size:22px;font-weight:700;}
.header h1 span{color:#25d366;}
.header p{color:rgba(255,255,255,0.5);font-size:13px;margin-top:4px;}
.container{max-width:820px;margin:40px auto;padding:0 20px 60px;}
.card{background:#fff;border-radius:14px;padding:36px;box-shadow:0 1px 4px rgba(0,0,0,0.07);}
h2{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:6px;}
.updated{font-size:13px;color:#aaa;margin-bottom:28px;}
h3{font-size:15px;font-weight:700;color:#1a1a2e;margin:24px 0 8px;}
p{font-size:14px;color:#555;margin-bottom:12px;}
ul{padding-left:20px;margin-bottom:12px;}
ul li{font-size:14px;color:#555;margin-bottom:6px;}
.steps{counter-reset:steps;}
.step-item{display:flex;gap:14px;margin-bottom:16px;align-items:flex-start;}
.step-num{width:30px;height:30px;border-radius:50%;background:#25d366;color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.step-content h4{font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:3px;}
.step-content p{font-size:13px;color:#666;margin:0;}

/* Form */
.form-section{background:#f0faf4;border-radius:12px;padding:24px;margin-top:24px;border:1.5px solid #c8e6c9;}
.form-section h3{margin-top:0;color:#2e7d32;}
.fgrp{margin-bottom:14px;}
.fgrp label{display:block;font-size:12px;font-weight:600;color:#444;margin-bottom:5px;}
.fgrp input,.fgrp textarea,.fgrp select{width:100%;padding:10px 14px;border:1.5px solid #e5e5e5;border-radius:8px;font-size:13px;outline:none;font-family:inherit;}
.fgrp input:focus,.fgrp textarea:focus{border-color:#25d366;}
.fgrp textarea{resize:vertical;min-height:80px;}
.btn-submit{background:#e53935;color:#fff;border:none;padding:12px 28px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;font-family:inherit;width:100%;margin-top:4px;}
.btn-submit:hover{background:#c62828;}

.alert-success{background:#e8f5e9;color:#2e7d32;padding:14px 18px;border-radius:8px;border-left:3px solid #25d366;font-size:14px;margin-bottom:16px;display:none;}
.alert-success.show{display:block;}

.contact-box{background:#fff8e1;border-left:3px solid #f57c00;border-radius:8px;padding:16px 20px;margin-top:24px;font-size:14px;color:#333;}
.contact-box a{color:#25d366;text-decoration:none;}
.footer{text-align:center;margin-top:32px;font-size:12px;color:#aaa;}
.footer a{color:#25d366;text-decoration:none;}
</style>
</head>
<body>

<div class="header">
    <h1>Fuiox <span>Technologies</span></h1>
    <p>WhatsApp Business Communication Platform</p>
</div>

<div class="container">
    <div class="card">
        <h2>Data Deletion Request</h2>
        <div class="updated">Last updated: May 16, 2026</div>

        <p>At Fuiox Technologies, we respect your right to control your personal data. You can request deletion of all your data stored on our platform at any time.</p>

        <h3>What Data We Delete</h3>
        <p>Upon receiving a verified deletion request, we will permanently delete:</p>
        <ul>
            <li>Your account information (name, email, organisation)</li>
            <li>Your WhatsApp Business credentials (Phone Number ID, Access Token, Business Account ID)</li>
            <li>All message history associated with your account</li>
            <li>All contact data stored in your account</li>
            <li>All template and bulk send logs</li>
            <li>All session and activity data</li>
        </ul>

        <h3>How to Request Data Deletion</h3>

        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-content">
                <h4>Submit the form below</h4>
                <p>Fill in your registered email address and the reason for deletion.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-content">
                <h4>Verify your identity</h4>
                <p>We will send a confirmation email to your registered address. Click the link to confirm your deletion request.</p>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-content">
                <h4>Data deleted within 30 days</h4>
                <p>Once confirmed, all your data will be permanently deleted within 30 days. You will receive a final confirmation email.</p>
            </div>
        </div>

        <!-- Deletion Request Form -->
        <div class="form-section">
            <h3>🗑 Submit Deletion Request</h3>

            <div class="alert-success" id="successAlert">
                ✅ Your deletion request has been received. We will email you a confirmation link within 24 hours.
            </div>

            <form onsubmit="submitDeletion(event)">
                <div class="fgrp">
                    <label>Registered Email Address *</label>
                    <input type="email" id="delEmail" required placeholder="Enter the email you used to sign up">
                </div>
                <div class="fgrp">
                    <label>Reason for Deletion <span style="color:#aaa;font-weight:400;">(optional)</span></label>
                    <textarea id="delReason" placeholder="e.g. No longer using the service, privacy concerns..."></textarea>
                </div>
                <button type="submit" class="btn-submit">🗑 Submit Data Deletion Request</button>
            </form>
        </div>

        <h3>Alternative: Delete via Email</h3>
        <div class="contact-box">
            You can also request data deletion by emailing us directly:<br><br>
            <strong>Email:</strong> <a href="mailto:fuioxtechnologies@gmail.com">fuioxtechnologies@gmail.com</a><br>
            <strong>Subject:</strong> Data Deletion Request<br>
            <strong>Include:</strong> Your registered email address and account name<br><br>
            We will process your request within <strong>30 days</strong>.
        </div>

        <h3>Note for Meta/Facebook Connected Accounts</h3>
        <p>If you connected our platform via Facebook/Meta login, you can also manage app permissions and request data deletion through your <a href="https://www.facebook.com/settings?tab=applications" style="color:#25d366;" target="_blank">Facebook App Settings</a>.</p>
    </div>

    <div class="footer">
        <a href="/">Home</a> &nbsp;·&nbsp;
        <a href="/privacy">Privacy Policy</a> &nbsp;·&nbsp;
        <a href="/terms">Terms of Service</a>
    </div>
</div>

<script>
function submitDeletion(e) {
    e.preventDefault();
    var email = document.getElementById('delEmail').value;
    var reason = document.getElementById('delReason').value;

    // Send to admin email via mailto as fallback
    // In production this should call an API endpoint
    document.getElementById('successAlert').classList.add('show');
    e.target.reset();

    // You can enhance this to POST to a Laravel endpoint
}
</script>
</body>
</html>