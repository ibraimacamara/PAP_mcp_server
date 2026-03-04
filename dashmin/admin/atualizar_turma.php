<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_turma.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_turma.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: lista_turma.php');
    exit;
}

$campos = ['codigo', 'ciclo_formacao', 'curso_id', 'statu'];
$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

if (empty($set)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: editar_turma.php?id=$id");
    exit;
}

$valores[] = $id;
$sql = "UPDATE turma SET " . implode(', ', $set) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Turma atualizada com sucesso.'];
    header('Location: lista_turma.php');
} catch (Exception $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: editar_turma.php?id=$id");
}
exit;
