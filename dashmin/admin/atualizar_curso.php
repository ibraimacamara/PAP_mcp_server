<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_curso.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_curso.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: lista_curso.php');
    exit;
}

$nome     = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$imagem   = trim($_POST['imagem_atual'] ?? '');

if (empty($nome)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'O nome do curso é obrigatório.'];
    header("Location: editar_curso.php?id=$id");
    exit;
}

// Handle image upload if a new file was provided
if (!empty($_FILES['imagem']['name'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['imagem']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Tipo de ficheiro não permitido.'];
        header("Location: editar_curso.php?id=$id");
        exit;
    }

    $ext      = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $filename = 'curso_' . $id . '_' . time() . '.' . $ext;
    $destDir  = '../uploads_curso/';

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destDir . $filename)) {
        $imagem = $filename;
    } else {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao fazer upload da imagem.'];
        header("Location: editar_curso.php?id=$id");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("UPDATE curso SET nome = ?, descricao = ?, imagem = ? WHERE id = ?");
    $stmt->execute([$nome, $descricao, $imagem, $id]);
    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Curso atualizado com sucesso.'];
    header('Location: lista_curso.php');
} catch (Exception $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: editar_curso.php?id=$id");
}
exit;
