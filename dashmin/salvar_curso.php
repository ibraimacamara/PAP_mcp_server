<?php


declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');
session_start();

require_once 'conexao.php';


define('LOG_FILE', __DIR__ . '/../logs/app.log');

function logErro(string $mensagem): void
{
    $data = date('Y-m-d H:i:s');
    error_log("[$data] $mensagem\n", 3, LOG_FILE);
}


function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'msg'  => $mensagem
    ];
    header('Location: forms.php');
    exit;
}



function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);

    http_response_code($httpCode);

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg'  => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: forms.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico(
        'Método HTTP inválido: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'),
        405
    );
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    erroUtilizador('Sessão expirada. Recarregue o formulário.');
}


$nome      = trim($_POST['nome'] ?? '');
$descricao    = trim($_POST['descricao'] ?? '');




$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        erroUtilizador('Erro no upload da foto.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif'
    ];

    if (!array_key_exists($foto['type'], $tiposPermitidos)) {
        erroUtilizador('Apenas imagens JPEG, PNG ou GIF são permitidas.');
    }

    $uploadDir = __DIR__ . '/../uploads_curso';
    if (!is_dir($uploadDir)) {
        erroUtilizador('A pasta de uploads não existe.');
    }

    // Normalizar nome do curso
    $slugCurso = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nome)));

    // Adiciona timestamp para evitar sobrescrita
    $extensao = $tiposPermitidos[$foto['type']];
    $novoNome = 'curso_' . $slugCurso . '_' . time() . '.' . $extensao;

    $destino = $uploadDir . '/' . $novoNome;

    if (!move_uploaded_file($foto['tmp_name'], $destino)) {
        erroUtilizador('Falha ao guardar a foto.');
    }

    $fotoPath = $novoNome;
}



if ($nome === ''  ) {
    erroUtilizador('Inseri o nome de curso.');
}

if (mb_strlen($nome) < 3) {
    erroUtilizador('O nome do curso é demasiado curto.');
}




try {
    $sql = "
        INSERT INTO curso (nome, descricao, imagem )
        VALUES (:nome, :descricao, :imagem)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'      => $nome,
        ':descricao'    => $descricao,
        ':imagem'    => $fotoPath
    ]);

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg'  => 'Curso registado com sucesso.'
    ];

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        erroUtilizador('Já existe um turma com esse nome.');
    }

    erroTecnico('Erro DB: ' . $e->getMessage());
}


unset($_SESSION['csrf_token']);
header('Location: forms.php');
exit;
