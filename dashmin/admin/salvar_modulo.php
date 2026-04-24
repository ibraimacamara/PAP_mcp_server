<?php
include('../conexao.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=modulo');
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Token CSRF inválido.');
}

$id_curso = !empty($_POST['id_curso']) ? (int) $_POST['id_curso'] : null;
$nome_modulo = trim($_POST['nome_modulo'] ?? '');
$codigo_modulo = trim($_POST['codigo_modulo'] ?? '');
$ordem = (int) ($_POST['ordem'] ?? 0);
$carga_horaria = (int) ($_POST['carga_horaria'] ?? 0);

$foto = null;

if (!empty($_FILES['foto']['name'])) {
    $permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    if (!in_array($_FILES['foto']['type'], $permitidos, true)) {
        die('Tipo de imagem inválido.');
    }

    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto = uniqid('modulo_', true) . '.' . $extensao;

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        '../uploads' . $foto
    );
}

$sql = "INSERT INTO modulo 
        (id_curso, nome_modulo, codigo_modulo, ordem, carga_horaria, foto)
        VALUES 
        (:id_curso, :nome_modulo, :codigo_modulo, :ordem, :carga_horaria, :foto)";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(':id_curso', $id_curso, $id_curso === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
$stmt->bindValue(':nome_modulo', $nome_modulo);
$stmt->bindValue(':codigo_modulo', $codigo_modulo);
$stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
$stmt->bindValue(':carga_horaria', $carga_horaria, PDO::PARAM_INT);
$stmt->bindValue(':foto', $foto);

$stmt->execute();

$_SESSION['alerta_modulo'] = [
    'tipo' => 'success',
    'msg' => 'Módulo registado com sucesso.'
];

header('Location: index.php?page=modulo');
exit;