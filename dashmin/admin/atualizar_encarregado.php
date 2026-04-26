<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=lista_encarregado');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta_encarregado_editar'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: index.php?page=lista_encarregado');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?page=lista_encarregado');
    exit;
}

$campos = ['nome', 'email', 'bi', 'contato', 'morada', 'genero', 'distrito', 'freguesia'];
$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

if (empty($set)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: index.php?page=editar_encarregado&id=$id");
    exit;
}

$valores[] = $id;
$sql = "UPDATE encarregado SET " . implode(', ', $set) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Encarregado atualizado com sucesso.'];
    header('Location: index.php?page=lista_encarregado');
} catch (Exception $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: index.php?page=editar_encarregado&id=$id");
}
exit;
