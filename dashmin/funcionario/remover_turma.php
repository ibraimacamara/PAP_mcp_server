<?php
include('../conexao.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: lista_turma.php');
    exit;
}

$id = (int) $_GET['id'];

try {
    // Remove relações aluno_turma primeiro
    $stmt = $pdo->prepare('DELETE FROM aluno_turma WHERE turma_id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Depois remove a turma
    $stmt = $pdo->prepare('DELETE FROM turma WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: lista_turma.php');
    exit;
} catch (PDOException $e) {
    echo 'Erro ao remover turma: ' . htmlspecialchars($e->getMessage());
    exit;
}

