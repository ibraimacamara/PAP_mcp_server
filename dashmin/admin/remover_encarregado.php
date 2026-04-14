<?php
include('../conexao.php');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die('Encarregado inválido.');
}

$id = (int) $_POST['id'];

try {
    // Buscar o user_id do encarregado
    $stmt = $pdo->prepare('SELECT user_id FROM encarregado WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $encarregado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$encarregado) {
        die('Encarregado não encontrado.');
    }

    $user_id = $encarregado['user_id'];

    // Remover o encarregado
    $stmt = $pdo->prepare('DELETE FROM encarregado WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Remover o utilizador associado, se existir
    if (!empty($user_id)) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    header('Location: lista_encarregado.php');
    exit;
} catch (PDOException $e) {
    die('Erro ao remover encarregado: ' . htmlspecialchars($e->getMessage()));
}