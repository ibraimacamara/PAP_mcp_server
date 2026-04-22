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
    $_SESSION['alerta_aluno'] = ['tipo' => 'warning', 'msg' => $mensagem];
    $_SESSION['old_aluno'] = $dados;
    $_SESSION['tinha_foto_aluno'] = $tinhaFoto;
    header('Location: form_aluno.php');
    exit;
}
function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta_aluno'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    unset($_SESSION['old_aluno'], $_SESSION['tinha_foto_aluno']);
    header('Location: form_aluno.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido');
}

if (
    empty($_POST['csrf_token_aluno']) ||
    empty($_SESSION['csrf_token_aluno']) ||
    !hash_equals($_SESSION['csrf_token_aluno'], $_POST['csrf_token_aluno'])
) {
    erroUtilizador('Sessão expirada.');
}
$dados = [
    'nome' => trim($_POST['nome'] ?? ''),
    'data_nascimento' => $_POST['data_nascimento'] ?? '',
    'contato' => trim($_POST['contato'] ?? ''),
    'bi' => trim($_POST['bi'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'morada' => trim($_POST['morada'] ?? ''),
    'genero' => $_POST['genero'] ?? '',
    'distrito' => trim($_POST['distrito'] ?? ''),
    'freguesia' => trim($_POST['freguesia'] ?? ''),
    'curso_id' => (int) ($_POST['curso_id'] ?? 0),
    'turma_id' => (int) ($_POST['turma_id'] ?? 0),
    'encarregado_principal_id' => (int) ($_POST['encarregado_principal_id'] ?? 0),
    'laco_principal' => trim($_POST['laco_principal'] ?? ''),
    'encarregado_secundario_id' => (int) ($_POST['encarregado_secundario_id'] ?? 0),
    'laco_secundario' => trim($_POST['laco_secundario'] ?? '')
];

$tinhaFoto = !empty($_FILES['foto']['name']);

if (
    $dados['nome'] === '' || $dados['data_nascimento'] === '' || $dados['contato'] === '' ||
    $dados['bi'] === '' || $dados['email'] === '' || $dados['morada'] === '' ||
    $dados['genero'] === '' || $dados['distrito'] === '' || $dados['freguesia'] === '' ||
    $dados['curso_id'] <= 0 || $dados['turma_id'] <= 0
) {
    erroUtilizador('Preencha todos os campos obrigatórios.', $dados, $tinhaFoto);
}

if ($dados['encarregado_principal_id'] <= 0 || $dados['laco_principal'] === '') {
    erroUtilizador('Preencha o encarregado principal e o laço familiar.', $dados, $tinhaFoto);
}

if ($dados['encarregado_secundario_id'] > 0 && $dados['laco_secundario'] === '') {
    erroUtilizador('Informe o laço familiar do encarregado secundário.', $dados, $tinhaFoto);
}

if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    erroUtilizador('Email inválido.', $dados, $tinhaFoto);
}

$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];
    if (!isset($foto['tmp_name']) || !is_uploaded_file($foto['tmp_name']) || $foto['error'] !== UPLOAD_ERR_OK) {
        erroUtilizador('Erro no upload da foto.', $dados, true);
    }

    $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);
    if (!isset($tiposPermitidos[$mime])) {
        erroUtilizador('Apenas imagens JPEG, PNG ou GIF são permitidas.', $dados, true);
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        erroTecnico('Pasta uploads indisponível em salvar_aluno.php');
    }

    $novoNome = 'aluno_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $dados['bi']) . '.' . $tiposPermitidos[$mime];
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
        ':categoria' => 'aluno',
        ':foto' => $fotoPath
    ]);
    $userId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare("
    INSERT INTO aluno 
    (user_id, nome, data_nascimento, contato, bi, email, morada, genero, distrito, freguesia,
    curso_id, turma_id, encarregado_principal_id, encarregado_secundario_id)
    VALUES 
    (:user_id, :nome, :data, :contato, :bi, :email, :morada, :genero, :distrito, :freguesia,
    :curso, :turma, :enc_principal, :enc_secundario)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':nome' => $dados['nome'],
        ':data' => $dados['data_nascimento'],
        ':contato' => $dados['contato'],
        ':bi' => $dados['bi'],
        ':email' => $dados['email'],
        ':morada' => $dados['morada'],
        ':genero' => $dados['genero'],
        ':distrito' => $dados['distrito'],
        ':freguesia' => $dados['freguesia'],
        ':curso' => $dados['curso_id'],
        ':turma' => $dados['turma_id'],
        ':enc_principal' => $dados['encarregado_principal_id'],
        ':enc_secundario' => $dados['encarregado_secundario_id'] ?: null
    ]);

    $pdo->commit();
    $_SESSION['alerta_aluno'] = ['tipo' => 'success', 'msg' => 'Aluno registado com sucesso.'];
    unset($_SESSION['old_aluno'], $_SESSION['tinha_foto_aluno'], $_SESSION['csrf_token_aluno']);
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
            erroUtilizador('Email já registado.', $dados, $tinhaFoto);
        }
        if (str_contains($msg, 'bi')) {
            erroUtilizador('BI já registado.', $dados, $tinhaFoto);
        }
        erroUtilizador('Já existe um registo com os dados informados.', $dados, $tinhaFoto);
    }

    erroTecnico('Erro BD em salvar_aluno.php: ' . $e->getMessage());
}
header('Location: form_aluno.php');
exit;
