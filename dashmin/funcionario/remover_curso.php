<?php
include('../conexao.php');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: lista_curso.php');
    exit;
}

$id = (int) $_POST['id'];

try {
    // Buscar a imagem antes de apagar
    $stmt = $pdo->prepare('SELECT imagem FROM curso WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($curso && !empty($curso['imagem'])) {
        $caminho = '../uploads/' . $curso['imagem'];

        if (file_exists($caminho) && is_file($caminho)) {
            unlink($caminho);
        }
    }

    // Apagar o curso
    $stmt = $pdo->prepare('DELETE FROM curso WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: lista_curso.php');
    exit;

} catch (PDOException $e) {
    echo 'Erro ao remover curso: ' . htmlspecialchars($e->getMessage());
    exit;
}