<?php
session_start();
include('../conexao.php');

// ================================
// Verifica método POST
// ================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_funcionario.php');
    exit;
}

// ================================
// CSRF
// ================================
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Token CSRF inválido.'];
    header('Location: lista_funcionario.php');
    exit;
}

// ================================
// ID do funcionário
// ================================
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'ID inválido.'];
    header('Location: lista_funcionario.php');
    exit;
}

// ================================
// Buscar user_id, foto atual e email
// ================================
$stmt = $pdo->prepare("
    SELECT f.user_id, u.foto, u.email
    FROM funcionario f
    JOIN users u ON u.id = f.user_id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Funcionário não encontrado.'];
    header('Location: lista_funcionario.php');
    exit;
}

$user_id = $dados['user_id'];
$fotoAtual = $dados['foto'] ?? '';
$emailAtual = $dados['email'] ?? '';

// ================================
// Normalizar gênero
// ================================
if (isset($_POST['genero'])) {
    $genero = trim($_POST['genero']);
    $genero = ucfirst(strtolower($genero)); // "masculino" -> "Masculino"
    $_POST['genero'] = $genero;
}

// ================================
// Campos do funcionário (tabela funcionario)
// ================================
$camposFuncionario = [
    'nome',
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
foreach ($camposFuncionario as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "`$campo` = ?";
        $valores[] = trim($_POST[$campo]);
    }
}

$valores[] = $id;
$sqlFuncionario = "UPDATE funcionario SET " . implode(', ', $set) . " WHERE id = ?";

// ================================
// Início da transação
// ================================
$pdo->beginTransaction();

try {
    // Atualiza funcionario
    $stmt = $pdo->prepare($sqlFuncionario);
    $stmt->execute($valores);

    // Atualiza email na tabela users
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);
    }

    // Upload de foto (se houver)
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
        $novoNome = "funcionario_" . $user_id . "." . $ext;
        $pasta = "uploads/";
        $destino = $pasta . $novoNome;

        if (!move_uploaded_file($foto['tmp_name'], $destino)) {
            throw new Exception('Erro ao guardar a imagem.');
        }

        // Apaga foto antiga
        if (!empty($fotoAtual) && file_exists($pasta . $fotoAtual)) {
            unlink($pasta . $fotoAtual);
        }

        // Atualiza foto na tabela users
        $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmt->execute([$novoNome, $user_id]);
    }

    $pdo->commit();

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg' => 'Funcionário atualizado com sucesso.'
    ];
    header('Location: lista_funcionario.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar funcionário: ' . $e->getMessage()
    ];
    header("Location: editar_funcionario.php?id=$id");
    exit;
}