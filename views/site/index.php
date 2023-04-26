<?php

/** @var yii\web\View $this */

$this->title = 'Web Chat';
?>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Chat yaratish
</button>


<div class="row mt-5">
    <div class="col-4">
        <div id="chats-list" class="list-group"></div>
    </div>
    <div class="col-8">
        <div id="messages-content" style="max-height: calc(100vh - 400px);overflow: hidden; overflow-y: auto;">
            <ul id="messages" class="list-unstyled">

            </ul>
        </div>

        <div class="form-outline mt-5">
            <textarea class="form-control" id="message-text" rows="4" placeholder="Habar..."></textarea>
        </div>

        <button type="button" onclick="sendMessage(this);" class="btn btn-success btn-rounded float-end mt-3">Yuborish</button>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="new-chat-title" class="form-label">Chat nomi</label>
                    <input type="text" class="form-control" id="new-chat-title">
                </div>
                <div class="mb-3">
                    <label for="new-chat-message" class="form-label">Birinchi habar matni</label>
                    <textarea class="form-control" id="new-chat-message" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" onclick="createChat()" class="btn btn-primary">Chat yaratish va habarni yuborish</button>
            </div>
        </div>
    </div>
</div>

<script>
    const connection = new WebSocket("ws://<?php echo Yii::$app->params['workerSocketName']?>");

    connection.onopen = () => {
        if (!localStorage.key('username')) {
            let username = prompt('Ismingizni kiriting');

            localStorage.setItem('username', username);
        }

        getChats();
    };

    connection.onerror = (error) => {
        console.log(`WebSocket error: ${error}`);
    };

    connection.onclose = () => {
        console.log('disconnected');
    };

    connection.onmessage = (data) => {
        const payload = JSON.parse(data.data);

        switch (payload.method) {
            case 'getChats':
                handleGetChats(payload.data);
                break;
            case 'createChat':
                handleCreateChat(payload.data);
                break;
            case 'getMessages':
                handleGetMessages(payload.data);
                break;
            case 'sendMessage':
                handleSendMessage(payload.data);
                break;
            case 'newMessage':
                handleNewMessage(payload.data);
                break;
        }
    };

    function getChats() {
        connection.send(JSON.stringify({
            method: 'getChats',
            data: {}
        }));
    }

    function handleGetChats(data) {
        const chats = data.chats;

        let chatsList = document.getElementById('chats-list');

        chats.forEach(chat => {
            let chatItem = document.createElement('button');
            chatItem.type = 'button';
            chatItem.className = 'list-group-item list-group-item-action';
            chatItem.innerText = chat.name;
            chatItem.setAttribute('data-id', chat.id);
            chatItem.setAttribute('onclick', 'getMessages(this)');

            chatsList.appendChild(chatItem);
        });
    }

    function createChat() {
        let title = document.getElementById('new-chat-title').value;
        let message = document.getElementById('new-chat-message').value;

        if (title.length === 0 || message.length === 0) {
            alert('Chat nomi va habar matni bo\'sh bo\'lishi mumkin emas!');
            return;
        }

        connection.send(JSON.stringify({
            method: 'createChat',
            data: {
                name: title,
                username: localStorage.getItem('username'),
                text: message
            }
        }));

        document.getElementById('new-chat-title').value = '';
        document.getElementById('new-chat-message').value = '';
    }

    function handleCreateChat(data) {
        let chatItem = document.createElement('button');
        chatItem.type = 'button';
        chatItem.className = 'list-group-item list-group-item-action';
        chatItem.innerText = data.chat.name;
        chatItem.setAttribute('data-id', data.chat.id);
        chatItem.setAttribute('onclick', 'getMessages(this)');

        document.getElementById('chats-list').appendChild(chatItem);
    }

    function getMessages(btn) {
        let chat_id = btn.getAttribute('data-id');

        localStorage.setItem('chat_id', chat_id);

        connection.send(JSON.stringify({
            method: 'getMessages',
            data: {
                chat_id: chat_id
            }
        }));
    }

    function handleGetMessages(data) {
        let messages = document.getElementById('messages');
        messages.innerHTML = '';

        data.messages.forEach(message => {
            messages.innerHTML += buildMessage(message);
            scrollDown();
        });
    }

    function buildMessage(message) {
        if (message.username === localStorage.getItem('username')) {
            return buildRightMessage(message);
        }

        return buildLeftMessage(message);
    }

    function buildLeftMessage(message) {
        return `<li class="d-flex justify-content-start mb-4">
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-6.webp" alt="avatar" class="rounded-circle d-flex align-self-start me-3 shadow-1-strong" width="60">
                <div class="card ms-3">
                    <div class="card-header d-flex justify-content-between p-3">
                        <p class="fw-bold mb-0">${message.username}</p>
                        <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${message.create_time}</p>
                    </div>
                    <div class="card-body" style="min-width: 300px;">
                        <p class="mb-0">${message.text}</p>
                    </div>
                </div>
            </li>`;
    }

    function buildRightMessage(message) {
        return `<li class="d-flex justify-content-end mb-4">
                <div class="card me-3">
                    <div class="card-header d-flex justify-content-between p-3">
                        <p class="fw-bold mb-0">${message.username}</p>
                        <p class="text-muted small mb-0"><i class="far fa-clock"></i> ${message.create_time}</p>
                    </div>
                    <div class="card-body" style="min-width: 300px;">
                        <p class="mb-0">${message.text}</p>
                    </div>
                </div>
                <img src="https://mdbcdn.b-cdn.net/img/Photos/Avatars/avatar-6.webp" alt="avatar" class="rounded-circle d-flex align-self-start me-3 shadow-1-strong" width="60">
            </li>`;
    }

    function sendMessage(btn) {
        let chat_id = localStorage.getItem('chat_id');
        let message = document.getElementById('message-text').value;

        if (message.length <= 0) {
            alert('Habar matni bo\'sh bo\'lishi mumkin emas!');
            return;
        }

        btn.disabled = true;

        connection.send(JSON.stringify({
            method: 'sendMessage',
            data: {
                chat_id: chat_id,
                username: localStorage.getItem('username'),
                text: message
            }
        }));

        document.getElementById('message-text').value = '';
        btn.disabled = false;
    }

    function handleSendMessage(data) {
        let messages = document.getElementById('messages');
        messages.innerHTML += buildRightMessage(data.message);

        scrollDown();
    }

    function handleNewMessage(data) {
        if (data.message.chat_id !== localStorage.getItem('chat_id')) {
            return;
        }

        let messages = document.getElementById('messages');
        messages.innerHTML += buildMessage(data.message);
        scrollDown();
    }

    function scrollDown() {
        console.log($('#messages').height());
        $('#messages-content').scrollTop($('#messages').height());
    }
</script>