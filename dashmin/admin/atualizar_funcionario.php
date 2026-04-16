<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_funcionario.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Token CSRF inválido.'
    ];
    header('Location: lista_funcionario.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'ID inválido.'
    ];
    header('Location: lista_funcionario.php');
    exit;
}

// Buscar user_id e foto atual
$stmt = $pdo->prepare("
    SELECT f.user_id, u.foto
    FROM funcionario f
    LEFT JOIN users u ON u.id = f.user_id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Funcionário não encontrado.'
    ];
    header('Location: lista_funcionario.php');
    exit;
}

$user_id = $dados['user_id'] ?? null;
$fotoAtual = $dados['foto'] ?? '';

// Normalizar género
if (isset($_POST['genero'])) {
    $genero = trim($_POST['genero']);
    $genero = strtolower($genero); // fica "masculino" ou "feminino"
    $_POST['genero'] = $genero;
}

// Campos da tabela funcionario
$camposFuncionario = [
    'nome',
    'bi',
    'email',
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

if (empty($set)) {
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'msg' => 'Nenhum dado para atualizar.'
    ];
    header("Location: editar_funcionario.php?id=$id");
    exit;
}

$valores[] = $id;
$sqlFuncionario = "UPDATE funcionario SET " . implode(', ', $set) . " WHERE id = ?";

$pdo->beginTransaction();

try {
    // Atualizar tabela funcionario
    $stmt = $pdo->prepare($sqlFuncionario);
    $stmt->execute($valores);

    // Upload da foto
    if (!empty($_FILES['foto']['name'])) {
        if (empty($user_id)) {
            throw new Exception('Este funcionário não tem utilizador associado para guardar a foto.');
        }

        $foto = $_FILES['foto'];

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($foto['type'], $tiposPermitidos)) {
            throw new Exception('Formato de imagem inválido.');
        }

        $ext = $tiposPermitidos[$foto['type']];
        $novoNome = 'funcionario_' . $user_id . '.' . $ext;
        $pasta = '../uploads/';
        $destino = $pasta . $novoNome;

        if (!move_uploaded_file($foto['tmp_name'], $destino)) {
            throw new Exception('Erro ao guardar a imagem.');
        }

        // Apagar foto antiga, se for diferente
        if (!empty($fotoAtual) && $fotoAtual !== $novoNome && file_exists($pasta . $fotoAtual)) {
            unlink($pasta . $fotoAtual);
        }

        // Atualizar foto na tabela users
        $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmt->execute([$novoNome, $user_id]);
    }

    $pdo->commit();

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg'  => 'Funcionário atualizado com sucesso.'
    ];

    header('Location: lista_funcionario.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg'  => 'Erro ao atualizar funcionário: ' . $e->getMessage()
    ];

    header("Location: editar_funcionario.php?id=$id");
    exit;
}