@extends('layouts.app')

@section('title', 'Chat')
@section('page_title', 'Chat')
@section('page_content_style', 'padding:0;overflow:hidden;height:calc(100vh - 60px);')

@push('styles')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
@endpush

@section('page_styles')
/* ── CHAT LAYOUT ── */
.chat-wrap { display:flex; width:100%; height:calc(100vh - 60px); overflow:hidden; background:#efeae2; }

/* ── CONTACTS PANEL ── */
.contacts-panel {
    width:340px; min-width:280px;
    background:#fff; display:flex; flex-direction:column;
    border-right:1px solid #e9edef; height:100%; overflow:hidden; flex-shrink:0;
}
.contacts-header {
    background:#f0f2f5; padding:10px 16px;
    display:flex; align-items:center; justify-content:space-between;
    flex-shrink:0; min-height:57px;
}
.contacts-header-title { font-size:15px; font-weight:700; color:#111b21; }
.contacts-search { padding:8px 12px; background:#fff; flex-shrink:0; display:flex; gap:8px; align-items:center; }
.contacts-search-input {
    flex:1; padding:8px 12px 8px 36px; border-radius:8px; border:none;
    background:#f0f2f5; font-size:14px; outline:none; color:#111b21;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2354656f'%3E%3Cpath d='M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:10px center; background-size:16px;
}
.contacts-search-input::placeholder { color:#8696a0; }
.btn-new-chat {
    width:34px; height:34px; border-radius:50%;
    background:#25d366; color:#fff; border:none;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; cursor:pointer; font-size:16px;
}
.btn-new-chat:hover { background:#1aaa50; }
#contactsList { flex:1; overflow-y:auto; }
#contactsList::-webkit-scrollbar { width:4px; }
#contactsList::-webkit-scrollbar-thumb { background:#e9edef; border-radius:2px; }

/* Contact item */
.contact-item {
    padding:11px 16px; cursor:pointer;
    display:flex; align-items:center; gap:12px;
    border-bottom:1px solid #f0f2f5; transition:background 0.1s;
}
.contact-item:hover { background:#f5f6f6; }
.contact-item.active { background:#f0f2f5; }
.contact-avatar {
    width:46px; height:46px; border-radius:50%;
    background:#dfe5e7; color:#111b21;
    font-size:16px; font-weight:600;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.contact-info { flex:1; overflow:hidden; }
.contact-top { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:2px; }
.contact-name { font-size:15px; font-weight:500; color:#111b21; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:185px; }
.contact-time { font-size:11px; color:#667781; flex-shrink:0; }
.contact-bottom { display:flex; justify-content:space-between; align-items:center; }
.contact-last { font-size:13px; color:#667781; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px; }
.unread-badge {
    background:#25d366; color:#fff; font-size:11px; font-weight:700;
    min-width:20px; height:20px; border-radius:10px;
    display:flex; align-items:center; justify-content:center; padding:0 5px; flex-shrink:0;
}

/* ── CHAT PANEL ── */
.chat-panel { flex:1; display:flex; flex-direction:column; height:100%; overflow:hidden; min-width:0; }

/* Chat header */
.chat-header {
    background:#f0f2f5; padding:10px 16px;
    display:flex; align-items:center; justify-content:space-between;
    flex-shrink:0; min-height:57px; border-bottom:1px solid #e9edef;
}
.chat-header-left { display:flex; align-items:center; gap:10px; min-width:0; }
.btn-back-contacts { display:none; background:none; border:none; cursor:pointer; color:#54656f; font-size:22px; padding:4px; flex-shrink:0; }
.chat-avatar {
    width:38px; height:38px; border-radius:50%;
    background:#dfe5e7; color:#54656f; font-size:20px;
    display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0;
}
.chat-contact-name { font-size:15px; font-weight:600; color:#111b21; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chat-contact-status { font-size:12px; color:#667781; }
.chat-header-right { display:flex; align-items:center; gap:4px; flex-shrink:0; }
.chat-icon-btn {
    width:38px; height:38px; border:none; background:transparent;
    color:#54656f; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:20px; transition:0.15s; flex-shrink:0;
}
.chat-icon-btn:hover { background:#e9edef; }

/* No chat selected */
.no-chat {
    flex:1; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    color:#667781; gap:12px; background:#f0f2f5;
}
.no-chat-icon { font-size:72px; opacity:0.15; }
.no-chat h3 { font-size:26px; font-weight:300; color:#41525d; }
.no-chat p { font-size:14px; color:#667781; }

/* Messages */
.messages-box {
    flex:1; overflow-y:auto; padding:12px 5% 16px;
    display:flex; flex-direction:column; gap:2px;
    background:#efeae2;
}
.messages-box::-webkit-scrollbar { width:5px; }
.messages-box::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.1); border-radius:3px; }
.date-sep { text-align:center; margin:8px 0; }
.date-sep span { background:#fff; color:#54656f; font-size:11px; padding:4px 12px; border-radius:8px; box-shadow:0 1px 0.5px rgba(0,0,0,0.13); }
.msg-row { display:flex; margin:1px 0; }
.msg-row.incoming { justify-content:flex-start; }
.msg-row.outgoing { justify-content:flex-end; }
.msg-bubble {
    max-width:65%; min-width:80px;
    padding:6px 8px 8px 9px; border-radius:8px;
    position:relative; word-break:break-word;
    box-shadow:0 1px 0.5px rgba(0,0,0,0.13);
}
.msg-row.incoming .msg-bubble { background:#fff; border-top-left-radius:0; }
.msg-row.outgoing .msg-bubble { background:#d9fdd3; border-top-right-radius:0; }
.msg-row.incoming .msg-bubble::before { content:''; position:absolute; top:0; left:-8px; border:8px solid transparent; border-top-color:#fff; border-left:0; }
.msg-row.outgoing .msg-bubble::before { content:''; position:absolute; top:0; right:-8px; border:8px solid transparent; border-top-color:#d9fdd3; border-right:0; }
.msg-text { font-size:14px; line-height:1.5; color:#111b21; padding-right:46px; word-break:break-word; }
.msg-meta { display:flex; justify-content:flex-end; align-items:center; gap:4px; font-size:11px; color:#667781; float:right; clear:both; margin-top:2px; }
.reply-quote { background:rgba(0,0,0,0.05); border-left:4px solid #25d366; border-radius:4px; padding:5px 8px; margin-bottom:5px; font-size:12px; color:#54656f; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.msg-bubble img { max-width:100%; border-radius:6px; display:block; cursor:pointer; }
.msg-bubble video { max-width:100%; border-radius:6px; display:block; }
.msg-bubble audio { width:100%; min-width:200px; height:36px; display:block; }
.msg-actions { position:absolute; top:4px; right:4px; opacity:0; transition:opacity 0.15s; }
.msg-bubble:hover .msg-actions { opacity:1; }
.msg-menu-btn {
    background:rgba(255,255,255,0.85); border:none;
    width:22px; height:22px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.15); font-size:14px;
}
.msg-dropdown { position:absolute; top:26px; right:0; background:#fff; border-radius:8px; min-width:155px; overflow:hidden; z-index:999; display:none; box-shadow:0 4px 16px rgba(0,0,0,0.18); }
.msg-dropdown.open { display:block; }
.msg-dropdown button { width:100%; border:none; background:none; color:#111b21; padding:11px 14px; text-align:left; cursor:pointer; font-size:13px; font-family:inherit; }
.msg-dropdown button:hover { background:#f5f6f6; }
.msg-dropdown button.danger { color:#f15c6d; }

/* Bars */
.lock-bar { display:none; background:#fff3e0; border-top:1px solid #ffe0b2; padding:10px 16px; align-items:center; justify-content:space-between; gap:10px; flex-shrink:0; }
.reply-bar { display:none; background:#fff; border-left:4px solid #00a884; padding:8px 16px; align-items:center; justify-content:space-between; flex-shrink:0; }
.reply-label { font-size:11px; color:#00a884; font-weight:700; margin-bottom:2px; }
.reply-preview { font-size:13px; color:#667781; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:300px; }
.file-bar { display:none; background:#f0f2f5; border-top:1px solid #e9edef; padding:8px 16px; align-items:center; gap:10px; flex-shrink:0; }

/* Input */
.input-area { background:#f0f2f5; padding:8px 12px; display:flex; align-items:center; gap:6px; flex-shrink:0; min-height:60px; }
.input-icon-btn { width:40px; height:40px; border:none; background:transparent; color:#54656f; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:20px; flex-shrink:0; transition:0.15s; }
.input-icon-btn:hover { background:rgba(0,0,0,0.06); }
#chatMsgInput { flex:1; padding:10px 14px; border-radius:8px; border:none; background:#fff; font-size:14px; color:#111b21; outline:none; font-family:inherit; min-width:0; }
#chatMsgInput::placeholder { color:#8696a0; }
#chatMsgInput:disabled { opacity:0.5; cursor:not-allowed; }
.send-circle { width:42px; height:42px; border:none; border-radius:50%; background:#00a884; color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:0.15s; font-size:20px; }
.send-circle:hover { background:#06cf9c; }
.send-circle:disabled { opacity:0.5; cursor:not-allowed; }
.send-circle.recording { background:#f15c6d; }

/* Emoji */
.emoji-wrap { position:relative; }
.emoji-panel { display:none; position:absolute; bottom:56px; left:-10px; background:#fff; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,0.18); width:300px; z-index:9999; overflow:hidden; }
.emoji-panel.open { display:block; }
.emoji-tabs { display:flex; border-bottom:1px solid #f0f2f5; padding:4px 6px; gap:2px; }
.emoji-tab { background:none; border:none; cursor:pointer; font-size:17px; padding:5px 7px; border-radius:6px; }
.emoji-tab.active,.emoji-tab:hover { background:#f0f2f5; }
.emoji-search-wrap { padding:6px 10px; border-bottom:1px solid #f0f2f5; }
.emoji-search-wrap input { width:100%; padding:5px 10px; border-radius:16px; border:none; background:#f0f2f5; font-size:13px; outline:none; }
.emoji-grid { display:grid; grid-template-columns:repeat(8,1fr); gap:2px; padding:6px; max-height:180px; overflow-y:auto; }
.emoji-grid span { font-size:20px; cursor:pointer; padding:3px; border-radius:5px; text-align:center; display:block; }
.emoji-grid span:hover { background:#f0f2f5; }

/* Top menu */
.chat-top-menu-wrap { position:relative; }
.chat-top-menu { display:none; position:absolute; top:44px; right:0; background:#fff; border-radius:8px; min-width:200px; z-index:9999; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.15); }
.chat-top-menu.open { display:block; }
.chat-top-menu button { width:100%; border:none; background:none; color:#111b21; padding:13px 18px; text-align:left; cursor:pointer; font-size:13px; font-family:inherit; }
.chat-top-menu button:hover { background:#f5f6f6; }
.chat-top-menu hr { border:none; border-top:1px solid #e9edef; margin:3px 0; }
.ai-toggle-row { padding:10px 18px; display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#111b21; }
.ai-switch { position:relative; width:38px; height:21px; display:inline-block; }
.ai-switch input { display:none; }
.ai-slider { position:absolute; inset:0; background:#ccc; border-radius:21px; cursor:pointer; transition:0.3s; }
.ai-slider::before { content:''; position:absolute; width:15px; height:15px; left:3px; top:3px; background:#fff; border-radius:50%; transition:0.3s; }
input:checked + .ai-slider { background:#25d366; }
input:checked + .ai-slider::before { transform:translateX(17px); }

/* Assign drop */
.assign-wrap { position:relative; }
.assign-drop { display:none; position:absolute; top:44px; right:0; background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.15); min-width:185px; z-index:999; overflow:hidden; }

/* Profile panel */
#chatProfileOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:9997; }
#chatProfileOverlay.show { display:block; }
#chatProfilePanel { position:fixed; top:0; right:-400px; width:370px; height:100vh; background:#fff; box-shadow:-4px 0 20px rgba(0,0,0,0.15); z-index:9998; transition:right 0.3s; overflow-y:auto; }
#chatProfilePanel.open { right:0; }
.profile-panel-header { background:linear-gradient(135deg,#00a884,#06cf9c); padding:22px 20px; color:#fff; }
.profile-panel-avatar { width:66px; height:66px; border-radius:50%; background:rgba(255,255,255,0.25); display:flex; align-items:center; justify-content:center; font-size:34px; margin-bottom:10px; }
.profile-panel-name { font-size:18px; font-weight:700; }
.profile-panel-phone { font-size:13px; opacity:0.8; margin-top:3px; }
.profile-panel-body { padding:18px; }
.profile-section-label { font-size:10px; font-weight:700; color:#8696a0; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; }
.profile-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f0f2f5; font-size:13px; color:#111b21; }
.profile-row span:first-child { color:#667781; }
.save-contact-form { background:#f0fdf4; border-radius:10px; padding:12px; margin-bottom:14px; }
.save-contact-form h6 { font-size:13px; font-weight:600; color:#00a884; margin-bottom:8px; }
.save-contact-form input { width:100%; padding:9px 12px; border:1.5px solid #b7ebc0; border-radius:8px; font-size:13px; outline:none; margin-bottom:8px; }
.save-contact-form button { background:#00a884; color:#fff; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; }
.profile-media-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:5px; }

/* React picker */
#reactPicker { display:none; position:fixed; background:#fff; border-radius:40px; padding:7px 10px; box-shadow:0 4px 20px rgba(0,0,0,0.18); z-index:9999; gap:5px; align-items:center; }
#reactPicker.show { display:flex; }
#reactPicker span { cursor:pointer; font-size:21px; transition:transform 0.15s; }
#reactPicker span:hover { transform:scale(1.3); }

/* Media preview */
#mediaPreviewModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.95); z-index:99999; align-items:center; justify-content:center; flex-direction:column; gap:1rem; }
#mediaPreviewModal.show { display:flex; }

/* Forward modal */
.fwd-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.fwd-modal-overlay.show { display:flex; }
.fwd-modal-panel { background:#fff; width:min(380px,95%); border-radius:12px; overflow:hidden; box-shadow:0 16px 40px rgba(0,0,0,0.2); }

/* Template modal */
.tpl-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; }
.tpl-modal-overlay.show { display:flex; }
.tpl-modal-box { background:#fff; border-radius:14px; padding:22px; width:90%; max-width:520px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
.tpl-item { border:1px solid #e9edef; border-radius:10px; padding:14px; margin-bottom:10px; }
.tpl-name { font-weight:700; font-size:14px; margin-bottom:4px; color:#111b21; }
.tpl-preview { font-size:13px; color:#667781; margin-bottom:8px; white-space:pre-wrap; }
.tpl-use-btn { background:#e9fbe5; color:#00a884; border:none; padding:7px 14px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; }
.tpl-use-btn:hover { background:#d1f5ca; }
.tpl-field label { display:block; font-size:12px; color:#54656f; margin:10px 0 4px; font-weight:600; }
.tpl-field input { width:100%; padding:9px 12px; border:1.5px solid #e9edef; border-radius:8px; font-size:14px; outline:none; }
.tpl-send-btn { background:#00a884; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:600; margin-top:12px; }
.tpl-back-btn { background:#f0f2f5; color:#111b21; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:14px; margin-top:12px; margin-right:8px; }

/* Toast */
.chat-toast { position:fixed; bottom:72px; left:50%; transform:translateX(-50%); background:#111b21; color:#fff; padding:8px 20px; border-radius:20px; font-size:13px; z-index:99999; pointer-events:none; opacity:0; transition:opacity 0.2s; white-space:nowrap; }
.chat-toast.show { opacity:1; }

/* Mobile */
#chatMobileOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:999; }
#chatMobileOverlay.show { display:block; }

@media(max-width:768px){
    .contacts-panel { position:fixed; top:60px; left:0; height:calc(100% - 60px); z-index:1000; width:100%; max-width:100%; transform:translateX(0); transition:transform 0.3s; }
    .contacts-panel.hidden { transform:translateX(-100%); }
    .chat-panel { width:100%; }
    .btn-back-contacts { display:flex !important; }
    .msg-bubble { max-width:85%; }
    .emoji-panel { width:270px; }
    #chatProfilePanel { width:100%; right:-100%; }
}
@media(max-width:480px){
    .messages-box { padding:10px 3% 14px; }
    .msg-bubble { max-width:92%; }
    .emoji-panel { width:250px; left:-50px; }
}
@endsection

@section('content')

<div class="chat-wrap" id="chatWrap">

    <!-- Mobile overlay -->
    <div id="chatMobileOverlay" onclick="chatBack()"></div>

    <!-- ═══ CONTACTS PANEL ═══ -->
    <div class="contacts-panel" id="contactsPanel">
        <div class="contacts-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div class="contacts-header-title">{{ $user->organisation }}</div>
            <div class="ai-toggle-row" style="margin:0;" title="Turn AI replies on/off for ALL conversations at once">
                <span style="font-size:11px;color:#888;"><i class="bi bi-robot me-1"></i>AI</span>
                <label class="ai-switch">
                    <input type="checkbox" id="globalAiToggle" onchange="chatToggleGlobalAI(this)">
                    <span class="ai-slider"></span>
                </label>
            </div>
        </div>
        <div class="contacts-search">
            <input type="text" class="contacts-search-input" id="chatSearchInput"
                placeholder="Search or start new chat" oninput="chatFilterUsers()" autocomplete="off">
            <button class="btn-new-chat" onclick="chatNewContact()" title="New Chat">
                <i class="bi bi-pencil-fill" style="font-size:14px;"></i>
            </button>
        </div>
        <div id="contactsList"></div>
    </div>

    <!-- ═══ CHAT PANEL ═══ -->
    <div class="chat-panel" id="chatPanel">

        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <button class="btn-back-contacts" id="btnBackContacts" onclick="chatBack()">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <div class="chat-avatar" id="chatHdrAvatar" onclick="chatOpenProfile(chatCurrentPhone)"><i class="bi bi-person-fill"></i></div>
                <div style="min-width:0;">
                    <div class="chat-contact-name" id="chatHdrName">Select a chat</div>
                    <div class="chat-contact-status" id="chatHdrStatus"></div>
                </div>
            </div>
            <div class="chat-header-right">
                <div id="chatAssignedBadge" class="badge rounded-pill d-none" style="background:#e8f5e9;color:#2e7d32;font-size:11px;font-weight:600;"></div>

                @if(($user->team_role ?? 'owner') !== 'agent')
                <div class="assign-wrap" id="chatAssignWrap">
                    <button class="chat-icon-btn" onclick="chatToggleAssign()" title="Assign agent">
                        <i class="bi bi-person-check-fill"></i>
                    </button>
                    <div class="assign-drop" id="chatAssignDrop">
                        <div class="px-3 py-2 border-bottom" style="font-size:11px;font-weight:700;color:#888;text-transform:uppercase;">Assign to</div>
                        <div id="chatAgentList"></div>
                        <div class="border-top px-3 py-2">
                            <button onclick="chatAssign(null)" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">✕ Unassign</button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="chat-top-menu-wrap" id="chatTopMenuWrap">
                    <button class="chat-icon-btn" onclick="chatToggleTopMenu()">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="chat-top-menu" id="chatTopMenu">
                        <button onclick="chatOpenProfile(chatCurrentPhone);chatCloseTopMenu()"><i class="bi bi-person me-2"></i>View Profile</button>
                        <button onclick="chatOpenFwd('');chatCloseTopMenu()"><i class="bi bi-forward me-2"></i>Forward Message</button>
                        <hr>
                        <div class="ai-toggle-row">
                            <span><i class="bi bi-robot me-2"></i>AI Reply (this chat)</span>
                            <label class="ai-switch">
                                <input type="checkbox" id="aiToggle" onchange="chatToggleAI(this)">
                                <span class="ai-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No chat selected -->
        <div class="no-chat" id="chatNoSelected">
            <div class="no-chat-icon">💬</div>
            <h3>Fuiox Chat</h3>
            <p>Select a contact to start chatting</p>
        </div>

        <!-- Messages -->
        <div class="messages-box" id="chatMessages" style="display:none;"></div>

        <!-- Lock bar -->
        <div class="lock-bar" id="chatLockBar">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-lock-fill text-warning"></i>
                <span style="font-size:13px;color:#e65100;font-weight:500;" id="chatLockMsg"></span>
            </div>
            <button class="btn btn-sm btn-fu-primary rounded-pill" onclick="chatOpenTpl()">
                <i class="bi bi-file-text me-1"></i>Send Template
            </button>
        </div>

        <!-- Reply bar -->
        <div class="reply-bar" id="chatReplyBar">
            <div style="min-width:0;">
                <div class="reply-label"><i class="bi bi-reply-fill me-1"></i>Replying to:</div>
                <div class="reply-preview" id="chatReplyText"></div>
            </div>
            <button onclick="chatCancelReply()" style="background:none;border:none;color:#8696a0;cursor:pointer;font-size:20px;">✕</button>
        </div>

        <!-- File preview bar -->
        <div class="file-bar" id="chatFileBar">
            <span id="chatFilePreview" style="flex:1;font-size:13px;color:#111b21;"></span>
            <button onclick="chatClearFile()" style="background:none;border:none;color:#f15c6d;cursor:pointer;font-size:20px;">✕</button>
        </div>

        <!-- Input -->
        <div class="input-area">
            <input type="file" id="chatFileInput" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.txt,.xlsx,.xls" style="display:none;" onchange="chatHandleFile()">
            <button class="input-icon-btn" onclick="document.getElementById('chatFileInput').click()" title="Attach">
                <i class="bi bi-paperclip"></i>
            </button>
            <div class="emoji-wrap">
                <button class="input-icon-btn" id="chatEmojiBtn" onclick="chatToggleEmoji()">
                    <i class="bi bi-emoji-smile"></i>
                </button>
                <div class="emoji-panel" id="chatEmojiPanel">
                    <div class="emoji-tabs">
                        <button class="emoji-tab active" onclick="chatShowEmoji('smileys')">😊</button>
                        <button class="emoji-tab" onclick="chatShowEmoji('gestures')">👍</button>
                        <button class="emoji-tab" onclick="chatShowEmoji('hearts')">❤️</button>
                        <button class="emoji-tab" onclick="chatShowEmoji('animals')">🐶</button>
                        <button class="emoji-tab" onclick="chatShowEmoji('food')">🍕</button>
                        <button class="emoji-tab" onclick="chatShowEmoji('symbols')">🔥</button>
                    </div>
                    <div class="emoji-search-wrap">
                        <input type="text" id="emojiSearch" name="emoji_search" placeholder="Search emoji…" oninput="chatSearchEmoji(this.value)" autocomplete="off">
                    </div>
                    <div class="emoji-grid" id="chatEmojiGrid"></div>
                </div>
            </div>
            <input type="text" id="chatMsgInput" placeholder="Type a message" disabled
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatSend();}">
            <button class="send-circle" id="chatVoiceBtn" disabled
                onmousedown="chatStartRec()" onmouseup="chatStopRec()"
                ontouchstart="event.preventDefault();chatStartRec();" ontouchend="event.preventDefault();chatStopRec();">
                <i class="bi bi-mic-fill"></i>
            </button>
            <button class="send-circle" style="background:#075e54;" id="chatTplBtn" onclick="chatOpenTpl()" disabled title="Send Template">
                <i class="bi bi-file-text-fill"></i>
            </button>
            <button class="send-circle" id="chatSendBtn" onclick="chatSend()" disabled>
                <i class="bi bi-send-fill" style="font-size:17px;"></i>
            </button>
        </div>
    </div>
</div>

<!-- ── Template Preview Modal ── -->
<div id="chatTplPreviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div class="p-4 bg-white rounded-4 position-relative" style="max-width:340px;width:90%;">
        <button onclick="document.getElementById('chatTplPreviewModal').style.display='none'" class="btn-close position-absolute top-0 end-0 m-3"></button>
        <div class="text-success fw-bold mb-2" style="font-size:11px;">📝 TEMPLATE PREVIEW</div>
        <div id="chatTplPreviewName" class="fw-bold mb-3" style="font-size:15px;color:#111b21;"></div>
        <div class="p-3 rounded-3" style="background:#e9fbe5;">
            <div id="chatTplPreviewBody" style="font-size:13px;color:#111b21;line-height:1.6;white-space:pre-wrap;word-break:break-word;"></div>
            <div class="text-end mt-2" style="font-size:10px;color:#667781;">✓✓</div>
        </div>
        <button onclick="document.getElementById('chatTplPreviewModal').style.display='none'" class="btn btn-fu-primary w-100 mt-3 rounded-3">Close</button>
    </div>
</div>

<!-- ── Template Modal ── -->
<div class="tpl-modal-overlay" id="chatTplModal">
    <div class="tpl-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong style="font-size:15px;"><i class="bi bi-file-text me-2 text-success"></i>Send Template</strong>
            <button onclick="chatCloseTpl()" class="btn-close"></button>
        </div>
        <input type="search" id="chatTplSearch" class="form-control mb-3" placeholder="Search templates…" oninput="chatFilterTpl()" style="border-radius:8px;font-size:14px;">
        <div id="chatTplList" class="tpl-list">
            <div class="text-center text-muted py-3">Loading…</div>
        </div>
    </div>
</div>

<!-- ── Forward Modal ── -->
<div class="fwd-modal-overlay" id="chatFwdModal">
    <div class="fwd-modal-panel">
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
            <span class="fw-bold">Forward Message</span>
            <button onclick="chatCloseFwd()" class="btn-close"></button>
        </div>
        <div id="chatFwdList" style="max-height:380px;overflow-y:auto;"></div>
    </div>
</div>

<!-- ── Profile Panel ── -->
<div id="chatProfileOverlay" onclick="chatCloseProfile()"></div>
<div id="chatProfilePanel">
    <div class="profile-panel-header">
        <button onclick="chatCloseProfile()" class="btn btn-sm btn-link text-white text-decoration-none p-0 mb-3">
            <i class="bi bi-arrow-left me-1"></i>Back
        </button>
        <div class="profile-panel-avatar" id="chatProfAvatar"><i class="bi bi-person-fill" style="font-size:34px;color:#fff;"></i></div>
        <div class="profile-panel-name" id="chatProfName">—</div>
        <div class="profile-panel-phone" id="chatProfPhone"></div>
    </div>
    <div class="profile-panel-body">
        <div id="chatSaveContactForm" class="save-contact-form" style="display:none;">
            <h6>Save this contact</h6>
            <input type="text" id="chatSaveContactName" placeholder="Enter name…">
            <button onclick="chatDoSave()">Save Contact</button>
        </div>
        <div class="profile-section-label">Details</div>
        <div class="profile-row"><span>Messages</span><span id="chatProfMsgCount">—</span></div>
        <div class="profile-row"><span>Chat since</span><span id="chatProfSince">—</span></div>
        <div class="profile-row"><span>Last active</span><span id="chatProfLast">—</span></div>
        <div class="profile-section-label mt-4">Shared Media</div>
        <div class="profile-media-grid" id="chatProfMedia"></div>
    </div>
</div>

<!-- ── React Picker ── -->
<div id="reactPicker">
    <span onclick="chatReact('👍')">👍</span>
    <span onclick="chatReact('❤️')">❤️</span>
    <span onclick="chatReact('😂')">😂</span>
    <span onclick="chatReact('😮')">😮</span>
    <span onclick="chatReact('😢')">😢</span>
    <span onclick="chatReact('🙏')">🙏</span>
    <span onclick="chatReact('🔥')">🔥</span>
</div>

<!-- ── Media Preview ── -->
<div id="mediaPreviewModal" onclick="if(event.target===this)chatCloseMedia()">
    <div id="mediaPreviewContent"></div>
    <div class="d-flex gap-3 mt-3">
        <a id="mediaDownloadBtn" download class="btn btn-success rounded-pill px-4">⬇ Download</a>
        <button onclick="chatCloseMedia()" class="btn btn-danger rounded-pill px-4">✕ Close</button>
    </div>
</div>

<div class="chat-toast" id="chatToast"></div>

@endsection

@push('scripts')
<script>
/* ── STATE ── */
let chatCurrentPhone='', chatCurrentName='', chatLastUpdate='', chatAllUsers=[];
let chatMetaTpls=[], chatSelTpl=null, chatReplyData=null;
let chatReactMsgId=null, chatProfilePhone='', chatFwdMsg='';
let chatRecorder=null, chatAudioChunks=[], chatIsRec=false;
let chatTeamMembers=[], chatCurEmojiCat='smileys';
let chatIsMobile = window.innerWidth <= 768;

/* ── HELPERS ── */
function chatEsc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function chatFmtSz(b){ if(!b)return''; const k=1024,s=['B','KB','MB','GB']; const i=Math.floor(Math.log(b)/Math.log(k)); return parseFloat((b/Math.pow(k,i)).toFixed(1))+' '+s[i]; }
function chatToast(msg){ const t=document.getElementById('chatToast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2000); }
function showToast(m){ chatToast(m); }
function showNotif(m){ chatToast(m); }
function escHtml(s){ return chatEsc(s); }

/* ── MOBILE ── */
window.addEventListener('resize',()=>{ chatIsMobile=window.innerWidth<=768; if(!chatIsMobile) document.getElementById('contactsPanel').classList.remove('hidden'); });
function chatBack(){
    document.getElementById('contactsPanel').classList.remove('hidden');
    document.getElementById('chatMobileOverlay').classList.remove('show');
}
function chatHideContacts(){
    if(!chatIsMobile) return;
    document.getElementById('contactsPanel').classList.add('hidden');
    document.getElementById('chatMobileOverlay').classList.add('show');
}

/* ── LOAD USERS ── */
function chatLoadUsers(){
    fetch('/chat/users',{credentials:'include'}).then(r=>r.json()).then(data=>{ chatAllUsers=data; chatRenderUsers(data); }).catch(()=>{});
}
function chatRenderUsers(data){
    const box = document.getElementById('contactsList');
    if(!data.length){ box.innerHTML='<div class="text-center text-muted py-4" style="font-size:13px;">No contacts yet</div>'; return; }
    box.innerHTML = data.map(u=>{
        const name = u.name||u.display_phone||u.phone;
        const active = u.phone===chatCurrentPhone ? 'active' : '';
        const safe = String(name).replace(/'/g,"\\'");
        const badge = u.unread>0 ? `<span class="unread-badge">${u.unread}</span>` : '';
        const asgn = u.assigned_to ? encodeURIComponent(JSON.stringify(u.assigned_to)) : '';
        return `<div class="contact-item ${active}" onclick="chatOpenChat('${u.phone}','${safe}','${asgn}')">
            <div class="contact-avatar"><i class="bi bi-person-fill"></i></div>
            <div class="contact-info">
                <div class="contact-top">
                    <div class="contact-name">${chatEsc(name)}${u.assigned_to?`<span class="ms-1" style="color:#a855f7;font-size:10px;">👤${chatEsc(u.assigned_to.name)}</span>`:''}</div>
                    <div class="contact-time">${u.last_time||''}</div>
                </div>
                <div class="contact-bottom">
                    <div class="contact-last">${chatEsc(u.last_message||'')}</div>
                    ${badge}
                </div>
            </div>
        </div>`;
    }).join('');
}
function chatFilterUsers(){ const q=document.getElementById('chatSearchInput').value.toLowerCase(); chatRenderUsers(chatAllUsers.filter(u=>(u.phone||'').toLowerCase().includes(q)||(u.name||'').toLowerCase().includes(q))); }

/* ── OPEN CHAT ── */
function chatOpenChat(phone,name,asgnStr){
    chatCurrentPhone=phone; chatCurrentName=name||phone;
    chatRefreshAiToggleState();
    document.getElementById('chatHdrAvatar').innerHTML='<i class="bi bi-person-fill"></i>';
    document.getElementById('chatHdrName').textContent=chatCurrentName;
    document.getElementById('chatHdrStatus').textContent='online';
    ['chatMsgInput','chatSendBtn','chatVoiceBtn','chatTplBtn'].forEach(id=>document.getElementById(id).disabled=false);
    document.getElementById('chatNoSelected').style.display='none';
    document.getElementById('chatMessages').style.display='flex';
    document.getElementById('chatMessages').style.flexDirection='column';
    let asgn=null; try{ if(asgnStr) asgn=JSON.parse(decodeURIComponent(asgnStr)); }catch(e){}
    const badge=document.getElementById('chatAssignedBadge');
    if(badge){ if(asgn?.name){ badge.textContent='👤 '+asgn.name; badge.classList.remove('d-none'); } else badge.classList.add('d-none'); }
    chatHideContacts();
    chatRenderUsers(chatAllUsers);
    chatLoadMessages();
}

/* ── MESSAGES ── */
function chatLoadMessages(){
    if(!chatCurrentPhone) return;
    fetch('/chat/window/'+chatCurrentPhone).then(r=>r.json()).then(w=>{
        const bar=document.getElementById('chatLockBar');
        if(w.locked){ bar.style.display='flex'; document.getElementById('chatLockMsg').textContent=w.message; }
        else bar.style.display='none';
    }).catch(()=>{});
    const box=document.getElementById('chatMessages');
    box.innerHTML='<div class="text-center text-muted py-4 m-auto">⏳ Loading…</div>';
    fetch('/chat/messages/'+chatCurrentPhone).then(r=>r.json()).then(data=>{
        if(!data.length){ box.innerHTML='<div class="text-center text-muted py-4 m-auto">👋 No messages yet</div>'; return; }
        let html=''; let lastDate='';
        data.forEach(m=>{
            const rd=m.date||'';
            if(rd&&rd!==lastDate){
                const today=new Date(); const yday=new Date(); yday.setDate(yday.getDate()-1);
                const mo=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const fd=d=>d.getDate()+' '+mo[d.getMonth()]+' '+d.getFullYear();
                const lbl=rd===fd(today)?'Today':rd===fd(yday)?'Yesterday':rd;
                html+=`<div class="date-sep"><span>${lbl}</span></div>`; lastDate=rd;
            }
            const content=chatBuildContent(m);
            const safe=(m.message||'').replace(/'/g,"\\'").replace(/`/g,'\\`').substring(0,80);
            const wid=m.whatsapp_message_id||m.meta_message_id||'';
            const react=m.reaction?`<span style="font-size:13px;">${m.reaction}</span>`:'';
            html+=`<div class="msg-row ${m.type}" id="msgrow_${m.id}">
  <div class="msg-bubble">
    <div class="msg-actions">
      <button class="msg-menu-btn" onclick="chatToggleMsgMenu(event,'mdrop_${m.id}')"><i class="bi bi-chevron-down" style="font-size:11px;"></i></button>
      <div class="msg-dropdown" id="mdrop_${m.id}">
        <button onclick="chatSetReply('${safe}','${wid}',${m.id})"><i class="bi bi-reply me-2"></i>Reply</button>
        <button onclick="chatCopy('${safe}')"><i class="bi bi-clipboard me-2"></i>Copy</button>
        <button onclick="chatOpenFwd('${safe}')"><i class="bi bi-forward me-2"></i>Forward</button>
        <button onclick="chatOpenTpl()"><i class="bi bi-file-text me-2"></i>Template</button>
        <button onclick="chatShowReact(event,${m.id})"><i class="bi bi-emoji-smile me-2"></i>React</button>
        <button class="danger" onclick="chatDelete(${m.id})"><i class="bi bi-trash me-2"></i>Delete</button>
      </div>
    </div>
    ${m.reply_to?`<div class="reply-quote">${chatEsc(m.reply_to)}</div>`:''}
    ${content}
    <div class="msg-meta">${react}<span>${m.created_at||''}</span></div>
  </div>
</div>`;
        });
        box.innerHTML=html; box.scrollTop=box.scrollHeight;
    }).catch(()=>{});
}

function chatGetExt(mimeOrType){
    if(!mimeOrType) return 'ogg';
    const t = mimeOrType.toLowerCase().split(';')[0].trim();
    if(t.includes('ogg'))  return 'ogg';
    if(t.includes('webm')) return 'webm';
    if(t.includes('mp4')||t.includes('mpeg')) return 'mp4';
    if(t.includes('jpeg')||t.includes('jpg')) return 'jpg';
    if(t.includes('png'))  return 'png';
    if(t.includes('gif'))  return 'gif';
    if(t.includes('pdf'))  return 'pdf';
    if(t==='audio') return 'ogg';
    if(t==='image') return 'jpg';
    if(t==='video') return 'mp4';
    return 'ogg';
}
function chatBuildContent(m){
    if(!m.media_type&&m.message&&m.message.startsWith('Template: ')){
        const n=m.message.replace('Template: ','').trim(); const tpl=(chatMetaTpls||[]).find(t=>t.name===n); const prev=tpl?tpl.preview:'';
        return`<div onclick="chatShowTplPreview('${chatEsc(n)}','${chatEsc(prev)}')" style="cursor:pointer;background:rgba(0,0,0,0.04);border-radius:8px;padding:9px 11px;min-width:160px;max-width:260px;border-left:3px solid #25d366;">
            <div style="font-size:10px;color:#25d366;font-weight:700;margin-bottom:3px;">📝 TEMPLATE</div>
            <div style="font-size:13px;font-weight:600;color:#111b21;">${chatEsc(n)}</div>
            ${prev?`<div style="font-size:11px;color:#667781;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">${chatEsc(prev.substring(0,55))}${prev.length>55?'…':''}</div>`:''}
            <div style="font-size:10px;color:#25d366;margin-top:5px;">Tap to preview →</div>
        </div>`;
    }
    if(!m.media_type||!m.media_id) return `<div class="msg-text">${chatEsc(m.message||'')}</div>`;
    const ext = chatGetExt(m.media_mime_type || m.media_type);
    const url = `/storage/media/${m.media_id}.${ext}`;
    const fallbackUrl = `/chat/media/${m.media_id}`;
    switch(m.media_type){
        case 'image': return `<img src="${url}" style="max-width:260px;max-height:260px;border-radius:6px;cursor:pointer;display:block;" onclick="chatOpenMedia('${url}','image')">${m.media_caption?`<div class="msg-text" style="padding-right:0;">${chatEsc(m.media_caption)}</div>`:''}`;
        case 'audio': return `<div style="min-width:220px;"><audio controls preload="auto" style="width:100%;height:40px;border-radius:8px;"><source src="${url}" type="audio/ogg"><source src="${url.replace(/\.\w+$/, '.webm')}" type="audio/webm"><source src="${fallbackUrl}" type="audio/ogg"></audio></div>`;
        case 'video': return `<video controls preload="metadata" style="max-width:260px;max-height:180px;border-radius:6px;display:block;cursor:pointer;" onclick="if(this.paused){this.play();}else{chatOpenMedia('${url}','video');}"><source src="${url}" type="video/mp4"><source src="${fallbackUrl}" type="video/mp4">Your browser cannot play this video. <a href="${fallbackUrl}" target="_blank">Download instead</a></video>${m.media_caption?`<div class="msg-text" style="padding-right:0;">${chatEsc(m.media_caption)}</div>`:''}`;
        case 'document': return `<a href="${url}" target="_blank" download style="display:inline-flex;align-items:center;gap:8px;padding:10px;background:rgba(0,0,0,0.05);border-radius:8px;text-decoration:none;color:#111b21;max-width:240px;"><span style="font-size:24px;">📄</span><div><div style="font-weight:600;font-size:13px;word-break:break-word;">${chatEsc(m.media_filename||'Document')}</div>${m.media_size?`<div style="font-size:11px;color:#667781;">${chatFmtSz(m.media_size)}</div>`:''}</div></a>`;
        default: return `<a href="${url}" target="_blank" style="color:#00a884;"><i class="bi bi-paperclip me-1"></i>Download</a>`;
    }
}

/* ── SEND ── */
function chatSend(){
    const inp=document.getElementById('chatMsgInput'); const fi=document.getElementById('chatFileInput');
    const msg=inp.value.trim(); const file=fi.files[0];
    if(!msg&&!file) return; if(!chatCurrentPhone) return;
    const fd=new FormData(); fd.append('phone',chatCurrentPhone);
    if(msg) fd.append('message',msg);
    if(chatReplyData){ fd.append('reply_to',chatReplyData.text); fd.append('reply_to_id',chatReplyData.waMsgId||chatReplyData.dbId); chatCancelReply(); }
    if(file){ fd.append('media',file); let mt='document'; if(file.type.startsWith('image/'))mt='image'; else if(file.type.startsWith('audio/'))mt='audio'; else if(file.type.startsWith('video/'))mt='video'; fd.append('media_type',mt); if(msg) fd.append('caption',msg); }
    inp.value=''; chatClearFile();
    fetch('/chat/send',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:fd})
    .then(r=>r.json()).then(d=>{ if(d.error) chatToast('❌ '+d.error); else chatLoadMessages(); }).catch(()=>chatToast('❌ Failed'));
}

/* ── FILE ── */
function chatHandleFile(){
    const file=document.getElementById('chatFileInput').files[0]; if(!file) return;
    const icon=file.type.startsWith('image/')?`<img src="${URL.createObjectURL(file)}" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">`:
        `<div style="width:44px;height:44px;background:#e9edef;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:22px;">${file.type.startsWith('audio/')?'🎵':file.type.startsWith('video/')?'🎥':'📄'}</div>`;
    document.getElementById('chatFilePreview').innerHTML=`<div class="d-flex align-items-center gap-2">${icon}<div><div style="font-size:13px;font-weight:600;">${chatEsc(file.name.substring(0,24))}</div><div style="font-size:12px;color:#667781;">${chatFmtSz(file.size)}</div></div></div>`;
    document.getElementById('chatFileBar').style.display='flex';
    document.getElementById('chatMsgInput').placeholder='Add a caption…';
    document.getElementById('chatMsgInput').focus();
}
function chatClearFile(){ document.getElementById('chatFileInput').value=''; document.getElementById('chatFileBar').style.display='none'; document.getElementById('chatMsgInput').placeholder='Type a message'; }

/* ── VOICE ── */
let chatRecStream = null;
async function chatStartRec(){
    if(!chatCurrentPhone){ chatToast('Select a chat first'); return; }
    if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){ chatToast('Microphone not supported in this browser'); return; }
    try{
        chatRecStream = await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true},video:false});
        chatAudioChunks=[];
        // Pick best supported mime type
        const mimeTypes = ['audio/webm;codecs=opus','audio/webm','audio/ogg;codecs=opus','audio/ogg','audio/mp4'];
        let mime = '';
        for(const m of mimeTypes){ if(MediaRecorder.isTypeSupported(m)){mime=m;break;} }
        const opts = mime ? {mimeType:mime} : {};
        chatRecorder = new MediaRecorder(chatRecStream, opts);
        chatRecorder.ondataavailable = e=>{ if(e.data&&e.data.size>0) chatAudioChunks.push(e.data); };
        chatRecorder.onstop = async()=>{
            const ext = mime.includes('ogg') ? 'ogg' : mime.includes('mp4') ? 'mp4' : 'webm';
            const type = mime || 'audio/webm';
            const blob = new Blob(chatAudioChunks, {type});
            if(chatRecStream){ chatRecStream.getTracks().forEach(t=>t.stop()); chatRecStream=null; }
            await chatSendVoice(blob, ext, type);
        };
        chatRecorder.start(100); // collect data every 100ms
        chatIsRec=true;
        document.getElementById('chatVoiceBtn').innerHTML='<i class="bi bi-stop-fill"></i>';
        document.getElementById('chatVoiceBtn').classList.add('recording');
        document.getElementById('chatMsgInput').placeholder='🔴 Recording… release to send';
    }catch(err){
        console.error('Mic error:',err);
        chatToast(err.name==='NotAllowedError'?'Microphone permission denied':'Could not access microphone');
    }
}
function chatStopRec(){
    if(!chatIsRec||!chatRecorder) return;
    chatRecorder.stop();
    chatIsRec=false;
    document.getElementById('chatVoiceBtn').innerHTML='<i class="bi bi-mic-fill"></i>';
    document.getElementById('chatVoiceBtn').classList.remove('recording');
    document.getElementById('chatMsgInput').placeholder='Type a message';
}
async function chatSendVoice(blob, ext='webm', type='audio/webm'){
    const fd=new FormData();
    fd.append('phone', chatCurrentPhone);
    fd.append('media', blob, 'voice.'+ext);
    fd.append('media_type', 'audio');
    fd.append('message', '');
    fd.append('force_audio', '1');
    try{
        const r=await fetch('/chat/send',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'},body:fd});
        const d=await r.json();
        if(d.error) chatToast('❌ '+d.error);
        else chatLoadMessages();
    }catch{ chatToast('Voice send failed'); }
}

/* ── EMOJI ── */
const chatEmojis={smileys:['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','😊','😇','🥰','😍','🤩','😘','😋','😛','😜','🤪','😝','🤑','🤗','🤔','🤐','😐','😑','😶','😏','😒','🙄','😬','😴','😷','🤒','🤢','🤧','🥵','🥶','😵','🤯','🤠','🥳','😎','😕','😟','😮','😲','😳','🥺','😦','😨','😢','😭','😱','😤','😡','😠','🤬','😈'],gestures:['👍','👎','👌','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','👋','🤚','✋','👏','🙌','🤝','🙏','💪'],hearts:['❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','❣️','💕','💞','💓','💗','💖','💘','💝'],animals:['🐶','🐱','🐭','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🐔','🐧','🦆','🦅','🦉','🦇','🐺','🐴','🦄'],food:['🍎','🍊','🍋','🍇','🍓','🍒','🥭','🍍','🥑','🍅','🍆','🍕','🍔','🍟','🌮','🌯','🍜','🍣','🧁','🍰','🎂','🍩','🍪','☕','🍵','🍺','🥂','🍷'],symbols:['🔥','✨','⭐','🌟','💫','⚡','❄️','🌊','🌈','🌙','☀️','✅','❎','🆗','➡️','⬆️','⬇️','🔄','🎵','🎶','🔔','💡','🔑','🎁','🎉','🎊']};
function chatToggleEmoji(){ const p=document.getElementById('chatEmojiPanel'); p.classList.toggle('open'); if(p.classList.contains('open')) chatShowEmoji('smileys'); }
function chatShowEmoji(cat){ chatCurEmojiCat=cat; document.querySelectorAll('.emoji-tab').forEach(t=>t.classList.remove('active')); const cats=['smileys','gestures','hearts','animals','food','symbols']; const idx=cats.indexOf(cat); if(idx>=0) document.querySelectorAll('.emoji-tab')[idx].classList.add('active'); document.getElementById('chatEmojiGrid').innerHTML=(chatEmojis[cat]||[]).map(e=>`<span onclick="chatInsertEmoji('${e}')">${e}</span>`).join(''); }
function chatSearchEmoji(q){ if(!q){ chatShowEmoji(chatCurEmojiCat); return; } const all=Object.values(chatEmojis).flat(); document.getElementById('chatEmojiGrid').innerHTML=all.slice(0,64).map(e=>`<span onclick="chatInsertEmoji('${e}')">${e}</span>`).join(''); }
function chatInsertEmoji(e){ const inp=document.getElementById('chatMsgInput'); const pos=inp.selectionStart; inp.value=inp.value.substring(0,pos)+e+inp.value.substring(pos); inp.focus(); inp.setSelectionRange(pos+e.length,pos+e.length); document.getElementById('chatEmojiPanel').classList.remove('open'); }
document.addEventListener('click',e=>{ const p=document.getElementById('chatEmojiPanel'); const b=document.getElementById('chatEmojiBtn'); if(p&&p.classList.contains('open')&&!p.contains(e.target)&&b&&!b.contains(e.target)) p.classList.remove('open'); });

/* ── REPLY ── */
function chatSetReply(text,wid,dbId){ chatReplyData={text,waMsgId:wid,dbId}; document.getElementById('chatReplyBar').style.display='flex'; document.getElementById('chatReplyText').textContent=text; document.getElementById('chatMsgInput').focus(); }
function chatCancelReply(){ chatReplyData=null; document.getElementById('chatReplyBar').style.display='none'; }

/* ── COPY ── */
function chatCopy(text){ navigator.clipboard.writeText(text).then(()=>chatToast('✓ Copied')); }

/* ── REACT ── */
function chatShowReact(e,id){ e.stopPropagation(); chatReactMsgId=id; const p=document.getElementById('reactPicker'); p.classList.add('show'); p.style.top=Math.max(70,e.clientY-60)+'px'; p.style.left=Math.max(10,e.clientX-120)+'px'; }
function chatReact(emoji){ if(!chatReactMsgId) return; document.getElementById('reactPicker').classList.remove('show'); fetch('/chat/react',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({message_id:chatReactMsgId,emoji})}).then(()=>chatLoadMessages()); }

/* ── DELETE ── */
function chatDelete(id){ if(!confirm('Delete this message?')) return; fetch('/chat/delete-message',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({message_id:id})}).then(()=>chatLoadMessages()); }

/* ── MSG MENU ── */
function chatToggleMsgMenu(e,id){ e.stopPropagation(); document.querySelectorAll('.msg-dropdown.open').forEach(el=>{ if(el.id!==id) el.classList.remove('open'); }); document.getElementById(id)?.classList.toggle('open'); }

/* ── FORWARD ── */
function chatOpenFwd(msg){ chatFwdMsg=msg; document.getElementById('chatFwdModal').classList.add('show'); document.getElementById('chatFwdList').innerHTML=chatAllUsers.map(u=>{ const n=u.name||u.display_phone||u.phone; return `<div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="cursor:pointer;" onclick="chatSendFwd('${u.phone}')" onmouseover="this.style.background='#f5f6f6'" onmouseout="this.style.background=''"><div style="width:36px;height:36px;border-radius:50%;background:#dfe5e7;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">${n.slice(0,2).toUpperCase()}</div><span style="font-size:14px;">${chatEsc(n)}</span></div>`; }).join(''); }
function chatCloseFwd(){ document.getElementById('chatFwdModal').classList.remove('show'); }
function chatSendFwd(phone){ if(!chatFwdMsg.trim()){ chatToast('No message'); return; } const fd=new FormData(); fd.append('phone',phone); fd.append('message',chatFwdMsg); fetch('/chat/send',{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:fd}).then(r=>r.json()).then(d=>{ chatCloseFwd(); if(d.error) chatToast('❌ '+d.error); else chatToast('✅ Forwarded'); }); }

/* ── TEMPLATE ── */
function chatOpenTpl(){
    if(!chatCurrentPhone){ chatToast('Select a chat first'); return; }
    chatSelTpl=null; document.getElementById('chatTplModal').classList.add('show');
    document.getElementById('chatTplSearch').value='';
    document.getElementById('chatTplList').innerHTML='<div class="text-center text-muted py-3">Loading…</div>';
    fetch('/templates/meta').then(r=>r.json()).then(data=>{ chatMetaTpls=(data.templates||[]).filter(t=>t.status==='APPROVED'); chatRenderTplList(chatMetaTpls); }).catch(()=>{ document.getElementById('chatTplList').innerHTML='<div class="text-center text-danger py-3">Could not load templates.</div>'; });
}
function chatCloseTpl(){ document.getElementById('chatTplModal').classList.remove('show'); chatSelTpl=null; }
function chatFilterTpl(){ const q=document.getElementById('chatTplSearch').value.toLowerCase(); chatRenderTplList(chatMetaTpls.filter(t=>`${t.name} ${t.language} ${t.preview}`.toLowerCase().includes(q))); }
function chatRenderTplList(tpls){
    if(chatSelTpl){ chatRenderTplForm(); return; }
    if(!tpls.length){ document.getElementById('chatTplList').innerHTML='<div class="text-center text-muted py-3">No approved templates found.</div>'; return; }
    document.getElementById('chatTplList').innerHTML=tpls.map(t=>`<div class="tpl-item"><div class="tpl-name">${chatEsc(t.name)} <span class="text-muted fw-normal" style="font-size:11px;">(${chatEsc(t.language)})</span></div><div class="tpl-preview">${chatEsc(t.preview||'No preview')}</div><div class="text-muted mb-2" style="font-size:11px;">Variables: ${t.parameter_count}</div><button class="tpl-use-btn" onclick="chatSelTpl=chatMetaTpls[${chatMetaTpls.indexOf(t)}];chatRenderTplForm()">Use template</button></div>`).join('');
}
function chatRenderTplForm(){
    const phs=[...new Set((chatSelTpl.preview||'').match(/\{\{\d+\}\}/g)||[])];
    let prevHtml=chatEsc(chatSelTpl.preview||''); phs.forEach(ph=>{ prevHtml=prevHtml.replace(chatEsc(ph),`<span style="background:#dcf8c6;color:#075e54;font-weight:700;padding:1px 4px;border-radius:4px;">${chatEsc(ph)}</span>`); });
    const fields=phs.map((ph,i)=>`<div class="tpl-field"><label>Variable ${i+1} <span class="text-muted fw-normal">(${chatEsc(ph)})</span></label><input type="text" id="chatTplVar${i}" placeholder="Enter value" class="form-control form-control-sm mt-1"></div>`).join('');
    let hasImg=false; if(chatSelTpl.components){ chatSelTpl.components.forEach(c=>{ if(c.type==='HEADER'&&c.format==='IMAGE') hasImg=true; }); }
    document.getElementById('chatTplList').innerHTML=`<div class="tpl-item">
        <div class="tpl-name">${chatEsc(chatSelTpl.name)}</div>
        <div class="p-3 rounded-3 mb-3" style="background:#f0fdf4;border:1px solid #c8e6c9;font-size:13px;line-height:1.6;white-space:pre-wrap;">${prevHtml}</div>
        ${fields||'<div class="text-muted mb-3" style="font-size:13px;">✅ No variables required.</div>'}
        <div class="tpl-field mt-2"><label>Language Code *</label><input type="text" id="chatTplLang" value="${chatEsc(chatSelTpl.language||'en_US')}" class="form-control form-control-sm mt-1"></div>
        ${hasImg?`<div class="tpl-field mt-2"><label>Header Image URL</label><input type="text" id="chatTplImg" placeholder="https://example.com/image.jpg" class="form-control form-control-sm mt-1"></div>`:''}
        <div class="mt-3">
            <button class="tpl-back-btn" onclick="chatSelTpl=null;chatRenderTplList(chatMetaTpls)"><i class="bi bi-arrow-left me-1"></i>Back</button>
            <button class="tpl-send-btn" onclick="chatSendTpl()"><i class="bi bi-send me-1"></i>Send Template</button>
        </div>
    </div>`;
}
function chatSendTpl(){
    if(!chatCurrentPhone||!chatSelTpl) return;
    const phs=(chatSelTpl.preview||'').match(/\{\{\d+\}\}/g)||[]; const params=[];
    for(let i=0;i<phs.length;i++){ const v=document.getElementById(`chatTplVar${i}`)?.value.trim(); if(!v){ chatToast(`Fill variable ${i+1}`); return; } params.push(v); }
    const lang=document.getElementById('chatTplLang')?.value.trim()||chatSelTpl.language||'en_US';
    const img=document.getElementById('chatTplImg')?.value.trim()||'';
    const btn=document.querySelector('.tpl-send-btn'); if(btn){ btn.textContent='Sending…'; btn.disabled=true; }
    fetch('/chat/send-template',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({recipient_phone:chatCurrentPhone,template_name:chatSelTpl.name,language_code:lang,parameters:params,header_image:img})})
    .then(r=>r.json()).then(d=>{ if(btn){ btn.innerHTML='<i class="bi bi-send me-1"></i>Send Template'; btn.disabled=false; } if(d.error){ chatToast('❌ '+d.error); return; } chatCloseTpl(); chatLoadMessages(); chatToast('✅ Template sent'); })
    .catch(()=>{ if(btn){ btn.innerHTML='<i class="bi bi-send me-1"></i>Send Template'; btn.disabled=false; } chatToast('❌ Network error'); });
}
function chatShowTplPreview(name,preview){ const tpl=(chatMetaTpls||[]).find(t=>t.name===name); document.getElementById('chatTplPreviewName').textContent=name; document.getElementById('chatTplPreviewBody').textContent=tpl?tpl.preview:preview||'No preview'; document.getElementById('chatTplPreviewModal').style.display='flex'; }

/* ── ASSIGN ── */
function chatLoadTeam(){ fetch('/chat/team-members',{credentials:'same-origin'}).then(r=>r.json()).then(d=>{ chatTeamMembers=d.members||[]; chatRenderAgents(); }).catch(()=>{}); }
function chatRenderAgents(){ const l=document.getElementById('chatAgentList'); if(!l) return; if(!chatTeamMembers.length){ l.innerHTML='<div class="px-3 py-2 text-muted" style="font-size:13px;">No team members</div>'; return; } l.innerHTML=chatTeamMembers.map(m=>`<div class="px-3 py-2 border-bottom" style="cursor:pointer;" onclick="chatAssign(${m.id})" onmouseover="this.style.background='#f5f6f6'" onmouseout="this.style.background=''"><div class="fw-semibold" style="font-size:13px;color:#1a1a2e;">${chatEsc(m.name)}</div><div class="text-muted" style="font-size:11px;">${m.team_role}</div></div>`).join(''); }
function chatToggleAssign(){ const d=document.getElementById('chatAssignDrop'); d.style.display=d.style.display==='block'?'none':'block'; }
function chatAssign(agentId){
    if(!chatCurrentPhone) return;
    fetch('/chat/assign',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({wa_id:chatCurrentPhone,agent_id:agentId})})
    .then(r=>r.json()).then(d=>{ document.getElementById('chatAssignDrop').style.display='none'; const b=document.getElementById('chatAssignedBadge'); if(agentId&&d.agent){ b.textContent='👤 '+d.agent; b.classList.remove('d-none'); } else b.classList.add('d-none'); chatLoadUsers(); chatToast(agentId?'✅ Assigned to '+d.agent:'✅ Unassigned'); }).catch(()=>chatToast('❌ Failed'));
}
document.addEventListener('click',e=>{ const w=document.getElementById('chatAssignWrap'); if(w&&!w.contains(e.target)) document.getElementById('chatAssignDrop').style.display='none'; });

/* ── NEW CHAT ── */
function chatNewContact() {
    const overlay = document.createElement('div');
    overlay.id = 'newChatOverlay';
    overlay.innerHTML = `
        <div style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
            <div style="background:#fff;border-radius:16px;padding:28px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                    <div style="width:44px;height:44px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-chat-dots-fill" style="font-size:20px;color:#25d366;"></i>
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:700;color:#1a1a2e;">New Chat</div>
                        <div style="font-size:12px;color:#888;">Enter a WhatsApp number to start</div>
                    </div>
                </div>
                <label style="font-size:12px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:6px;">Phone Number</label>
                <input id="newChatInput" type="tel" placeholder="e.g. 919876543210"
                    style="width:100%;padding:11px 14px;border:1.5px solid #e5e9f0;border-radius:10px;font-size:14px;outline:none;color:#1a1a2e;font-family:inherit;margin-bottom:6px;"
                    onfocus="this.style.borderColor='#25d366'" onblur="this.style.borderColor='#e5e9f0'"
                    onkeydown="if(event.key==='Enter') confirmNewChat()">
                <div style="font-size:11px;color:#aaa;margin-bottom:20px;">Include country code without + (e.g. 91 for India)</div>
                <div style="display:flex;gap:10px;">
                    <button onclick="document.getElementById('newChatOverlay').remove()"
                            style="flex:1;padding:11px;border:1.5px solid #e5e9f0;border-radius:10px;background:#fff;color:#555;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
                        Cancel
                    </button>
                    <button onclick="confirmNewChat()"
                            style="flex:1;padding:11px;border:none;border-radius:10px;background:#25d366;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
                        <i class="bi bi-chat-dots me-1"></i>Start Chat
                    </button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(overlay);
    setTimeout(() => document.getElementById('newChatInput')?.focus(), 100);
}

function confirmNewChat() {
    const input = document.getElementById('newChatInput');
    if (!input) return;
    const c = input.value.replace(/\D/g, '');
    if (c.length < 8) {
        input.style.borderColor = '#e53935';
        input.placeholder = 'Invalid number — try again';
        return;
    }
    document.getElementById('newChatOverlay')?.remove();
    chatOpenChat(c, c, '');
}

/* ── TOP MENU ── */
function chatToggleTopMenu(){ document.getElementById('chatTopMenu').classList.toggle('open'); }
function chatCloseTopMenu(){ document.getElementById('chatTopMenu').classList.remove('open'); }
function chatToggleAI(cb){
    if(!chatCurrentPhone){ cb.checked=!cb.checked; return; }
    fetch('/ai/toggle-conversation',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({wa_id:chatCurrentPhone, channel:'whatsapp', status:cb.checked?'on':'off'})})
    .then(r=>r.json()).then(d=>{ chatToast(cb.checked ? 'AI enabled for this chat' : 'AI disabled for this chat'); })
    .catch(()=>{ chatToast('Failed to update AI toggle'); cb.checked=!cb.checked; });
}
function chatToggleGlobalAI(cb){
    if(!confirm(cb.checked ? 'Turn ON AI replies for ALL conversations?' : 'Turn OFF AI replies for ALL conversations?')) { cb.checked=!cb.checked; return; }
    fetch('/ai/toggle-global',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body:JSON.stringify({status:cb.checked?'on':'off'})})
    .then(r=>r.json()).then(d=>{ chatToast(cb.checked ? 'AI enabled for all conversations' : 'AI disabled for all conversations'); })
    .catch(()=>{ chatToast('Failed to update global AI toggle'); cb.checked=!cb.checked; });
}
function chatRefreshAiToggleState(){
    if(!chatCurrentPhone) return;
    fetch('/ai/conversation-state?wa_id='+encodeURIComponent(chatCurrentPhone)+'&channel=whatsapp',{credentials:'same-origin'})
    .then(r=>r.json()).then(d=>{ const el=document.getElementById('aiToggle'); if(el) el.checked = !!d.ai_enabled; });
}

/* ── PROFILE ── */
function chatOpenProfile(phone){
    if(!phone) return; chatProfilePhone=phone;
    document.getElementById('chatProfilePanel').classList.add('open'); document.getElementById('chatProfileOverlay').classList.add('show');
    document.getElementById('chatProfPhone').textContent=phone; document.getElementById('chatProfAvatar').innerHTML='<i class="bi bi-person-fill" style="font-size:34px;color:#fff;"></i>'; document.getElementById('chatProfName').textContent='Loading…';
    fetch('/chat/profile/'+phone).then(r=>r.json()).then(d=>{
        document.getElementById('chatProfName').textContent=d.name||phone;
        document.getElementById('chatProfMsgCount').textContent=d.message_count||0;
        document.getElementById('chatProfSince').textContent=d.chat_since||'—';
        document.getElementById('chatProfLast').textContent=d.last_seen||'—';
        document.getElementById('chatSaveContactForm').style.display=d.is_saved?'none':'block';
        document.getElementById('chatProfMedia').innerHTML=(d.shared_media||[]).map(m=>m.media_type==='image'?`<img src="/chat/media/${m.media_id}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:6px;cursor:pointer;" onclick="chatOpenMedia('/chat/media/${m.media_id}','image')">`:`<div style="background:#f0f2f5;border-radius:6px;aspect-ratio:1;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">📄</div>`).join('')||'<div class="text-muted" style="font-size:12px;">No media</div>';
    }).catch(()=>{});
}
function chatCloseProfile(){ document.getElementById('chatProfilePanel').classList.remove('open'); document.getElementById('chatProfileOverlay').classList.remove('show'); }
function chatDoSave(){ const n=document.getElementById('chatSaveContactName').value.trim(); if(!n){ chatToast('Enter a name'); return; } fetch('/chat/save-contact',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({name:n,phone:chatProfilePhone})}).then(r=>r.json()).then(d=>{ if(d.status==='saved'){ document.getElementById('chatSaveContactForm').style.display='none'; document.getElementById('chatProfName').textContent=n; chatLoadUsers(); } }); }

/* ── MEDIA ── */
function chatOpenMedia(url,type){ document.getElementById('mediaPreviewContent').innerHTML=type==='image'?`<img src="${url}" style="max-width:92vw;max-height:82vh;border-radius:8px;object-fit:contain;">`:`<video controls autoplay playsinline style="max-width:92vw;max-height:82vh;border-radius:8px;"><source src="${url}" type="video/mp4">Your browser cannot play this video.</video>`; document.getElementById('mediaDownloadBtn').href=url; document.getElementById('mediaPreviewModal').classList.add('show'); }
function chatCloseMedia(){ document.getElementById('mediaPreviewModal').classList.remove('show'); }

/* ── AUTO REFRESH ── */
setInterval(()=>{
    fetch('/chat/last-update').then(r=>r.json()).then(d=>{ if(d.last!==chatLastUpdate){ chatLastUpdate=d.last; chatLoadUsers(); if(chatCurrentPhone) chatLoadMessages(); } }).catch(()=>{});
},2500);

/* ── CLOSE ON OUTSIDE CLICK ── */
document.addEventListener('click',e=>{
    const tm=document.getElementById('chatTopMenu'); const tw=document.getElementById('chatTopMenuWrap');
    if(tm&&tw&&!tw.contains(e.target)) tm.classList.remove('open');
    document.getElementById('reactPicker').classList.remove('show');
    document.querySelectorAll('.msg-dropdown.open').forEach(el=>el.classList.remove('open'));
});

/* ── INIT ── */
document.addEventListener('DOMContentLoaded',()=>{
    const pc=document.getElementById('fuContent'); if(pc){ pc.style.padding='0'; pc.style.overflow='hidden'; }
    if(chatIsMobile) document.getElementById('contactsPanel').classList.remove('hidden');
});

chatLoadUsers();
chatLoadTeam();
</script>
@endpush