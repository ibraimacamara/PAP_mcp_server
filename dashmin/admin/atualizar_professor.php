<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_professor.php');
    exit;
}

/* ================================
   CSRF
================================ */
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_professor.php');
    exit;
}

/* ================================
   ID PROFESSOR
================================ */
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: lista_professor.php');
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
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Professor não encontrado.'];
    header('Location: lista_professor.php');
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
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: editar_professor.php?id=$id");
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
            'image/png' => 'png',
            'image/webp' => 'webp'
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

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg' => 'Professor atualizado com sucesso.'
    ];

    header('Location: lista_professor.php');

} catch (Exception $e) {

    $pdo->rollBack();

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar: ' . $e->getMessage()
    ];

    header("Location: lista_professor.php?id=$id");
}

exit;