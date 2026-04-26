<?php

declare(strict_types=1);
include "../conexao.php";

// Só inicia a sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o utilizador está logado
if (empty($_SESSION['auth']) || !$_SESSION['auth']) {
    header('Location: ../login.php');
    exit;
}

// Verifica se é ADMIN
if (!isset($_SESSION['categoria']) || $_SESSION['categoria'] !== 'admin') {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Pega os dados do utilizador da sessão
$nomeUser = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'Sem nome';
$categoriaUser = $_SESSION['categoria'] ?? 'Sem categoria';
$fotoUser = $_SESSION['foto'] ?? 'default.jpg';

// Ajusta o caminho da foto
$fotoPath = file_exists(__DIR__ . '/../uploads/' . $fotoUser)
    ? '../uploads/' . $fotoUser
    : '../uploads/default.jpg';

// Função para marcar link ativo
function isActive(string $page): string
{
    $currentPage = $_GET['page'] ?? 'home';
    return $currentPage === $page ? 'active' : '';
}
?>
<style>
    #chatWidget {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 360px;
        height: 500px;
        z-index: 9999;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
        flex-direction: column;
        overflow: hidden;
    }

    #chatWidget.open {
        display: flex;
    }

    #chatWidget .cw-header {
        background: #0d6efd;
        color: #fff;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        font-size: 15px;
    }

    #chatWidget .cw-header button {
        background: none;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        line-height: 1;
    }

    #chatWidget .cw-messages {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    #chatWidget .cw-bubble {
        max-width: 85%;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 14px;
        line-height: 1.45;
        word-wrap: break-word;
        white-space: pre-wrap;
    }

    #chatWidget .cw-user {
        align-self: flex-end;
        background: #0d6efd;
        color: #fff;
        border-bottom-right-radius: 3px;
    }

    #chatWidget .cw-agent {
        align-self: flex-start;
        background: #fff;
        border: 1px solid #dee2e6;
        border-bottom-left-radius: 3px;
    }

    #chatWidget .cw-input-row {
        display: flex;
        gap: 8px;
        padding: 10px 12px;
        border-top: 1px solid #dee2e6;
        background: #fff;
    }

    #chatWidget .cw-input-row textarea {
        flex: 1;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 14px;
        resize: none;
        outline: none;
        max-height: 80px;
        overflow-y: auto;
    }

    #chatWidget .cw-input-row button {
        border: none;
        background: #0d6efd;
        color: #fff;
        border-radius: 8px;
        padding: 0 12px;
        cursor: pointer;
        font-size: 16px;
        flex-shrink: 0;
    }

    #chatWidget .cw-input-row button:disabled {
        background: #aaa;
    }

    .cw-typing span {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #aaa;
        border-radius: 50%;
        margin: 0 2px;
        animation: cwbounce 1.2s infinite;
    }

    .cw-typing span:nth-child(2) {
        animation-delay: .2s;
    }

    .cw-typing span:nth-child(3) {
        animation-delay: .4s;
    }

    @keyframes cwbounce {

        0%,
        80%,
        100% {
            transform: translateY(0)
        }

        40% {
            transform: translateY(-5px)
        }
    }
</style>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8">
    <title>SGEI-ECP</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link href="img/favicon.ico" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<style>
    .sidebar {
        overflow-y: auto !important;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .sidebar::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
</style>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar white navbar-light">
                <a href="index.php?page=home" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-university me-2"></i>SGE-ECP</h3>
                </a>

                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto do utilizador"
                            style="width: 55px; height: 53px;">
                        <div
                            class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
                        </div>
                    </div>
                    <div class="ms-3">
                        <h4><?= htmlspecialchars($categoriaUser) ?></h4>
                    </div>
                </div>

                <div class="navbar-nav w-100">
                    <a href="index.php?page=home" class="nav-item nav-link <?= isActive('home'); ?>">
                        <i class="fa fa-tachometer-alt me-2"></i>Painel
                    </a>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="fa fa-graduation-cap me-2"></i>Aluno
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_aluno"
                                class="dropdown-item ps-5 <?= isActive('form_aluno') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_aluno"
                                class="dropdown-item ps-5 <?= isActive('lista_aluno') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="fa fa-user-tie me-2"></i>Encarregado
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_encarregado"
                                class="dropdown-item ps-5 <?= isActive('form_encarregado') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_encarregado"
                                class="dropdown-item ps-5 <?= isActive('lista_encarregado') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="fa fa-chalkboard-teacher me-2"></i>Professor
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_professor"
                                class="dropdown-item ps-5 <?= isActive('form_professor') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_professor"
                                class="dropdown-item ps-5 <?= isActive('lista_professor') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="bi bi-book me-2"></i>Curso
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_curso"
                                class="dropdown-item ps-5 <?= isActive('form_curso') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_curso"
                                class="dropdown-item ps-5 <?= isActive('lista_curso') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="bi bi-people me-2"></i>Turma
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_turma"
                                class="dropdown-item ps-5 <?= isActive('form_turma') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_turma"
                                class="dropdown-item ps-5 <?= isActive('lista_turma') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>

                    <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="bi bi-people me-2"></i>Modulo
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_modulo"
                                class="dropdown-item ps-5 <?= isActive('form_modulo') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_modulo"
                                class="dropdown-item ps-5 <?= isActive('listar_modulo') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div> -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link" data-bs-toggle="dropdown">
                            <i class="fa fa-user-tie me-2"></i>Funcionário
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="index.php?page=form_funcionario"
                                class="dropdown-item ps-5 <?= isActive('form_funcionario') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-user-plus me-2 text-primary"></i>Registar
                            </a>
                            <a href="index.php?page=lista_funcionario"
                                class="dropdown-item ps-5 <?= isActive('lista_funcionario') ? 'active bg-light text-dark shadow' : ''; ?>">
                                <i class="fa fa-book-open me-2 text-primary"></i>Listar
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-white ibu navbar-light sticky-top px-4 py-0">
                <a href="index.php?page=home" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>

                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>

                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="pesquisa">
                </form>

                <a href="#" class="sidebar-toggler flex-shrink-0 ms-2">
                    <i class="fa fa-search"></i>
                </a>

                <button class="btn btn-sm btn-primary ms-2" onclick="toggleChatWidget()" title="Assistente IA">
                    <i class="fa fa-robot me-1"></i> IA
                </button>

                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle" src="<?= htmlspecialchars($fotoPath) ?>"
                                alt="Foto do utilizador" style="width: 53px; height: 50px;">

                            <span class="d-none d-lg-inline-flex">
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="index.php?page=editar_user&id=<?= (int) $_SESSION['user_id'] ?>"
                                class="dropdown-item">
                                Meu perfil
                            </a>
                            <a href="#" class="dropdown-item">Configurações</a>
                            <a href="index.php?page=logout" class="dropdown-item">Sair</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Chat Widget Popup -->


            <div id="chatWidget">
                <div class="cw-header">
                    <span><i class="fa fa-robot me-2"></i>Assistente SGE-LMS</span>
                    <button onclick="toggleChatWidget()" title="Fechar">✕</button>
                </div>
                <div class="cw-messages" id="cwMessages">
                    <div class="cw-bubble cw-agent">Olá! Como posso ajudar?</div>
                </div>
                <div class="cw-input-row">
                    <textarea id="cwInput" rows="1" placeholder="Escreve aqui..."></textarea>
                    <button id="cwSendBtn" onclick="cwSend()" title="Enviar">➤</button>
                </div>
            </div>

            <script>
                const CW_WEBHOOK = 'http://localhost:5678/webhook/ibra-chat-webhook/chat';
                const cwSessionId = 'widget-' + Math.random().toString(36).slice(2, 9);

                function toggleChatWidget() {
                    const w = document.getElementById('chatWidget');
                    w.classList.toggle('open');
                    if (w.classList.contains('open')) document.getElementById('cwInput').focus();
                }

                const cwInput = document.getElementById('cwInput');

                cwInput.addEventListener('input', () => {
                    cwInput.style.height = 'auto';
                    cwInput.style.height = cwInput.scrollHeight + 'px';
                });

                cwInput.addEventListener('keydown', e => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        cwSend();
                    }
                });

                function cwAppend(text, role) {
                    const msgs = document.getElementById('cwMessages');
                    const b = document.createElement('div');
                    b.className = 'cw-bubble ' + (role === 'user' ? 'cw-user' : 'cw-agent');
                    b.textContent = text;
                    msgs.appendChild(b);
                    msgs.scrollTop = msgs.scrollHeight;
                    return b;
                }

                function cwTyping() {
                    const msgs = document.getElementById('cwMessages');
                    const b = document.createElement('div');
                    b.className = 'cw-bubble cw-agent cw-typing';
                    b.id = 'cwTyping';
                    b.innerHTML = '<span></span><span></span><span></span>';
                    msgs.appendChild(b);
                    msgs.scrollTop = msgs.scrollHeight;
                }

                async function cwSend() {
                    const text = cwInput.value.trim();
                    if (!text) return;

                    cwAppend(text, 'user');
                    cwInput.value = '';
                    cwInput.style.height = 'auto';
                    document.getElementById('cwSendBtn').disabled = true;
                    cwTyping();

                    try {
                        const res = await fetch(CW_WEBHOOK, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ chatInput: text, sessionId: cwSessionId })
                        });

                        if (!res.ok) throw new Error('HTTP ' + res.status);

                        const data = await res.json();
                        document.getElementById('cwTyping')?.remove();

                        const reply = data.output ?? data.text ?? data.message ?? data.response ?? JSON.stringify(data);
                        cwAppend(reply, 'agent');
                    } catch (err) {
                        document.getElementById('cwTyping')?.remove();
                        cwAppend('Erro ao contactar o assistente.', 'agent');
                    }

                    document.getElementById('cwSendBtn').disabled = false;
                }
            </script>