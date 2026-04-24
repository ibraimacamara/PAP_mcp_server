<?php
declare(strict_types=1);

session_start();
require_once 'conexao.php';

define('LOG_FILE', __DIR__ . 'logs/app.log');


// FUNÇÕES

function logErro(string $msg): void
{
    error_log("[" . date('Y-m-d H:i:s') . "] $msg\n", 3, LOG_FILE);
}

function redirectComMensagem(string $msg, string $tipo = 'danger'): void
{
    $_SESSION['alerta_login'] = [
        'tipo' => $tipo,
        'msg' => $msg
    ];
    header("Location: login.php");
    exit;
}

function validarCSRF(): void
{
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        redirectComMensagem('Sessão expirada. Tente novamente.');
    }
}

// VALIDAÇÃO

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectComMensagem('Acesso inválido.');
}

validarCSRF();

// INPUT

$username = filter_input(INPUT_POST, 'username');
$senha = $_POST['senha'] ?? '';

if (!$username || empty($senha)) {
    redirectComMensagem('Preencha corretamente os campos.');
}


//   LOGIN


try {

    $stmt = $pdo->prepare("
        SELECT id, username, senha, categoria, foto, primeiro_login
        FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$user || !password_verify($senha, $user['senha'])) {
        redirectComMensagem('Credenciais inválidas.');
    }

    //  PRIMEIRO LOGIN - REDIRECIONAMENTO

    $isPrimeiroLogin = ((int) $user['primeiro_login'] === 0);

    //incrementar contador de login
    $stmtUpdate = $pdo->prepare("
    UPDATE users
    SET primeiro_login = primeiro_login +1  
    WHERE id = :id");
    $stmtUpdate->execute(['id' => $user['id']]);

    if ($isPrimeiroLogin) {

        session_regenerate_id(true);

        $_SESSION['auth'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['categoria'] = $user['categoria'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['foto'] = $user['foto'] ?? 'default.jpg';

        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];

        $_SESSION['forcar_edicao'] = true;

        unset($_SESSION['csrf_token']);

        $rotaEditar = [
            'admin' => 'admin/editar_user.php',
            'aluno' => 'aluno/editar_user.php',
            'professor' => 'prof/editar_user.php',
            'funcionario' => 'funcionario/editar_user.php',
            'encarregado' => 'encarregado/editar_user.php'

        ];

        $categoria = strtolower(trim($user['categoria']));

        if (!isset($rotaEditar[$categoria])) {
            logErro('Categoria Inválida' . $categoria);
            session_destroy();
            redirectComMensagem('Erro interno.');
        }

        header("Location: " . $rotaEditar[$categoria] . "?id=" . $user['id']);
        exit;
    }


    //   LOGIN OK

    session_regenerate_id(true);

    $_SESSION['auth'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['categoria'] = $user['categoria'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['foto'] = $user['foto'] ?? 'default.jpg';

    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT'];

    unset($_SESSION['csrf_token']);


    // REDIRECIONAMENTO POR CATEGORIA

    $rotas = [
        'admin' => 'admin/index.php',
        'aluno' => 'aluno/index.php',
        'professor' => 'prof/index.php',
        'funcionario' => 'funcionario/index.php',
        'encarregado' => 'encarregado/index.php'
    ];

    $categoria = strtolower(trim($user['categoria']));

    if (!isset($rotas[$categoria])) {
        logErro("Categoria inválida: " . $categoria);
        session_destroy();
        redirectComMensagem('Erro interno.');
    }

    header("Location: " . $rotas[$categoria]);
    exit;

} catch (Throwable $e) {
    logErro("LOGIN ERROR: " . $e->getMessage());
    redirectComMensagem('Erro interno. Tente novamente.');
}