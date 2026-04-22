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

function redirecionarComErroUtilizador(string $mensagem): void
{
    $_SESSION['alerta_funcionario'] = [
        'tipo' => 'warning',
        'msg' => $mensagem
    ];

    $_SESSION['old_funcionario'] = $_POST;

    if (!empty($_FILES['foto']['name'])) {
        $_SESSION['tinha_foto_funcionario'] = true;
    }

    header('Location: form_funcionario.php');
    exit;
}

function redirecionarComErroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);

    $_SESSION['alerta_funcionario'] = [
        'tipo' => 'danger',
        'msg' => 'Ocorreu um erro interno.'
    ];

    $_SESSION['old_funcionario'] = $_POST;

    if (!empty($_FILES['foto']['name'])) {
        $_SESSION['tinha_foto_funcionario'] = true;
    }

    header('Location: form_funcionario.php');
    exit;
}

function obterUserIdPorEmail(PDO $pdo, string $email): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $id = $stmt->fetchColumn();

    return $id === false ? null : (int)$id;
}

function userEstaVinculadoAFuncionario(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM funcionario WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $userId]);

    return (bool)$stmt->fetchColumn();
}

function limparUserOrfaoPorEmail(PDO $pdo, string $email): void
{
    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE username = :email
          AND NOT EXISTS (
              SELECT 1
              FROM funcionario
              WHERE funcionario.user_id = users.id
          )
    ");
    $stmt->execute([':email' => $email]);
}

function limparFotoSeExistir(?string $fotoPath): void
{
    if (empty($fotoPath)) {
        return;
    }

    $caminhoCompleto = __DIR__ . '/../uploads/' . $fotoPath;

    if (is_file($caminhoCompleto)) {
        unlink($caminhoCompleto);
    }
}

function validarMimeRealImagem(string $tmpName): ?string
{
    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return null;
    }

    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if ($mime === false || !isset($tiposPermitidos[$mime])) {
        return null;
    }

    return $tiposPermitidos[$mime];
}

/* =====================================================
   SEGURANÇA DO REQUEST
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionarComErroTecnico('Método HTTP inválido em salvar_funcionario.php', 405);
}

if (
    empty($_POST['csrf_token_funcionario']) ||
    empty($_SESSION['csrf_token_funcionario']) ||
    !hash_equals($_SESSION['csrf_token_funcionario'], $_POST['csrf_token_funcionario'])
) {
    redirecionarComErroUtilizador('Sessão expirada. Atualize a página e tente novamente.');
}

/* =====================================================
   RECEBER DADOS
===================================================== */
$nome = trim((string)($_POST['nome'] ?? ''));
$dataNasc = trim((string)($_POST['data_nascimento'] ?? ''));
$contato = trim((string)($_POST['contato'] ?? ''));
$bi = trim((string)($_POST['bi'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$morada = trim((string)($_POST['morada'] ?? ''));
$nacionalidade = trim((string)($_POST['nacionalidade'] ?? ''));
$nif = trim((string)($_POST['nif'] ?? ''));
$genero = trim((string)($_POST['genero'] ?? ''));
$distrito = trim((string)($_POST['distrito'] ?? ''));
$freguesia = trim((string)($_POST['freguesia'] ?? ''));
$cargo = trim((string)($_POST['cargo'] ?? ''));
$categoria = trim((string)($_POST['categoria'] ?? ''));
$t_contrato = trim((string)($_POST['t_contrato'] ?? ''));
$h_profissional = trim((string)($_POST['h_profissional'] ?? ''));
$h_academica = trim((string)($_POST['h_academica'] ?? ''));

/* =====================================================
   VALIDAÇÃO
===================================================== */
if (
    $nome === '' ||
    $dataNasc === '' ||
    $contato === '' ||
    $bi === '' ||
    $email === '' ||
    $morada === '' ||
    $nacionalidade === '' ||
    $nif === '' ||
    $genero === '' ||
    $distrito === '' ||
    $freguesia === '' ||
    $cargo === '' ||
    $categoria === '' ||
    $t_contrato === '' ||
    $h_profissional === '' ||
    $h_academica === ''
) {
    redirecionarComErroUtilizador('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionarComErroUtilizador('Email inválido.');
}

$generosPermitidos = ['Masculino', 'Feminino'];
if (!in_array($genero, $generosPermitidos, true)) {
    redirecionarComErroUtilizador('Género inválido.');
}

$categoriasPermitidas = ['admin', 'funcionario'];
if (!in_array($categoria, $categoriasPermitidas, true)) {
    redirecionarComErroUtilizador('Categoria inválida.');
}

$tiposContratoPermitidos = [
    'Contrato com termo',
    'Contrato sem termo',
    'Prestação de serviços'
];
if (!in_array($t_contrato, $tiposContratoPermitidos, true)) {
    redirecionarComErroUtilizador('Tipo de contrato inválido.');
}

$dataObj = DateTime::createFromFormat('Y-m-d', $dataNasc);
if (!$dataObj || $dataObj->format('Y-m-d') !== $dataNasc) {
    redirecionarComErroUtilizador('Data de nascimento inválida.');
}

$hoje = new DateTime('today');
if ($dataObj > $hoje) {
    redirecionarComErroUtilizador('A data de nascimento não pode ser no futuro.');
}

/* =====================================================
   VALIDAR EMAIL JÁ EXISTENTE / ESTADO INCONSISTENTE
===================================================== */
try {
    $userIdExistente = obterUserIdPorEmail($pdo, $email);

    if ($userIdExistente !== null) {
        if (userEstaVinculadoAFuncionario($pdo, $userIdExistente)) {
            redirecionarComErroUtilizador('O email já está registado. Tente outro.');
        }

        limparUserOrfaoPorEmail($pdo, $email);
    }
} catch (PDOException $e) {
    redirecionarComErroTecnico('Erro ao validar email existente em salvar_funcionario.php: ' . $e->getMessage());
}

/* =====================================================
   UPLOAD DE FOTO
===================================================== */
$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if (!isset($foto['error'], $foto['tmp_name'], $foto['size'])) {
        redirecionarComErroUtilizador('Upload de foto inválido.');
    }

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        redirecionarComErroUtilizador('Erro no upload da foto.');
    }

    if ($foto['size'] > 2 * 1024 * 1024) {
        redirecionarComErroUtilizador('A foto não pode ter mais de 2MB.');
    }

    if (!is_uploaded_file($foto['tmp_name'])) {
        redirecionarComErroUtilizador('Ficheiro de foto inválido.');
    }

    $extensao = validarMimeRealImagem($foto['tmp_name']);
    if ($extensao === null) {
        redirecionarComErroUtilizador('Apenas imagens JPEG, PNG ou GIF são permitidas.');
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        redirecionarComErroUtilizador('A pasta de uploads não existe.');
    }

    if (!is_writable($uploadDir)) {
        redirecionarComErroUtilizador('A pasta de uploads não tem permissões de escrita.');
    }

    $novoNome = 'funcionario_' . bin2hex(random_bytes(16)) . '.' . $extensao;
    $destino = $uploadDir . $novoNome;

    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        redirecionarComErroUtilizador('Falha ao guardar a foto.');
    }

    $fotoPath = $novoNome;
}

/* =====================================================
   GRAVAR NA BASE DE DADOS
===================================================== */
try {
    $pdo->beginTransaction();

    // Password temporária inicial.
    // Se quiseres segurança a sério, deves obrigar mudança no primeiro login.
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

    $userId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO funcionario
        (
            user_id,
            nome,
            data_nascimento,
            contato,
            bi,
            email,
            morada,
            nacionalidade,
            nif,
            genero,
            distrito,
            freguesia,
            cargo,
            tipo_c,
            h_profissional,
            h_academica
        )
        VALUES
        (
            :user_id,
            :nome,
            :data,
            :contato,
            :bi,
            :email,
            :morada,
            :nacionalidade,
            :nif,
            :genero,
            :distrito,
            :freguesia,
            :cargo,
            :tipo_contrato,
            :h_profissional,
            :h_academica
        )
    ");
    $stmt->execute([
        ':user_id' => $userId,
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
        ':cargo' => $cargo,
        ':tipo_contrato' => $t_contrato,
        ':h_profissional' => $h_profissional,
        ':h_academica' => $h_academica
    ]);

    $pdo->commit();

    $_SESSION['alerta_funcionario'] = [
        'tipo' => 'success',
        'msg' => 'Funcionário registado com sucesso.'
    ];

    unset($_SESSION['csrf_token_funcionario']);
    unset($_SESSION['old_funcionario']);
    unset($_SESSION['tinha_foto_funcionario']);

    header('Location: form_funcionario.php');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    limparFotoSeExistir($fotoPath);

    if ($e->getCode() === '23000') {
        $mensagemErro = strtolower($e->getMessage());

        if (str_contains($mensagemErro, 'username') || str_contains($mensagemErro, 'email')) {
            try {
                limparUserOrfaoPorEmail($pdo, $email);
            } catch (Throwable $cleanupError) {
                logErro('Falha ao limpar user órfão: ' . $cleanupError->getMessage());
            }

            redirecionarComErroUtilizador('O email já está registado. Tente outro.');
        }

        if (str_contains($mensagemErro, 'bi')) {
            redirecionarComErroUtilizador('O BI já está registado. Tente outro.');
        }

        if (str_contains($mensagemErro, 'nif')) {
            redirecionarComErroUtilizador('O NIF já está registado. Tente outro.');
        }

        if (str_contains($mensagemErro, 'contato') || str_contains($mensagemErro, 'contacto')) {
            redirecionarComErroUtilizador('O contacto já está registado. Tente outro.');
        }

        redirecionarComErroUtilizador('Funcionário já registado.');
    }

    redirecionarComErroTecnico('Erro BD em salvar_funcionario.php: ' . $e->getMessage());
}