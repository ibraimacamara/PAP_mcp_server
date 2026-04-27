<?php
include('../conexao.php');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die('Professor inválido.');
}

$id = (int) $_POST['id'];

try {
    $pdo->beginTransaction();

    // Buscar user_id do professor e foto do utilizador
    $stmt = $pdo->prepare("
        SELECT p.user_id, u.foto
        FROM professor p
        LEFT JOIN users u ON u.id = p.user_id
        WHERE p.id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professor) {
        $pdo->rollBack();
        die('Professor não encontrado.');
    }

    $user_id = $professor['user_id'] ?? null;
    $foto    = $professor['foto'] ?? null;

    // Apagar foto da pasta uploads
    if (!empty($foto)) {
        $caminhoFoto = '../uploads/' . basename($foto);

        if (file_exists($caminhoFoto) && is_file($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    // Apagar professor
    $stmt = $pdo->prepare('DELETE FROM professor WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Apagar utilizador associado
    if (!empty($user_id)) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $pdo->commit();

    header('Location: index.php?page=lista_professor');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Erro ao remover professor: ' . htmlspecialchars($e->getMessage()));
}