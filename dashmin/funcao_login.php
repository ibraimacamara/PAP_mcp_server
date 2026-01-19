<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
session_start();

require_once 'conexao.php';


/* =====================================================
   LOG E ERRO
===================================================== */
define('LOG_FILE', __DIR__ . '/../logs/app.log');

function logErro(string $mensagem): void
{
    $data = date('Y-m-d H:i:s');
    error_log("[$data] $mensagem\n", 3, LOG_FILE);
}

function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => $mensagem];
    header('Location: index.php');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    header('Location: index.php');
    exit;
}

/* =====================================================
   SEGURANÇA
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido no login');
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    erroUtilizador('Sessão expirada. Tente novamente.');
}

/* =====================================================
   RECEBER DADOS
===================================================== */
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

/* =====================================================
   VALIDAÇÃO
===================================================== */
if (!$email || !$senha) {
    erroUtilizador('Preencha o email e a senha.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    erroUtilizador('Email inválido.');
}

/* =====================================================
   LOGIN
===================================================== */
try {

    $stmt = $pdo->prepare("
        SELECT id, email, senha, categoria, status
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        erroUtilizador('Credenciais inválidas.');
    }


    if ($user['status'] !== 'Ativo') {
        erroUtilizador('Conta inativa. Contacte a administração.');
    }


    if (!password_verify($senha, $user['senha'])) {
        erroUtilizador('Credenciais inválidas.');
    }


    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'        => $user['id'],
        'email'     => $user['email'],
        'categoria' => $user['categoria']
    ];

    unset($_SESSION['csrf_token']);


 
    switch ($user['categoria']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;

        case 'aluno':
            header('Location: home.php');
            break;

        case 'professor':
            header('Location: professor/dashboard.php');
            break;

        default:
            logErro('Categoria inválida para user ID ' . $user['id']);
            session_destroy();
            erroTecnico('Categoria inválida');
    }

    exit;

} catch (PDOException $e) {
    erroTecnico('Erro BD LOGIN: ' . $e->getMessage());
}
