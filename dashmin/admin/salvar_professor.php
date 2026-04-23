<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
session_start();

include '../conexao.php';

define('LOG_FILE', __DIR__ . '/../logs/app.log');

function logErro(string $mensagem): void
{
    $data = date('Y-m-d H:i:s');
    error_log("[$data] $mensagem\n", 3, LOG_FILE);
}

function erroUtilizador(string $mensagem, array $dados = [], bool $tinhaFoto = false): void
{
    $_SESSION['alerta_professor'] = ['tipo' => 'warning', 'msg' => $mensagem];
    $_SESSION['old_professor'] = $dados;
    $_SESSION['tinha_foto_professor'] = $tinhaFoto;
    header('Location: index.php?page=form_professor');
    exit;
}

function sucessoUtilizador(string $mensagem): void
{
    $_SESSION['alerta_professor'] = ['tipo' => 'success', 'msg' => $mensagem];
    unset($_SESSION['old_professor'], $_SESSION['tinha_foto_professor'], $_SESSION['csrf_token_professor']);
    header('Location: index.php?page=form_professor');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta_professor'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    unset($_SESSION['old_professor'], $_SESSION['tinha_foto_professor']);
    header('Location: index.php?page=form_professor');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido em salvar_professor.php');
}

if (
    empty($_POST['csrf_token_professor']) ||
    empty($_SESSION['csrf_token_professor']) ||
    !hash_equals($_SESSION['csrf_token_professor'], $_POST['csrf_token_professor'])
) {
    erroUtilizador('Sessão expirada. Tente novamente.');
}

$dados = [
    'nome' => trim($_POST['nome'] ?? ''),
    'data_nascimento' => $_POST['data_nascimento'] ?? '',
    'contato' => trim($_POST['contato'] ?? ''),
    'bi' => trim($_POST['bi'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'morada' => trim($_POST['morada'] ?? ''),
    'nacionalidade' => trim($_POST['nacionalidade'] ?? ''),
    'nif' => trim($_POST['nif'] ?? ''),
    'genero' => $_POST['genero'] ?? '',
    'distrito' => trim($_POST['distrito'] ?? ''),
    'freguesia' => trim($_POST['freguesia'] ?? ''),
    'grupo_disciplinar' => trim($_POST['grupo_disciplinar'] ?? ''),
    't_contrato' => $_POST['t_contrato'] ?? '',
    'h_profissional' => trim($_POST['h_profissional'] ?? ''),
    'h_academica' => trim($_POST['h_academica'] ?? '')
];

$tinhaFoto = !empty($_FILES['foto']['name']);

foreach ($dados as $campo => $valor) {
    if ($valor === '') {
        erroUtilizador('Preencha todos os campos obrigatórios.', $dados, $tinhaFoto);
    }
}

if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    erroUtilizador('Email inválido.', $dados, $tinhaFoto);
}

$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if (!isset($foto['tmp_name']) || !is_uploaded_file($foto['tmp_name'])) {
        erroUtilizador('Upload da foto inválido.', $dados, true);
    }

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        erroUtilizador('Erro no upload da foto.', $dados, true);
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'

    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);

    if (!isset($tiposPermitidos[$mime])) {
        erroUtilizador('Apenas imagens JPG, JPEG, PNG ou GIF são permitidas.', $dados, true);
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        erroTecnico('Pasta uploads indisponível para salvar_professor.php');
    }

    $novoNome = 'professor_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $dados['bi']) . '.' . $tiposPermitidos[$mime];
    $destino = $uploadDir . $novoNome;

    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        erroUtilizador('Falha ao guardar a foto.', $dados, true);
    }

    $fotoPath = $novoNome;
}

try {
    $pdo->beginTransaction();

    $senhaHash = password_hash($dados['bi'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, senha, categoria, foto)
        VALUES (:username, :senha, :categoria, :foto)
    ");
    $stmt->execute([
        ':username' => $dados['email'],
        ':senha' => $senhaHash,
        ':categoria' => 'professor',
        ':foto' => $fotoPath
    ]);

    $userId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO professor
        (user_id, nome, data_nascimento, contato, bi, email, morada, nacionalidade, nif, genero, distrito, freguesia, grupo_d, tipo_c, h_profissional, h_academica)
        VALUES
        (:user_id, :nome, :data, :contato, :bi, :email, :morada, :nacionalidade, :nif, :genero, :distrito, :freguesia, :grupo_disciplinar, :tipo_contrato, :h_profissional, :h_academica)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':nome' => $dados['nome'],
        ':data' => $dados['data_nascimento'],
        ':contato' => $dados['contato'],
        ':bi' => $dados['bi'],
        ':email' => $dados['email'],
        ':morada' => $dados['morada'],
        ':nacionalidade' => $dados['nacionalidade'],
        ':nif' => $dados['nif'],
        ':genero' => $dados['genero'],
        ':distrito' => $dados['distrito'],
        ':freguesia' => $dados['freguesia'],
        ':grupo_disciplinar' => $dados['grupo_disciplinar'],
        ':tipo_contrato' => $dados['t_contrato'],
        ':h_profissional' => $dados['h_profissional'],
        ':h_academica' => $dados['h_academica']
    ]);

    $pdo->commit();
    sucessoUtilizador('Professor registado com sucesso.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($fotoPath) {
        $caminhoFoto = __DIR__ . '/../uploads/' . $fotoPath;
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    if ($e->getCode() === '23000') {
        $msg = strtolower($e->getMessage());
        if (str_contains($msg, 'username') || str_contains($msg, 'email')) {
            erroUtilizador('O email já está registado. Tente outro.', $dados, $tinhaFoto);
        }
        if (str_contains($msg, 'bi')) {
            erroUtilizador('O BI já está registado. Tente outro.', $dados, $tinhaFoto);
        }
        if (str_contains($msg, 'nif')) {
            erroUtilizador('O NIF já está registado. Tente outro.', $dados, $tinhaFoto);
        }
        if (str_contains($msg, 'contato') || str_contains($msg, 'contacto')) {
            erroUtilizador('O contacto já está registado. Tente outro.', $dados, $tinhaFoto);
        }
        erroUtilizador('Já existe um registo com um dos dados informados.', $dados, $tinhaFoto);
    }

    erroTecnico('Erro BD em salvar_professor.php: ' . $e->getMessage());
}
