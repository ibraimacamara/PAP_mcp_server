<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=lista_turma');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta_turma_editar'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: index.php?page=lista_turma');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?page=lista_turma');
    exit;
}

$campos = ['codigo', 'ciclo_formacao', 'curso_id'];
$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

// Diretor (ID do professor) tratado separadamente
$diretorId = isset($_POST['diretor_id']) && $_POST['diretor_id'] !== ''
    ? (int) $_POST['diretor_id']
    : null;

$set[] = "`diretor` = ?";
$valores[] = $diretorId;

if (empty($set)) {
    $_SESSION['alerta_turma_editar'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: index.php?page=editar_turma&id=$id");
    exit;
}

$valores[] = $id;
$sql = "UPDATE turma SET " . implode(', ', $set) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    $_SESSION['alerta_turma_editar'] = ['tipo' => 'success', 'msg' => 'Turma atualizada com sucesso.'];
    header('Location: index.php?page=lista_turma');
} catch (Exception $e) {
    $_SESSION['alerta_turma_editar'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: index.php?page=editar_turma&id=$id");
}
exit;
