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
    header('Location: form_aluno.php');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);
    $_SESSION['alerta'] = ['tipo' => 'danger', 'msg' => 'Ocorreu um erro interno.'];
    header('Location: form_aluno.php');
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
$genero = $_POST['genero'] ?? '';
$distrito = trim($_POST['distrito'] ?? '');
$freguesia = trim($_POST['freguesia'] ?? '');

$cursoId = (int) ($_POST['curso_id'] ?? 0);
$turmaId = (int) ($_POST['turma_id'] ?? 0);

$encarregadoPrincipalId = (int) ($_POST['encarregado_principal_id'] ?? 0);
$lacoPrincipal = trim($_POST['laco_principal'] ?? '');

$encarregadoSecundarioId = (int) ($_POST['encarregado_secundario_id'] ?? 0);
$lacoSecundario = trim($_POST['laco_secundario'] ?? '');



//VALIDAÇÃO

if (!$nome || !$dataNasc || !$contato || !$bi || !$email || !$cursoId || !$turmaId) {
    erroUtilizador('Preencha todos os campos obrigatórios.');
}

if (empty($encarregadoPrincipalId) || empty($lacoPrincipal)) {
    erroUtilizador('Preencha o encarregado principal e o laço familiar.');
}

if (!empty($encarregadoSecundarioId) && empty($lacoSecundario)) {
    erroUtilizador('Informe o laço familiar do encarregado secundário.');
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
    $novoNome = 'aluno_' . $bi . '.' . $extensao;
    $destino = $uploadDir . $novoNome;


    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        erroUtilizador('Falha ao guardar a foto.');
    }

    $fotoPath = $novoNome;
}



/* =====================================================
   TRANSAÇÃO
===================================================== */
try {
    $pdo->beginTransaction();

    // users
    $partesNome = explode(' ', trim($nome));
    $apelido = end($partesNome);

    // Primeira letra maiúscula (suporte a acentos)
    $apelidoFormatado = mb_convert_case($apelido, MB_CASE_TITLE, 'UTF-8');

    // Ano atual
    $anoAtual = date('Y');

    // Senha final
    $senhaOriginal = $apelidoFormatado . $anoAtual . '#';

    // Hash da senha (RECOMENDADO)
    $senhaHash = password_hash($senhaOriginal, PASSWORD_DEFAULT);
    $categoria = "aluno";
    $stmt = $pdo->prepare("
    INSERT INTO users (email, senha, categoria, foto)
    VALUES (:email, :senha, :categoria, :foto)
    ");
    $stmt->execute([
        ':email' => $email,
        ':senha' => $senhaHash,
        ':categoria' => $categoria,
        ':foto' => $fotoPath
    ]);
    $userId = (int) $pdo->lastInsertId();

    // Inserir aluno
    $stmt = $pdo->prepare("
        INSERT INTO aluno 
        (user_id, nome, data_nascimento, contato, bi, email, morada, genero, distrito, freguesia)
        VALUES (:user_id, :nome, :data, :contato, :bi, :email, :morada, :genero, :distrito, :freguesia )
    ");
    $stmt->execute([
        'user_id' => $userId,
        ':nome' => $nome,
        ':data' => $dataNasc,
        ':contato' => $contato,
        ':bi' => $bi,
        ':email' => $email,
        ':morada' => $morada,
        ':genero' => $genero,
        ':distrito' => $distrito,
        ':freguesia' => $freguesia,
        
    ]);
    $alunoId = (int) $pdo->lastInsertId();

    // Relação com curso
    $stmt = $pdo->prepare("INSERT INTO aluno_curso (numero_aluno, curso_id) VALUES (:aluno, :curso)");
    $stmt->execute([':aluno' => $alunoId, ':curso' => $cursoId]);

    // Relação com turma
    $stmt = $pdo->prepare("INSERT INTO aluno_turma (numero_aluno, turma_id) VALUES (:aluno, :turma)");
    $stmt->execute([':aluno' => $alunoId, ':turma' => $turmaId]);




    $userId = (int) $pdo->lastInsertId();


    // Encarregado principal
    $stmt = $pdo->prepare("INSERT INTO aluno_encarregado (numero_aluno, encarregado_id, laco_familiar) VALUES (:aluno, :encarregado, :laco)");
    $stmt->execute([
        ':aluno' => $alunoId,
        ':encarregado' => $encarregadoPrincipalId,
        ':laco' => $lacoPrincipal
    ]);

    // Encarregado secundário (opcional)
    if ($encarregadoSecundarioId) {
        $stmt->execute([
            ':aluno' => $alunoId,
            ':encarregado' => $encarregadoSecundarioId,
            ':laco' => $lacoSecundario
        ]);
    }

    $pdo->commit();

    $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Aluno registado com sucesso.'];

} catch (PDOException $e) {
    $pdo->rollBack();

    // Remove foto se já tiver sido movida
    if ($fotoPath && file_exists(__DIR__ . '/' . $fotoPath)) {
        unlink(__DIR__ . '/' . $fotoPath);
    }

    if ($e->getCode() === '23000') {
        erroUtilizador('Aluno já registado.');
    }

    erroTecnico('Erro BD: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
header('Location: form_aluno.php');
exit;
