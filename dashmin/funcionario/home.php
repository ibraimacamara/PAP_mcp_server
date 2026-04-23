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
        padding-bottom: 120px;
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
        0%, 80%, 100% {
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
        align-items: flex-end;
        gap: 8px;
        background: #f1f3f5;
        border-radius: 12px;
        padding: 8px 12px;
        max-width: 800px;
        margin: 0 auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        background: #0d6efd;
        color: #fff;
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
        background: #aaa;
        cursor: not-allowed;
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

    #attachBtn {
        border: none;
        background: #fff;
        color: #333;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        border: 1px solid #ddd;
    }
</style>

<div class="chat-page">
    <div class="chat-messages" id="chatMessages">
        <div class="chat-welcome" id="welcomeMsg">
            <i class="fa fa-robot text-primary"></i>
            <h5>Assistente SGE-ECP</h5>
            <p>
                Olá, <strong><?= htmlspecialchars($username) ?></strong>.<br>
                Posso ajudar com consultas no sistema.<br>
                Também posso mostrar os seus próprios dados quando pedir, por exemplo:
                <br><em>"lista os meus dados"</em>
            </p>
        </div>
    </div>

    <div class="chat-input-area">
        <div class="chat-input-box">
            <button type="button" id="attachBtn" title="Anexar">
                <i class="fa fa-plus"></i>
            </button>

            <input type="file" id="fileInput" multiple hidden>

            <textarea id="userInput" rows="1" placeholder="Faça uma pergunta..."></textarea>

            <button id="sendBtn" type="button" title="Enviar">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>

        <div class="text-center mt-1" style="font-size:11px;color:#aaa;">
            Assistente IA — pode cometer erros. Verifique informações importantes.
        </div>
    </div>
</div>

<script>
const N8N_WEBHOOK = 'http://localhost:5678/webhook/e978382d-df6e-46da-b67d-a6982f6ae11b/chat';

const authUser = {
    user_id: <?= json_encode($userId) ?>,
    categoria: <?= json_encode($categoria) ?>,
    username: <?= json_encode($username) ?>,
    foto: <?= json_encode($foto) ?>
};

// ID da conversa associado ao utilizador autenticado
const sessionId = 'user-' + authUser.user_id;

const textarea = document.getElementById('userInput');
const sendBtn = document.getElementById('sendBtn');
const messages = document.getElementById('chatMessages');
const attachBtn = document.getElementById('attachBtn');
const fileInput = document.getElementById('fileInput');

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
textarea.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

/* =========================
   BOTÃO ENVIAR
========================= */
sendBtn.addEventListener('click', sendMessage);

/* =========================
   BOTÃO ANEXAR
========================= */
attachBtn.addEventListener('click', () => {
    fileInput.click();
});

/* =========================
   SELECIONAR FICHEIROS
========================= */
fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files);

    files.forEach(file => {
        if (!file.type.startsWith('image/')) {
            appendMessage('Apenas imagens são permitidas.', 'agent');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            appendMessage('Imagem muito grande (máx 2MB).', 'agent');
            return;
        }

        selectedFiles.push(file);
    });

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

    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '200px';
            img.style.borderRadius = '10px';
            img.style.marginTop = '6px';
            bubble.appendChild(img);
        }
    });

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

    appendMessage(text, 'user', selectedFiles);

    textarea.value = '';
    textarea.style.height = 'auto';
    sendBtn.disabled = true;

    showTyping();

    try {
        const formData = new FormData();
        formData.append('chatInput', text);
        formData.append('sessionId', sessionId);

        // dados do utilizador autenticado vindos da sessão PHP
        formData.append('user_id', String(authUser.user_id));
        formData.append('categoria', authUser.categoria);
        formData.append('username', authUser.username);
        formData.append('foto', authUser.foto);

        selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        const res = await fetch(N8N_WEBHOOK, {
            method: 'POST',
            body: formData
        });

        removeTyping();

        if (!res.ok) {
            throw new Error('Erro HTTP ' + res.status);
        }

        const data = await res.json();

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
        textarea.focus();
        selectedFiles = [];
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