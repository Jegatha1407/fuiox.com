// WhatsApp-style chat frontend logic (initial refactor)
// Keeps existing behavior from resources/views/chat.blade.php.

console.log('Chat script loaded successfully');

let currentPhone = '';
let currentName = '';
let lastUpdate   = '';
let allUsers     = [];

let mediaRecorder   = null;
let audioChunks     = [];
let isRecording     = false;
let recordingTimer  = null;
let mediaPreviewModal = null;

/* ── LOAD USERS ──────────────────────── */
function loadUsers() {
    fetch('/chat/users')
        .then(r => r.json())
        .then(data => {
            allUsers = data;
            renderUsers(data);
        })
        .catch(() => {});
}

function renderUsers(data) {
    let html = '';
    if (data.length === 0) {
        html = `<div style="padding:2rem;text-align:center;color:#aaa;font-size:0.85rem;">No contacts yet</div>`;
    }

    data.forEach(u => {
        const initials = u.name
            ? u.name.slice(0, 2).toUpperCase()
            : (u.display_phone ? u.display_phone.slice(-2) : (u.phone ? u.phone.slice(-2) : '?'));

        const activeClass = u.phone === currentPhone ? 'active' : '';
        const contactName = u.name || u.display_phone || u.phone;
        const safeName = String(u.name || u.display_phone || u.phone).replace(/'/g, "\\'");
        const phoneLine = `<div class="user-number">${u.display_phone || u.phone}</div>`;

        html += `
            <div class="user ${activeClass}" onclick="openChat('${u.phone}', '${safeName}')">
                <div class="user-avatar">${initials}</div>
                <div class="user-info">
                    <div class="user-phone">${contactName}</div>
                    ${phoneLine}
                    <div class="user-last">${u.last_message || ''}</div>
                    ${u.unread_count > 0 ? `
<div style="
background:#00a884;
color:white;
font-size:11px;
padding:2px 7px;
border-radius:20px;
display:inline-block;
margin-top:5px;">
${u.unread_count}
</div>` : ''}
                </div>
            </div>`;
    });

    const usersEl = document.getElementById('users');
    if (usersEl) usersEl.innerHTML = html;
}

/* ── SEARCH FILTER ───────────────────── */
function filterUsers() {
    const q = document.getElementById('searchInput')?.value?.toLowerCase() || '';
    const filtered = allUsers.filter(u => (u.phone || '').toLowerCase().includes(q));
    renderUsers(filtered);
}

/* ── OPEN CHAT ───────────────────────── */
function openChat(phone, name = '') {
    currentPhone = phone;
    currentName = name || phone;

    document.getElementById('headerAvatar').innerText =
        (currentName || '?').substring(0, 1).toUpperCase();

    document.getElementById('headerName').innerText = currentName;
    document.getElementById('headerStatus').innerText = 'online';

    document.getElementById('msgInput').disabled = false;
    document.getElementById('sendBtn').disabled = false;
    document.getElementById('voiceBtn').disabled = false;

    document.getElementById('msgInput').placeholder = 'Type a message...';

    // Close sidebar on mobile
    document.getElementById('sidebar').classList.remove('active');
    document.getElementById('overlay').classList.remove('active');

    renderUsers(allUsers);
    loadMessages();
}

/* ── LOAD MESSAGES ───────────────────── */
function loadMessages() {
    if (!currentPhone) return;

    fetch('/chat/messages/' + currentPhone)
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                document.getElementById('messages').innerHTML = `
                    <div class="no-chat">
                        <div class="icon">👋</div>
                        <p>No messages yet with ${currentPhone}</p>
                    </div>`;
                return;
            }

            let html = '';

            data.forEach(m => {
                let content = m.message;

                if (m.media_type && m.media_id) {
                    const mediaUrl = `/chat/media/${m.media_id}`;

                    switch (m.media_type) {
                        case 'image':
                            content = `
                                <div style="position:relative;">
                                    <img src="${mediaUrl}"
                                         alt="Image"
                                         style="max-width:250px;max-height:250px;border-radius:8px;cursor:pointer;display:block;"
                                         onclick="previewMedia('${mediaUrl}','image')"
                                         onerror="this.src='';this.alt='Image not available'">
                                    ${m.media_caption ? `<div style="font-size:12px;color:#555;margin-top:4px;">${m.media_caption}</div>` : ''}
                                </div>`;
                            break;

                        case 'audio':
                            content = `
                                <div style="min-width:230px;">
                                    <audio controls preload="metadata" style="width:100%;">
                                        <source src="${mediaUrl}" type="audio/webm">
                                        Your browser does not support audio.
                                    </audio>
                                </div>`;
                            break;

                        case 'video':
                            content = `
                                <div>
                                    <video controls style="max-width:250px;max-height:200px;border-radius:8px;display:block;cursor:pointer;"
                                           onclick="previewMedia('${mediaUrl}','video')">
                                        <source src="${mediaUrl}" type="${m.media_mime_type || 'video/mp4'}">
                                    </video>
                                    ${m.media_caption ? `<div style="font-size:12px;color:#555;margin-top:4px;">${m.media_caption}</div>` : ''}
                                </div>`;
                            break;

                        case 'document':
                            content = `
                                <a href="${mediaUrl}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;background:#f0f0f0;border-radius:10px;text-decoration:none;color:#333;max-width:250px;">
                                    <span style="font-size:24px;">📄</span>
                                    <div>
                                        <div style="font-weight:600;font-size:13px;word-break:break-word;">${m.media_filename || 'Document'}</div>
                                        ${m.media_size ? `<div style="font-size:11px;color:#888;">${formatFileSize(m.media_size)}</div>` : ''}
                                    </div>
                                </a>`;
                            break;

                        default:
                            content = `<a href="${mediaUrl}" target="_blank">📎 Download file</a>`;
                    }
                }

                html += `
                    <div class="msg-row ${m.type}">
                        <div class="msg">
                            ${content}
                            <div class="meta">${m.created_at || ''}</div>
                        </div>
                    </div>`;
            });

            const box = document.getElementById('messages');
            box.innerHTML = html;
            box.scrollTop = box.scrollHeight;
        })
        .catch(() => {});
}

/* ── SEND MESSAGE ────────────────────── */
function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    const mediaInput = document.getElementById('mediaInput');
    const file = mediaInput.files[0];

    if (!msg && !file) return;
    if (!currentPhone) return;

    const formData = new FormData();
    formData.append('phone', currentPhone);

    if (msg) formData.append('message', msg);

    if (file) {
        formData.append('media', file);
        const mimeType = file.type;
        let mediaType = 'document';

        if (mimeType.startsWith('image/')) mediaType = 'image';
        else if (mimeType.startsWith('audio/')) mediaType = 'audio';
        else if (mimeType.startsWith('video/')) mediaType = 'video';

        formData.append('media_type', mediaType);
        if (msg) formData.append('caption', msg);
    }

    input.value = '';
    mediaInput.value = '';
    const bar = document.getElementById('filePreviewBar');
    if (bar) bar.style.display = 'none';
    input.placeholder = 'Type a message...';

    fetch('/chat/send', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            if (data.error) return alert('❌ ' + data.error);
            loadMessages();
        })
        .catch(err => {
            console.error('Error sending message:', err);
            alert('Error sending message');
        });
}

/* ── FILE HANDLING ───────────────────── */
function handleFileSelect() {
    const fileInput = document.getElementById('mediaInput');
    const file = fileInput.files[0];
    if (!file) return;

    if (file.size > 16 * 1024 * 1024) {
        alert('File too large. Maximum size is 16MB.');
        fileInput.value = '';
        return;
    }

    const bar = document.getElementById('filePreviewBar');
    const nameEl = document.getElementById('filePreviewName');

    const fileName = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;
    const icon = file.type.startsWith('image/') ? '🖼️'
        : (file.type.startsWith('audio/') ? '🎵'
            : (file.type.startsWith('video/') ? '🎥' : '📄'));

    nameEl.innerHTML = `${icon} <strong>${fileName}</strong> <span style="color:#888;">(${formatFileSize(file.size)})</span>`;
    bar.style.display = 'flex';

    document.getElementById('msgInput').placeholder = 'Add a caption (optional)...';
    document.getElementById('msgInput').focus();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function clearFile() {
    document.getElementById('mediaInput').value = '';
    document.getElementById('msgInput').placeholder = 'Type a message...';
    document.getElementById('filePreviewBar').style.display = 'none';
}

/* ── MEDIA PREVIEW MODAL ─────────────── */
function previewMedia(url, type) {
    if (mediaPreviewModal) mediaPreviewModal.remove();

    const modal = document.createElement('div');
    modal.id = 'mediaPreviewModal';
    modal.style.cssText = `
        position:fixed;inset:0;background:rgba(0,0,0,0.92);
        z-index:9999;display:flex;align-items:center;
        justify-content:center;flex-direction:column;gap:1rem;
    `;

    let mediaHtml = '';
    if (type === 'image') {
        mediaHtml = `<img src="${url}" style="max-width:90vw;max-height:80vh;border-radius:8px;object-fit:contain;">`;
    } else if (type === 'video') {
        mediaHtml = `<video controls autoplay style="max-width:90vw;max-height:80vh;"><source src="${url}"></video>`;
    }

    modal.innerHTML = `
        ${mediaHtml}
        <div style="display:flex;gap:1rem;">
            <a href="${url}" download
               style="background:#25d366;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;">
               ⬇ Download
            </a>
            <button onclick="document.getElementById('mediaPreviewModal')?.remove()"
                    style="background:#e53935;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600;">
                ✕ Close
            </button>
        </div>`;

    modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });

    document.body.appendChild(modal);
    mediaPreviewModal = modal;
}

/* ── VOICE RECORDING ─────────────────── */
async function startRecording() {
    if (!currentPhone) {
        alert('Select a chat first');
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks = [];

        let mimeType = 'audio/webm';
        if (!MediaRecorder.isTypeSupported(mimeType)) mimeType = '';

        mediaRecorder = new MediaRecorder(stream, mimeType ? { mimeType } : {});

        mediaRecorder.ondataavailable = function(e) {
            if (e.data && e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = async function() {
            const audioBlob = new Blob(audioChunks, { type: mimeType || 'audio/webm' });
            await sendVoiceMessage(audioBlob);
            stream.getTracks().forEach(track => track.stop());
        };

        mediaRecorder.start();
        isRecording = true;

        document.getElementById('voiceBtn').innerHTML = '⏹';
        document.getElementById('voiceBtn').style.background = '#e53935';
        document.getElementById('msgInput').placeholder = 'Recording voice...';
    } catch (err) {
        console.log(err);
        alert('Microphone permission denied');
    }
}

function stopRecording() {
    if (!isRecording || !mediaRecorder) return;

    clearTimeout(recordingTimer);
    mediaRecorder.stop();
    isRecording = false;

    document.getElementById('voiceBtn').style.background = '#25d366';
    document.getElementById('voiceBtn').innerHTML = '🎤';
    document.getElementById('msgInput').placeholder = 'Type a message...';
}

async function sendVoiceMessage(blob) {
    try {
        const formData = new FormData();
        formData.append('phone', currentPhone);
        formData.append('media', blob, 'voice-message.webm');
        formData.append('media_type', 'audio');
        formData.append('message', '');

        const response = await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: formData
        });

        const data = await response.json();
        if (data.error) return alert(data.error);

        loadMessages();
    } catch (err) {
        console.log(err);
        alert('Voice send failed');
    }
}

/* ── SIDEBAR TOGGLE ──────────────────── */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}

// Auto load
loadUsers();

// Expose for inline handlers
window.loadUsers = loadUsers;
window.renderUsers = renderUsers;
window.filterUsers = filterUsers;
window.openChat = openChat;
window.loadMessages = loadMessages;
window.sendMessage = sendMessage;
window.handleFileSelect = handleFileSelect;
window.clearFile = clearFile;
window.previewMedia = previewMedia;
window.startRecording = startRecording;
window.stopRecording = stopRecording;
window.toggleSidebar = toggleSidebar;

