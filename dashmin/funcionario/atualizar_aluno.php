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
$userId = (int) ($_POST['user_id'] ?? 0);

if ($numero_aluno <= 0) {
    header('Location: lista_aluno.php');
    exit;
}

/* =========================
   CAMPOS DIRETOS (SEM RELAÇÕES)
========================= */
$campos = [
    'nome',
    'email',
    'bi',
    'contato',
    'data_nascimento',
    'morada',
    'distrito',
    'freguesia',
    'genero',
    'curso_id',
    'turma_id',
    'encarregado_principal_id',
    'encarregado_secundario_id'
];

$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $set[] = "`$campo` = ?";
        $valores[] = $_POST[$campo] !== '' ? trim($_POST[$campo]) : null;
    }
}

if (empty($set)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => 'Nenhum campo para atualizar.'];
    header("Location: editar_aluno.php?id=$numero_aluno");
    exit;
}

/* =========================
   FOTO
========================= */
$novaFotoNome = null;

if ($userId > 0 && !empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if ($foto['error'] === UPLOAD_ERR_OK) {

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif'
        ];

        if (isset($tiposPermitidos[$foto['type']])) {

            $uploadDir = __DIR__ . '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = $tiposPermitidos[$foto['type']];
            $novoNome = 'aluno_' . $numero_aluno . '_' . time() . '.' . $ext;
            $destino = $uploadDir . $novoNome;

            if (move_uploaded_file($foto['tmp_name'], $destino)) {
                $novaFotoNome = $novoNome;
            }
        }
    }
}

/* =========================
   UPDATE DIRETO
========================= */
$valores[] = $numero_aluno;
$sql = "UPDATE aluno SET " . implode(', ', $set) . " WHERE numero_aluno = ?";

try {
    $pdo->beginTransaction();

    // Atualiza aluno (inclui curso_id, turma_id, encarregados)
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    // Atualiza foto no users (se existir)
    if ($novaFotoNome !== null && $userId > 0) {
        $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmt->execute([$novaFotoNome, $userId]);
    }

    $pdo->commit();

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg' => 'Aluno atualizado com sucesso.'
    ];

    header("Location: detalhe_aluno.php?id=$numero_aluno");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar: ' . $e->getMessage()
    ];

    header("Location: editar_aluno.php?id=$numero_aluno");
    exit;
}