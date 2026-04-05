<?php
include('../conexao.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Aluno inválido.');
}

$id = (int) $_GET['id'];

try {


    // Remove relações com encarregados
    $stmt = $pdo->prepare('DELETE FROM aluno_encarregado WHERE encarregado_id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    

    // pegar id de users na tabela aluno
    $stmt = $pdo->prepare('SELECT  user_id FROM encarregado WHERE id= :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user){
        die("encarregado não encontrado");
    }
    $user_id = $user['user_id'];

    // Remove o próprio aluno
    $stmt = $pdo->prepare('DELETE FROM encarregado WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    //Remover user 
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :user_id');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Depois de remover, volta para a lista de alunos
    header('Location: lista_encarregado.php');
    exit;
} catch (PDOException $e) {
    die('Erro ao remover aluno: ' . htmlspecialchars($e->getMessage()));
}

