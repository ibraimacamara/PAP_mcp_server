<?php
include('../conexao.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die('Aluno inválido.');
}

try {
    $pdo->beginTransaction();

    // Buscar user_id e foto
    $stmt = $pdo->prepare("
        SELECT a.user_id, u.foto
        FROM aluno a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE a.numero_aluno = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);

    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        throw new Exception("Aluno não encontrado.");
    }

    $user_id = $aluno['user_id'];
    $foto = $aluno['foto'] ?? null;

    // Apagar aluno
    $stmt = $pdo->prepare('DELETE FROM aluno WHERE numero_aluno = :id');
    $stmt->execute([':id' => $id]);

    // Apagar user (se existir)
    if (!empty($user_id)) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
        $stmt->execute([':user_id' => $user_id]);
    }

    $pdo->commit();

    // Apagar ficheiro depois do commit
    if (!empty($foto)) {
        $caminhoFoto = "../uploads/" . basename($foto);
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    header('Location: lista_aluno.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Erro: ' . htmlspecialchars($e->getMessage()));
}