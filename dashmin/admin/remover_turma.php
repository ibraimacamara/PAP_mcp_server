<?php
include('../conexao.php');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: index.php?page=lista_turma');
    exit;
}

$id = (int) $_POST['id'];

try {
    $stmt = $pdo->prepare('DELETE FROM turma WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: index.php?page=lista_turma');
    exit;
} catch (PDOException $e) {
    echo 'Erro ao remover turma: ' . htmlspecialchars($e->getMessage());
    exit;
}