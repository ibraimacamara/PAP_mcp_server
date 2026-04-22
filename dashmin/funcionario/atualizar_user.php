<?php
session_start();
include('../conexao.php');

// CONFIG LOG
define('LOG_FILE', __DIR__ . '/../logs/app.log');

function logError($msg) {
    error_log(date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, 3, LOG_FILE);
}

// VALIDAR MÉTODO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:editar_user.php');
    exit;
}

// CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token inválido.'];
    header('Location: editar_user.php');
    exit;
}

// ID
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'ID inválido.'];
    header('Location: editar_user.php');
    exit;
}


// DADOS
$username = trim($_POST['username'] ?? '');
$senha    = trim($_POST['senha'] ?? '');
$confirm  = trim($_POST['confirmar_senha'] ?? '');

// VALIDAÇÃO
if (empty($username)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'nome de utilizador é obrigatório.'];
    header("Location: editar_user.php?id=$id");
    exit;
}

// PASSWORD
$senhaHash = null;

if (!empty($senha)) {

    if ($senha !== $confirm) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Senhas não coincidem.'];
        header("Location: editar_user.php?id=$id");
        exit;
    }

    if (strlen($senha) < 6) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Senha muito curta.'];
        header("Location: editar_user.php?id=$id");
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
}


// UPLOAD IMAGEM
$imagem = null;

if (!empty($_FILES['imagem']['name'])) {

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Imagem inválida.'];
        header("Location: editar_user.php?id=$id");
        exit;
    }

    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $id . '_' . time() . '.' . $ext;

    $destDir = __DIR__ . '../uploads/';
    $destPath = $destDir . $filename;

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destPath)) {
        logError("Erro upload imagem user ID: $id");

        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro no upload.'];
        header("Location: editar_user.php?id=$id");
        exit;
    }

    $imagem = $filename;
}


// UPDATE
try {

    $campos = ["username = ?"];
    $params = [$username];

    if ($senhaHash) {
        $campos[] = "senha = ?";
        $params[] = $senhaHash;
    }

    if ($imagem) {
        $campos[] = "foto = ?";
        $params[] = $imagem;
    }

    $params[] = $id;

    $sql = "UPDATE users SET " . implode(', ', $campos) . " WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg' => 'Utilizador atualizado com sucesso.'
    ];

    header('Location: index.php');

} catch (Exception $e) {

    logError("Erro update user ID $id: " . $e->getMessage());

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar utilizador.'
    ];

    header("Location: editar_user.php?id=$id");
}

exit;