<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shrimp Chat API Tester</title>
    <style>
        /* CSS hanya untuk penempatan, tanpa hiasan */
        body { font-family: sans-serif; margin: 0; padding: 0; display: flex; flex-direction: column; height: 100vh; }
        header { background: #eee; padding: 10px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        .container { display: flex; flex: 1; overflow: hidden; }
        .sidebar { width: 300px; border-right: 1px solid #ccc; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 20px; }
        .main-content { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 20px; }
        .box { border: 1px solid #999; padding: 10px; margin-bottom: 10px; }
        .hidden { display: none !important; }
        .flex-row { display: flex; gap: 10px; align-items: center; }
        .flex-col { display: flex; flex-direction: column; gap: 5px; }
        .chat-area { display: flex; flex-direction: column; height: 400px; border: 1px solid #ccc; }
        .messages { flex: 1; overflow-y: auto; padding: 10px; display: flex; flex-direction: column; gap: 10px; }
        .message-input-area { display: flex; gap: 10px; padding: 10px; border-top: 1px solid #ccc; }
        .message-input-area input { flex: 1; }
        .item-list { border-bottom: 1px dotted #ccc; padding: 5px 0; }
    </style>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body>
<div id="toast-message" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:10px 20px; border-radius:5px; z-index:9999; text-align:center; box-shadow: 0 4px 6px rgba(0,0,0,0.3);"></div>

<header>
    <div>
        <h2>Shrimp Chat Tester</h2>
    </div>
    <div id="auth-status" class="flex-row">
        <span id="logged-in-user">Not Logged In</span>
        <button id="btn-edit-profile" class="hidden" onclick="document.getElementById('section-profile').classList.toggle('hidden')">Edit Profile</button>
        <button id="btn-logout" class="hidden">Logout</button>
    </div>
</header>

<div class="container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <!-- Auth Section -->
        <div id="section-auth" class="box">
            <h3>Login / Register</h3>
            <div class="flex-col">
                <input type="text" id="auth-name" placeholder="Name (for Register)">
                <input type="email" id="auth-email" placeholder="Email">
                <input type="password" id="auth-password" placeholder="Password">
                <div class="flex-row">
                    <button onclick="login()">Login</button>
                    <button onclick="register()">Register</button>
                </div>
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div id="section-requests" class="box hidden">
            <h3>Pending Contact Requests</h3>
            <button onclick="loadPendingRequests()">Refresh</button>
            <div id="requests-list"></div>
        </div>

        <!-- Rooms Section -->
        <div id="section-rooms" class="box hidden">
            <h3>Chat Rooms</h3>
            <button onclick="loadRooms()">Refresh</button>
            <div id="rooms-list"></div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- Profile Completion -->
        <div id="section-profile" class="box hidden">
            <h3>Complete Profile</h3>
            <div class="flex-row">
                <input type="text" id="profile-fullname" placeholder="Full Name">
                <input type="date" id="profile-birthdate">
                <select id="profile-gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <button onclick="completeProfile()">Save Profile</button>
            </div>
        </div>

        <!-- Search User -->
        <div id="section-search" class="box hidden">
            <h3>Search Users</h3>
            <div class="flex-row">
                <input type="text" id="search-keyword" placeholder="Search by name, email, full_name">
                <button onclick="searchUsers()">Search</button>
            </div>
            <div id="search-results" style="margin-top: 10px;"></div>
        </div>

        <!-- Create Group (Placeholder) -->
        <div id="section-group" class="box hidden">
            <h3>Create Group</h3>
            <div class="flex-row">
                <input type="text" id="group-name" placeholder="Group Name">
                <button onclick="alert('Belum ada endpoint API untuk membuat grup. Fitur ini masih placeholder.')">Create Group</button>
            </div>
        </div>

        <!-- Active Chat -->
        <div id="section-chat" class="box hidden flex-col">
            <h3 id="active-room-title">Select a room to chat</h3>
            <div class="chat-area">
                <div class="messages" id="chat-messages"></div>
                <div class="message-input-area">
                    <input type="text" id="chat-input" placeholder="Type a message...">
                    <button onclick="sendMessage()">Send</button>
                </div>
            </div>
            <button onclick="loadMessages()">Refresh Messages</button>
        </div>
    </div>
</div>

<script>
    const API_BASE = '/api/v1';
    let currentRoomId = null;
    let currentUser = null;
    let pusher = null;
    let currentChannel = null;

    window.alert = function(msg) {
        let msgStr = typeof msg === 'string' ? msg : JSON.stringify(msg);
        const toast = document.getElementById('toast-message');
        toast.innerText = msgStr;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 4000);
    };

    // Utility: Fetch with Auth
    async function apiFetch(path, options = {}) {
        const token = localStorage.getItem('token');
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` }),
            ...(options.headers || {})
        };
        const response = await fetch(`${API_BASE}${path}`, { ...options, headers });
        const data = await response.json().catch(() => null);
        return { status: response.status, data };
    }

    // UI Updates
    function updateUI() {
        const token = localStorage.getItem('token');
        if (token) {
            document.getElementById('section-auth').classList.add('hidden');
            document.getElementById('section-profile').classList.add('hidden');
            document.getElementById('section-search').classList.remove('hidden');
            document.getElementById('section-requests').classList.remove('hidden');
            document.getElementById('section-rooms').classList.remove('hidden');
            document.getElementById('section-group').classList.remove('hidden');
            document.getElementById('section-chat').classList.remove('hidden');
            document.getElementById('btn-logout').classList.remove('hidden');
            document.getElementById('btn-edit-profile').classList.remove('hidden');
            fetchMe();
            loadPendingRequests();
            loadRooms();
        } else {
            document.getElementById('section-auth').classList.remove('hidden');
            document.getElementById('section-profile').classList.add('hidden');
            document.getElementById('section-search').classList.add('hidden');
            document.getElementById('section-requests').classList.add('hidden');
            document.getElementById('section-rooms').classList.add('hidden');
            document.getElementById('section-group').classList.add('hidden');
            document.getElementById('section-chat').classList.add('hidden');
            document.getElementById('btn-logout').classList.add('hidden');
            document.getElementById('btn-edit-profile').classList.add('hidden');
            document.getElementById('logged-in-user').innerText = 'Not Logged In';
        }
    }

    // Auth
    async function login() {
        const email = document.getElementById('auth-email').value;
        const password = document.getElementById('auth-password').value;
        const res = await apiFetch('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        if (res.data && (res.data.access_token || res.data.token)) {
            const token = res.data.access_token || res.data.token;
            localStorage.setItem('token', token);
            alert('Login successful');
            updateUI();
        } else {
            alert('Login failed: ' + JSON.stringify(res.data));
        }
    }

    async function register() {
        const name = document.getElementById('auth-name').value;
        const email = document.getElementById('auth-email').value;
        const password = document.getElementById('auth-password').value;
        const res = await apiFetch('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password, password_confirmation: password })
        });
        if (res.status === 201) {
            alert('Register successful. You can now login.');
        } else {
            alert('Register failed: ' + JSON.stringify(res.data));
        }
    }

    document.getElementById('btn-logout').addEventListener('click', async () => {
        await apiFetch('/auth/logout', { method: 'POST' });
        localStorage.removeItem('token');
        updateUI();
    });

    async function fetchMe() {
        const res = await apiFetch('/auth/me', { method: 'GET' });
        if (res.data && res.data.data) {
            currentUser = res.data.data;
            document.getElementById('logged-in-user').innerText = `Logged in as: ${currentUser.name} (${currentUser.email})`;
            connectWebSocket();
        }
    }

    function connectWebSocket() {
        if (!pusher) {
            pusher = new Pusher('{{ env('REVERB_APP_KEY') }}', {
                wsHost: window.location.hostname,
                wsPort: 8080,
                forceTLS: false,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1',
                authEndpoint: '/api/v1/broadcasting/auth',
                auth: {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    }
                }
            });
        }
    }

    // Profile
    async function completeProfile() {
        const full_name = document.getElementById('profile-fullname').value;
        const birth_date = document.getElementById('profile-birthdate').value;
        const gender = document.getElementById('profile-gender').value;
        
        const res = await apiFetch('/auth/onboarding', {
            method: 'POST',
            body: JSON.stringify({ full_name, birth_date, gender })
        });
        alert(JSON.stringify(res.data));
    }

    // Search
    async function searchUsers() {
        const keyword = document.getElementById('search-keyword').value;
        const res = await apiFetch(`/users/search?q=${keyword}`, { method: 'GET' });
        const list = document.getElementById('search-results');
        list.innerHTML = '';
        if (res.data && res.data.data) {
            res.data.data.forEach(u => {
                const div = document.createElement('div');
                div.className = 'item-list flex-row';
                const name = u.profile ? u.profile.full_name : u.name;
                div.innerHTML = `
                    <div style="flex:1"><b>${name}</b> (${u.email})</div>
                    <button onclick="sendRequest(${u.id})">Add Contact</button>
                `;
                list.appendChild(div);
            });
        }
    }

    // Contacts
    async function sendRequest(target_user_id) {
        const res = await apiFetch('/contacts/request', {
            method: 'POST',
            body: JSON.stringify({ target_user_id })
        });
        alert(JSON.stringify(res.data));
    }

    async function loadPendingRequests() {
        const res = await apiFetch('/contacts/requests/pending', { method: 'GET' });
        const list = document.getElementById('requests-list');
        list.innerHTML = '';
        if (res.data && res.data.data) {
            res.data.data.forEach(req => {
                const div = document.createElement('div');
                div.className = 'item-list flex-col';
                const name = req.requester && req.requester.profile ? req.requester.profile.full_name : req.requester.name;
                div.innerHTML = `
                    <div>From: <b>${name}</b></div>
                    <div class="flex-row">
                        <button onclick="respondRequest(${req.id}, 'accept')">Accept</button>
                        <button onclick="respondRequest(${req.id}, 'reject')">Reject</button>
                    </div>
                `;
                list.appendChild(div);
            });
        }
    }

    async function respondRequest(contact_id, action) {
        const res = await apiFetch(`/contacts/requests/${contact_id}/respond`, {
            method: 'PATCH',
            body: JSON.stringify({ action })
        });
        alert(JSON.stringify(res.data));
        loadPendingRequests();
        loadRooms(); // Room might be created if accepted
    }

    // Rooms & Chat
    async function loadRooms() {
        const res = await apiFetch('/chat/rooms', { method: 'GET' });
        const list = document.getElementById('rooms-list');
        list.innerHTML = '';
        if (res.data && res.data.data) {
            res.data.data.forEach(room => {
                const div = document.createElement('div');
                div.className = 'item-list flex-row';
                div.innerHTML = `
                    <div style="flex:1">Room #${room.id} (${room.type})</div>
                    <button onclick="openRoom(${room.id})">Open Chat</button>
                `;
                list.appendChild(div);
            });
        }
    }

    async function openRoom(roomId) {
        currentRoomId = roomId;
        document.getElementById('active-room-title').innerText = `Room #${roomId}`;
        loadMessages();

        if (pusher) {
            if (currentChannel) {
                pusher.unsubscribe(currentChannel.name);
            }
            currentChannel = pusher.subscribe('private-room.' + roomId);
            currentChannel.bind('message.new', function(data) {
                appendMessage(data.message);
            });
        }
    }

    async function loadMessages() {
        if (!currentRoomId) return;
        const res = await apiFetch(`/chat/rooms/${currentRoomId}/messages`, { method: 'GET' });
        const list = document.getElementById('chat-messages');
        list.innerHTML = '';
        if (res.data && res.data.data) {
            res.data.data.forEach(msg => appendMessage(msg, list));
        }
    }

    function appendMessage(msg, container = null) {
        const list = container || document.getElementById('chat-messages');
        const div = document.createElement('div');
        
        let senderName = 'Unknown';
        if (msg.sender) {
            senderName = msg.sender.profile && msg.sender.profile.full_name ? msg.sender.profile.full_name : msg.sender.name;
        }

        const isMe = currentUser && msg.sender_id === currentUser.id;
        
        if (isMe) {
            div.style.textAlign = 'right';
            div.innerHTML = `<span style="background: #dcf8c6; padding: 5px 10px; border-radius: 10px; display: inline-block; margin-left: 20%; word-break: break-all;"><b>You</b>: ${msg.content}</span>`;
        } else {
            div.style.textAlign = 'left';
            div.innerHTML = `<span style="background: #eee; padding: 5px 10px; border-radius: 10px; display: inline-block; margin-right: 20%; word-break: break-all;"><b>${senderName}</b>: ${msg.content}</span>`;
        }
        
        list.appendChild(div);
        list.scrollTop = list.scrollHeight;
    }

    async function sendMessage() {
        if (!currentRoomId) {
            alert('Select a room first!');
            return;
        }
        const content = document.getElementById('chat-input').value;
        if (!content) return;

        const res = await apiFetch('/chat/messages', {
            method: 'POST',
            body: JSON.stringify({ room_id: currentRoomId, content })
        });
        if (res.status === 201) {
            document.getElementById('chat-input').value = '';
            loadMessages();
        } else {
            alert('Failed to send message: ' + JSON.stringify(res.data));
        }
    }

    // Initialize UI on load
    updateUI();

</script>
</body>
</html>
