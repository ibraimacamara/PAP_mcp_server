<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_aluno.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_aluno.php');
    exit;
}

$numero_aluno = (int) ($_POST['numero_aluno'] ?? 0);
if ($numero_aluno <= 0) {
    header('Location: lista_aluno.php');
    exit;
}

$campos = ['nome', 'email', 'bi', 'contato', 'data_nascimento', 'morada', 'distrito', 'freguesia', 'genero'];
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
    header("Location: editar_aluno.php?id=$numero_aluno");
    exit;
}

$valores[] = $numero_aluno;
$sql = "UPDATE aluno SET " . implode(', ', $set) . " WHERE numero_aluno = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Aluno atualizado com sucesso.'];
    header("Location: detalhe_aluno.php?id=$numero_aluno");
} catch (Exception $e) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Erro ao atualizar: ' . $e->getMessage()];
    header("Location: editar_aluno.php?id=$numero_aluno");
}
exit;
