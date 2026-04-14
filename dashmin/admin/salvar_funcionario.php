<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
session_start();

include '../conexao.php';

/* =====================================================
   LOG E ERRO
===================================================== */
define('LOG_FILE', __DIR__ . '/../logs/app.log');

function logErro(string $mensagem): void
{
    $data = date('Y-m-d H:i:s');
    error_log("[$data] $mensagem\n", 3, LOG_FILE);
}

function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta'] = ['tipo' => 'warning', 'msg' => $mensagem];

    // guarda os dados do formulário
    $_SESSION['old'] = $_POST;

    header('Location: form_funcionario.php');
    exit;
}

function old($campo)
{
    return htmlspecialchars($_SESSION['old'][$campo] ?? '', ENT_QUOTES, 'UTF-8');
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    header('Location: form_funcionario.php');
    exit;
}


//SEGURANÇA no request

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido');
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    erroUtilizador('Sessão expirada.');
}


//RECEBENDO DADOS

$nome = trim($_POST['nome'] ?? '');
$dataNasc = $_POST['data_nascimento'] ?? '';
$contato = trim($_POST['contato'] ?? '');
$bi = trim($_POST['bi'] ?? '');
$email = trim($_POST['email'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$nacionalidade = trim($_POST['nacionalidade'] ?? '');
$nif = trim($_POST['nif'] ?? '');
$genero = $_POST['genero'] ?? '';
$distrito = trim($_POST['distrito'] ?? '');
$freguesia = trim($_POST['freguesia'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');

$categoria = trim($_POST['categoria'] ?? '');
$t_contrato = $_POST['t_contrato'] ?? '';
$h_profissional = trim($_POST['h_profissional'] ?? '');
$h_academica = trim($_POST['h_academica'] ?? '');


//VALIDAÇÃO

if (
    !$nome || !$dataNasc || !$contato || !$bi || !$email || !$nacionalidade || !$nif
    || !$t_contrato || !$h_profissional || !$h_academica || !$cargo || !$categoria
) {

    erroUtilizador('Preencha todos os campos obrigatórios.');
}



if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    erroUtilizador('Email inválido.');
}


//UPLOAD DE FOTO


$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        erroUtilizador('Erro no upload da foto.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!array_key_exists($foto['type'], $tiposPermitidos)) {
        erroUtilizador('Apenas imagens JPEG, PNG ou GIF são permitidas.');
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        erroUtilizador('A pasta de uploads não existe.');
    }

    $extensao = $tiposPermitidos[$foto['type']];
    $novoNome = 'funcionario_' . $bi . '.' . $extensao;
    $destino = $uploadDir . $novoNome;


    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        erroUtilizador('Falha ao guardar a foto.');
    }

    $fotoPath = $novoNome;
}



try {
    $pdo->beginTransaction();


    // users

    $senhaOriginal = $bi;

    $senhaHash = password_hash($senhaOriginal, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
    INSERT INTO users (username, senha, categoria, foto)
    VALUES (:username, :senha, :categoria, :foto)
    ");
    $stmt->execute([
        ':username' => $email,
        ':senha' => $senhaHash,
        ':categoria' => $categoria,
        ':foto' => $fotoPath
    ]);

    $userId = (int) $pdo->lastInsertId();

    // Inserir funcionario
    $stmt = $pdo->prepare("
        INSERT INTO funcionario
        (user_id, nome, data_nascimento, contato, bi, morada,nacionalidade, nif, genero, distrito, freguesia, cargo, tipo_c, h_profissional, h_academica )
        VALUES (:user_id,:nome, :data, :contato, :bi, :morada,:nacionalidade, :nif, :genero, :distrito, :freguesia, :cargo, :tipo_contrato, :h_profissional, :h_academica)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':nome' => $nome,
        ':data' => $dataNasc,
        ':contato' => $contato,
        ':bi' => $bi,

        ':morada' => $morada,
        ':nacionalidade' => $nacionalidade,
        ':nif' => $nif,
        ':genero' => $genero,
        ':distrito' => $distrito,
        ':freguesia' => $freguesia,
        ':cargo' => $cargo,
        ':tipo_contrato' => $t_contrato,
        ':h_profissional' => $h_profissional,
        ':h_academica' => $h_academica

    ]);


    $pdo->commit();

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'funcionario registado com sucesso.'];

} catch (PDOException $e) {
    $pdo->rollBack();

    // Remove foto se já tiver sido movida
    if ($fotoPath && file_exists('/' . $fotoPath)) {
        unlink('/' . $fotoPath);
    }

    if ($e->getCode() === '23000') {
        erroUtilizador('funcionario já registado.');
    }

    erroTecnico('Erro BD: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
unset($_SESSION['old']);
header('Location: form_funcionario.php');
exit;
