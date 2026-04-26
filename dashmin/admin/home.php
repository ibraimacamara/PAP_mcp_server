<?php
declare(strict_types=1);

include '../conexao.php';
include 'menu.php';
include 'nav-menu.php';

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DA SESSÃO
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    die('Utilizador não autenticado.');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$categoria = (string) ($_SESSION['categoria'] ?? '');
$username = (string) ($_SESSION['username'] ?? '');
$foto = (string) ($_SESSION['foto'] ?? 'default.jpg');

if ($userId <= 0) {
    die('Sessão inválida: user_id não encontrado.');
}
?>

<style>
    .chat-page {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 60px);
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: transparent;
        padding-bottom: 180px;
    }

    .msg-bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 16px;
        line-height: 1.5;
        word-wrap: break-word;
        white-space: pre-wrap;
    }

    .msg-user {
        align-self: flex-end;
        background: #0d6efd;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .msg-agent {
        align-self: flex-start;
        background: #ffffff;
        color: #333;
        border: 1px solid #e0e0e0;
        border-bottom-left-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .msg-agent .agent-label {
        font-size: 11px;
        color: #888;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .message-images {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .msg-user.has-image {
        background: transparent;
        padding: 0;
    }

    .message-images img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 12px;
        object-fit: cover;
        display: block;

        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .typing-indicator span {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #aaa;
        border-radius: 50%;
        margin: 0 2px;
        animation: bounce 1.2s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes bounce {

        0%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-6px);
        }
    }

    .chat-input-area {
        padding: 12px 16px;
        background: transparent;
        position: fixed;
        bottom: 0;
        left: 30%;
        width: 60%;
        z-index: 1000;
    }

    .chat-input-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
        background: #f1f3f5;
        border-radius: 12px;
        padding: 10px 12px;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .file-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .preview-item {
        position: relative;
        width: 70px;
        height: 70px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #ddd;
        background: #fff;
        flex-shrink: 0;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .preview-remove {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 20px;
        height: 20px;
        border: none;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 12px;
        cursor: pointer;
        line-height: 20px;
        text-align: center;
        padding: 0;
    }

    .chat-input-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .chat-input-box textarea {
        flex: 1;
        border: none;
        background: transparent;
        resize: none;
        outline: none;
        font-size: 15px;
        max-height: 150px;
        overflow-y: auto;
        line-height: 1.4;
    }

    .chat-input-box button {
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
    }

    .chat-input-box button:disabled {
        background: #aaa !important;
        cursor: not-allowed;
    }

    #sendBtn {
        background: #0d6efd;
        color: #fff;
    }

    #attachBtn {
        background: #fff;
        color: #333;
        border: 1px solid #ddd;
    }

    .chat-welcome {
        text-align: center;
        color: #aaa;
        margin: auto;
        padding: 40px 20px;
    }

    .chat-welcome i {
        font-size: 48px;
        margin-bottom: 12px;
        display: block;
    }
</style>

<div class="chat-page">
    <div class="chat-messages" id="chatMessages">
        <div class="chat-welcome" id="welcomeMsg">
            <i class="fa fa-robot text-primary"></i>
            <h5>Assistente SGE-ECP</h5>
            <p>Olá! Posso consultar alunos, encarregados, turmas, professores e cursos.<br>Como posso ajudar?</p>
        </div>
    </div>

    <div class="chat-input-area">
        <div class="chat-input-box">
            <div id="filePreview" class="file-preview"></div>

            <div class="chat-input-row">
                <button type="button" id="attachBtn" title="Anexar imagem">
                    <i class="fa fa-plus"></i>
                </button>

                <input type="file" id="fileInput" accept="image/*" multiple hidden>

                <textarea id="userInput" rows="1" placeholder="Faça uma pergunta..."></textarea>

                <button id="sendBtn" type="button" title="Enviar">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>

        <div class="text-center mt-1" style="font-size:11px;color:#aaa;">
            Assistente IA — pode cometer erros. Verifique informações importantes.
        </div>
    </div>
</div>

<script>
    const N8N_WEBHOOK = 'http://localhost:5678/webhook/ibra-chat-webhook/chat';

    const authUser = {
        user_id: <?= json_encode($userId) ?>,
        categoria: <?= json_encode($categoria) ?>,
        username: <?= json_encode($username) ?>,
        foto: <?= json_encode($foto) ?>
    };

    const sessionId = 'user-' + authUser.user_id;

    const textarea = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const messages = document.getElementById('chatMessages');
    const attachBtn = document.getElementById('attachBtn');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');

    let selectedFiles = [];

    /* =========================
       AUTO RESIZE TEXTAREA
    ========================= */
    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });

    /* =========================
       ENTER PARA ENVIAR
    ========================= */
    textarea.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    /* =========================
       BOTÕES
    ========================= */
    sendBtn.addEventListener('click', sendMessage);

    attachBtn.addEventListener('click', () => {
        fileInput.click();
    });

    /* =========================
       PREVIEW DE IMAGENS
    ========================= */
    function renderPreview() {
        filePreview.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'preview-item';

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'preview-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.title = 'Remover imagem';

            removeBtn.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                renderPreview();
            });

            item.appendChild(img);
            item.appendChild(removeBtn);
            filePreview.appendChild(item);
        });
    }

    /* =========================
       SELECIONAR FICHEIROS
    ========================= */
    fileInput.addEventListener('change', () => {
        const files = Array.from(fileInput.files);

        files.forEach((file) => {
            if (!file.type.startsWith('image/')) {
                appendMessage('Apenas imagens são permitidas.', 'agent');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                appendMessage('Imagem muito grande (máx. 2 MB).', 'agent');
                return;
            }

            selectedFiles.push(file);
        });

        renderPreview();
        fileInput.value = '';
    });

    /* =========================
       MOSTRAR MENSAGEM
    ========================= */
    function appendMessage(text, role, files = []) {
        document.getElementById('welcomeMsg')?.remove();

        const wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexDirection = 'column';

        const bubble = document.createElement('div');
        bubble.classList.add('msg-bubble', role === 'user' ? 'msg-user' : 'msg-agent');

        if (role === 'agent') {
            const label = document.createElement('div');
            label.className = 'agent-label';
            label.innerHTML = '<i class="fa fa-robot"></i> Assistente SGE-ECP';
            bubble.appendChild(label);
        }

        if (text) {
            const content = document.createElement('div');
            content.textContent = text;
            bubble.appendChild(content);
        }

        if (files.length > 0) {
            const imagesWrap = document.createElement('div');
            imagesWrap.className = 'message-images';

            files.forEach((file) => {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    imagesWrap.appendChild(img);
                }
            });

            bubble.appendChild(imagesWrap);
        }

        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    /* =========================
       TYPING INDICATOR
    ========================= */
    function showTyping() {
        removeTyping();

        const bubble = document.createElement('div');
        bubble.classList.add('msg-bubble', 'msg-agent');
        bubble.id = 'typingIndicator';
        bubble.innerHTML =
            '<div class="agent-label"><i class="fa fa-robot"></i> Assistente SGE-ECP</div>' +
            '<div class="typing-indicator"><span></span><span></span><span></span></div>';

        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTyping() {
        document.getElementById('typingIndicator')?.remove();
    }

    /* =========================
       ENVIAR MENSAGEM
    ========================= */
    async function sendMessage() {
        const text = textarea.value.trim();

        if (!text && selectedFiles.length === 0) {
            return;
        }

        const filesToSend = [...selectedFiles];

        appendMessage(text, 'user', filesToSend);

        textarea.value = '';
        textarea.style.height = 'auto';
        sendBtn.disabled = true;
        attachBtn.disabled = true;

        showTyping();

        try {
            const formData = new FormData();
            formData.append('chatInput', text);
            formData.append('sessionId', sessionId);
            formData.append('user_id', String(authUser.user_id));
            formData.append('categoria', authUser.categoria);
            formData.append('username', authUser.username);
            formData.append('foto', authUser.foto);

            filesToSend.forEach((file) => {
                formData.append('files[]', file, file.name);
            });

            const res = await fetch(N8N_WEBHOOK, {
                method: 'POST',
                body: formData
            });

            removeTyping();

            if (!res.ok) {
                throw new Error('Erro HTTP ' + res.status);
            }

            const contentType = res.headers.get('content-type') || '';

            let data;
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const textResponse = await res.text();
                data = { message: textResponse };
            }

            const reply =
                data.output ??
                data.text ??
                data.message ??
                data.response ??
                'Sem resposta do assistente.';

            appendMessage(reply, 'agent');
        } catch (err) {
            removeTyping();
            appendMessage('Erro ao contactar o assistente.', 'agent');
            console.error(err);
        } finally {
            sendBtn.disabled = false;
            attachBtn.disabled = false;
            textarea.focus();
            selectedFiles = [];
            renderPreview();
        }
    }
</script>

<?php include 'footer.php'; ?>

</div>

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/chart/chart.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="js/main.js"></script>
</body>

</html>