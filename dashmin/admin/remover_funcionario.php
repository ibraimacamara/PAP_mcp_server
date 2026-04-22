<?php
include('../conexao.php');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die('Funcionário inválido.');
}

$id = (int) $_POST['id'];

try {
    // Buscar user_id e foto antes de apagar
    $stmt = $pdo->prepare("
        SELECT 
            f.user_id,
            u.foto
        FROM funcionario f
        LEFT JOIN users u ON u.id = f.user_id
        WHERE f.id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        throw new Exception("Funcionário não encontrado.");
    }

    $user_id = $funcionario['user_id'];
    $foto = $funcionario['foto'];

    // Apagar foto, se existir
    if (!empty($foto)) {
        $caminhoFoto = "../uploads/" . basename($foto);
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    // Apagar primeiro o funcionário
    $stmt = $pdo->prepare('DELETE FROM funcionario WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Depois apagar o utilizador associado
    if (!empty($user_id)) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    header('Location: lista_funcionario.php');
    exit;

} catch (Exception $e) {
    die('Erro ao remover funcionario: ' . htmlspecialchars($e->getMessage()));
}