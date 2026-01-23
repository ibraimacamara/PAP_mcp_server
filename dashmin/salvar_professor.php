<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
session_start();

require_once 'conexao.php';

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
    header('Location: form_professor.php');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    header('Location: form_professor.php');
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

$nome           = trim($_POST['nome'] ?? '');
$dataNasc       = $_POST['data_nascimento'] ?? '';
$contato        = trim($_POST['contato'] ?? '');
$bi             = trim($_POST['bi'] ?? '');
$email          = trim($_POST['email'] ?? '');
$morada         = trim($_POST['morada'] ?? '');
$nacionalidade  = trim($_POST['nacionalidade']??'');
$nif            = trim($_POST['nif'] ?? '');
$genero         = $_POST['genero'] ?? '';
$distrito       = trim($_POST['distrito'] ?? '');
$freguesia      = trim($_POST['freguesia'] ?? '');


//VALIDAÇÃO

if (!$nome || !$dataNasc || !$contato || !$bi || !$email || !$nacionalidade || !$nif) {
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

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        erroUtilizador('A pasta de uploads não existe.');
    }

    $extensao = $tiposPermitidos[$foto['type']];
    $novoNome = 'professor_' . $bi . '.' . $extensao;
    $destino = $uploadDir . $novoNome;


    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        erroUtilizador('Falha ao guardar a foto.');
    }

    $fotoPath = $novoNome;
}




try {
    $pdo->beginTransaction();

    // Inserir professor
    $stmt = $pdo->prepare("
        INSERT INTO professor 
        (nome, data_nascimento, contato, bi, email, morada,nacionalidade, nif, genero, distrito, freguesia, foto)
        VALUES (:nome, :data, :contato, :bi, :email, :morada,:nacionalidade, :nif, :genero, :distrito, :freguesia, :foto)
    ");
    $stmt->execute([
        ':nome' => $nome,
        ':data' => $dataNasc,
        ':contato' => $contato,
        ':bi' => $bi,
        ':email' => $email,
        ':morada' => $morada,
        ':nacionalidade' => $nacionalidade,
        ':nif' => $nif,
        ':genero' => $genero,
        ':distrito' => $distrito,
        ':freguesia' => $freguesia,
        ':foto' => $fotoPath
    ]);
    


    // users
    $partesNome = explode(' ', trim($nome));
    $apelido = end($partesNome);

    $apelidoFormatado = mb_convert_case($apelido, MB_CASE_TITLE, 'UTF-8');

    $anoAtual = date('Y');

    $senhaOriginal = $apelidoFormatado . $anoAtual . '#';

    $senhaHash = password_hash($senhaOriginal, PASSWORD_DEFAULT);
    $categoria = "professor";
    $stmt = $pdo->prepare("
    INSERT INTO users (email, senha, categoria, foto)
    VALUES (:email, :senha, :categoria, :foto)
    ");
    $stmt->execute([
        ':email' => $email,
        ':senha' => $senhaHash,
        ':categoria' => $categoria,
        'foto' => $fotoPath
    ]);

    $userId = (int) $pdo->lastInsertId();


 
 

    $pdo->commit();

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Professor registado com sucesso.'];

} catch (PDOException $e) {
    $pdo->rollBack();

    // Remove foto se já tiver sido movida
    if ($fotoPath && file_exists(__DIR__ . '/' . $fotoPath)) {
        unlink(__DIR__ . '/' . $fotoPath);
    }

    if ($e->getCode() === '23000') {
        erroUtilizador('Professor já registado.');
    }

    erroTecnico('Erro BD: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
header('Location: form_professor.php');
exit;
