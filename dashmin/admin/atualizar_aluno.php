<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_aluno.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_aluno.php');
    exit;
}

$numero_aluno = (int) ($_POST['numero_aluno'] ?? 0);
if ($numero_aluno <= 0) {
    header('Location: lista_aluno.php');
    exit;
}

$campos = ['nome', 'email', 'bi', 'contato', 'data_nascimento', 'morada', 'distrito', 'freguesia', 'genero'];
$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

if (empty($set)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: editar_aluno.php?id=$numero_aluno");
    exit;
}

// Trata upload de nova foto (se enviada)
$userId = (int) ($_POST['user_id'] ?? 0);
$novaFotoNome = null;

if ($userId > 0 && !empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if ($foto['error'] === UPLOAD_ERR_OK) {
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if (isset($tiposPermitidos[$foto['type']])) {
            $uploadDir = 'uploads/';
            if (is_dir($uploadDir)) {
                $extensao = $tiposPermitidos[$foto['type']];
                $novoNome = 'aluno_' . $numero_aluno . '_' . time() . '.' . $extensao;
                $destino = $uploadDir . $novoNome;

                if (move_uploaded_file($foto['tmp_name'], $destino)) {
                    $novaFotoNome = $novoNome;
                }
            }
        }
    }
}

$valores[] = $numero_aluno;
$sqlAluno = "UPDATE aluno SET " . implode(', ', $set) . " WHERE numero_aluno = ?";

try {
    $pdo->beginTransaction();

    // Atualiza dados do aluno
    $stmt = $pdo->prepare($sqlAluno);
    $stmt->execute($valores);

    // Se houver nova foto, atualiza também em users
    if ($novaFotoNome !== null && $userId > 0) {
        $stmtFoto = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmtFoto->execute([$novaFotoNome, $userId]);
    }

    $pdo->commit();

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Aluno atualizado com sucesso.'];
    header("Location: detalhe_aluno.php?id=$numero_aluno");
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: editar_aluno.php?id=$numero_aluno");
}
exit;
