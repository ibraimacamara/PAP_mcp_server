<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_curso.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    header('Location: lista_curso.php');
    exit;
}

try {
    // Busca nome da imagem atual do curso
    $stmt = $pdo->prepare('SELECT imagem FROM curso WHERE id = ?');
    $stmt->execute([$id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagem = $curso['imagem'] ?? null;

    // Remove relações dependentes antes de apagar o curso
    // 1) aluno_curso
    $stmt = $pdo->prepare('DELETE FROM aluno_curso WHERE curso_id = ?');
    $stmt->execute([$id]);

    // 2) turmas do curso
    $stmt = $pdo->prepare('DELETE FROM turma WHERE curso_id = ?');
    $stmt->execute([$id]);

    // 3) próprio curso
    $stmt = $pdo->prepare('DELETE FROM curso WHERE id = ?');
    $stmt->execute([$id]);

    // 4) apaga o ficheiro da imagem, se existir e não for vazio
    if (!empty($imagem)) {
        $caminhoImagem = 'uploads/' . $imagem;
        if (is_file($caminhoImagem)) {
            @unlink($caminhoImagem);
        }
    }

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Curso removido com sucesso.'];
} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao remover curso: ' . $e->getMessage()];
}

header('Location: lista_curso.php');
exit;

