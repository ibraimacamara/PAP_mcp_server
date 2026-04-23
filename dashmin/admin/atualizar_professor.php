<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=lista_professor');
    exit;
}

/* ================================
   CSRF
================================ */
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta_professor'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('index.php?page=Location: lista_professor');
    exit;
}

/* ================================
   ID PROFESSOR
================================ */
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php?page=lista_professor');
    exit;
}

/* ================================
   BUSCAR USER_ID E FOTO ATUAL
================================ */
$stmt = $pdo->prepare("
SELECT professor.user_id, users.foto
FROM professor
JOIN users ON users.id = professor.user_id
WHERE professor.id = ?
");
$stmt->execute([$id]);

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    $_SESSION['alerta_professor'] = ['tipo' => 'danger', 'msg' => 'Professor não encontrado.'];
    header('Location: index.php?page=lista_professor');
    exit;
}

$user_id = $dados['user_id'];
$fotoAtual = $dados['foto'];

/* ================================
   CAMPOS DO PROFESSOR
================================ */
$campos = [
    'nome',
    'email',
    'bi',
    'nif',
    'contato',
    'data_nascimento',
    'morada',
    'nacionalidade',
    'genero',
    'distrito',
    'freguesia',
    'cargo',
    'tipo_c',
    'h_profissional',
    'h_academica'
];

$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

if (empty($set)) {
    $_SESSION['alerta_professor'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: index.php?page=editar_professor&id=$id");
    exit;
}

/* ================================
   UPDATE PROFESSOR
================================ */
$valores[] = $id;

$sql = "UPDATE professor SET " . implode(', ', $set) . " WHERE id = ?";

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    /* ================================
       UPLOAD FOTO
    ================================= */

    if (!empty($_FILES['foto']['name'])) {

        $foto = $_FILES['foto'];

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        if (!array_key_exists($foto['type'], $tiposPermitidos)) {
            throw new Exception('Formato de imagem inválido.');
        }

        $ext = $tiposPermitidos[$foto['type']];
        $novoNome = "professor_" . $user_id . "." . $ext;

        $pasta = "../uploads/";
        $destino = $pasta . $novoNome;

        if (!move_uploaded_file($foto['tmp_name'], $destino)) {
            throw new Exception('Erro ao guardar a imagem.');
        }

        /* apagar foto antiga */
        if (!empty($fotoAtual) && file_exists($pasta . $fotoAtual)) {
            unlink($pasta . $fotoAtual);
        }

        /* atualizar foto na tabela users */

        $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmt->execute([$novoNome, $user_id]);
    }

    $pdo->commit();

    $_SESSION['alerta_professor'] = [
        'tipo' => 'success',
        'msg' => 'Professor atualizado com sucesso.'
    ];

    header('Location: index.php?page=lista_professor');

} catch (Exception $e) {

    $pdo->rollBack();

    $_SESSION['alerta_professor'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar: ' . $e->getMessage()
    ];

    header("Location: index.php?page=lista_professor&id=$id");
}

exit;