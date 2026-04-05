<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: editar_user.php');
    exit;
}

// Validação CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_curso.php');
    exit;
}

// ID
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: lista_curso.php');
    exit;
}

// Dados
$nome          = trim($_POST['nome'] ?? '');
$descricao     = trim($_POST['descricao'] ?? '');
$imagem        = trim($_POST['imagem_atual'] ?? '');
$imagem_antiga = $imagem; // guardar referência da antiga
$coordenadorId = (int) ($_POST['coordenador_id'] ?? 0);

// Validação
if (empty($nome)) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'O nome do curso é obrigatório.'];
    header("Location: editar_curso.php?id=$id");
    exit;
}

// Upload de nova imagem
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

    $destDir  = 'uploads/';
    $destPath = $destDir . $filename;

    // Criar pasta se não existir
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    // Upload
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destPath)) {

        $imagem = $filename;

        //  Apagar imagem antiga (se existir e não for default)
        if (!empty($imagem_antiga) && $imagem_antiga !== 'default.png') {
            $oldPath = $destDir . $imagem_antiga;

            if (file_exists($oldPath) && is_file($oldPath)) {
                unlink($oldPath);
            }
        }

    } else {
        $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao fazer upload da imagem.'];
        header("Location: editar_curso.php?id=$id");
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        UPDATE curso 
        SET nome = ?, descricao = ?, coordenador = ?, imagem = ? 
        WHERE id = ?
    ");

    $stmt->execute([
        $nome,
        $descricao,
        $coordenadorId > 0 ? $coordenadorId : null,
        $imagem,
        $id
    ]);

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Curso atualizado com sucesso.'];
    header('Location: lista_curso.php');

} catch (Exception $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar: ' . $e->getMessage()
    ];
    header("Location: editar_curso.php?id=$id");
}

exit;