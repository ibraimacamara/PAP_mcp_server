<?php
include('../conexao.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Aluno inválido.');
}

$id = (int) $_GET['id'];

try {

    // Buscar user_id e foto antes de apagar
    $stmt = $pdo->prepare('SELECT user_id, foto FROM funcionario WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$funcionario) {
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

    // pegar id de users na tabela funcionario
    $stmt = $pdo->prepare('SELECT  user_id FROM funcionario WHERE id= :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("aluno não encontrado");
    }
    $user_id = $user['user_id'];

    // Remove o próprio funcionario
    $stmt = $pdo->prepare('DELETE FROM funcionario WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    //Remover user 
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();


    header('Location: lista_funcionario.php');
    exit;
} catch (PDOException $e) {
    die('Erro ao remover funcionario: ' . htmlspecialchars($e->getMessage()));
}

