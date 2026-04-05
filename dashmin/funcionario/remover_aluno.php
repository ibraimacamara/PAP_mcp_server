<?php
include('../conexao.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Aluno inválido.');
}

$id = (int) $_GET['id'];

try {

    $pdo->beginTransaction();

    // Buscar user_id e foto antes de apagar
    $stmt = $pdo->prepare('SELECT user_id, foto FROM aluno WHERE numero_aluno = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aluno) {
        throw new Exception("Aluno não encontrado.");
    }

    $user_id = $aluno['user_id'];
    $foto = $aluno['foto'];

    // Apagar ficheiro da foto (se existir)
    if (!empty($foto)) {
        $caminhoFoto = "../uploads/" . basename($foto); 
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    // Remover relações com turmas
    $stmt = $pdo->prepare('DELETE FROM aluno_turma WHERE numero_aluno = :id');
    $stmt->execute([':id' => $id]);

    // Remover relações com encarregados
    $stmt = $pdo->prepare('DELETE FROM aluno_encarregado WHERE numero_aluno = :id');
    $stmt->execute([':id' => $id]);

    // Remover relações com curso
    $stmt = $pdo->prepare('DELETE FROM aluno_curso WHERE numero_aluno = :id');
    $stmt->execute([':id' => $id]);

    // Remover aluno
    $stmt = $pdo->prepare('DELETE FROM aluno WHERE numero_aluno = :id');
    $stmt->execute([':id' => $id]);

    // Remover user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
    $stmt->execute([':user_id' => $user_id]);

    $pdo->commit();

    header('Location: lista_aluno.php');
    exit;

} catch (Exception $e) {
    // Reverter tudo em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Erro ao remover aluno: ' . htmlspecialchars($e->getMessage()));
}