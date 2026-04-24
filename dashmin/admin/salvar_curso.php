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

function moodleRequest(string $function, array $params): array
{
    $moodleUrl = 'https://ibraima.sieno.pt/sgei/webservice/rest/server.php';
    $token = '';

    $postFields = array_merge([
        'wstoken' => $token,
        'wsfunction' => $function,
        'moodlewsrestformat' => 'json'
    ], $params);

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $moodleUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postFields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $erro = curl_error($ch);
        curl_close($ch);
        throw new Exception('Erro cURL Moodle: ' . $erro);
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        throw new Exception('Resposta inválida do Moodle: ' . $response);
    }

    if (isset($decoded['exception'])) {
        throw new Exception('Erro Moodle: ' . ($decoded['message'] ?? 'Erro desconhecido'));
    }

    return $decoded;
}

function criarCursoNoMoodle(string $nome, string $descricao = ''): int
{
    $resposta = moodleRequest('core_course_create_categories', [
        'categories[0][name]' => $nome,
        'categories[0][description]' => $descricao,
        'categories[0][descriptionformat]' => 1
    ]);

    if (empty($resposta[0]['id'])) {
        throw new Exception('Moodle não devolveu o ID da categoria criada.');
    }

    return (int) $resposta[0]['id'];
}

function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta_curso'] = [
        'tipo' => 'warning',
        'msg' => $mensagem
    ];
    header('Location: index.php?page=curso_turma');
    exit;
}



function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);

    http_response_code($httpCode);

    $_SESSION['alerta_curso'] = [
        'tipo' => 'danger',
        'msg' => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: index.php?page=curso_turma');
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


$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$coordenadorId = (int) ($_POST['coordenador_id'] ?? 0);




$fotoPath = null;

if (!empty($_FILES['foto']['name'])) {
    $foto = $_FILES['foto'];

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        erroUtilizador('Erro no upload da foto.');
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!array_key_exists($foto['type'], $tiposPermitidos)) {
        erroUtilizador('Apenas imagens JPEG, PNG ou GIF são permitidas.');
    }

    $uploadDir = '../uploads';
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



if ($nome === '') {
    erroUtilizador('Inseri o nome de curso.');
}

if (mb_strlen($nome) < 3) {
    erroUtilizador('O nome do curso é demasiado curto.');
}


try {
    $pdo->beginTransaction();

    $moodleCategoryId = criarCursoNoMoodle($nome, $descricao);

    $sql = "
        INSERT INTO curso (nome, descricao, coordenador, imagem, moodle_category_id)
        VALUES (:nome, :descricao, :coordenador, :imagem, :moodle_category_id)
    ";

    $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':nome'               => $nome,
    ':descricao'          => $descricao,
    ':coordenador'        => $coordenadorId > 0 ? $coordenadorId : null,
    ':imagem'             => $fotoPath,
    ':moodle_category_id' => $moodleCategoryId
]);

$pdo->commit();

    $_SESSION['alerta_curso'] = [
        'tipo' => 'success',
        'msg' => 'Curso registado com sucesso.'
    ];
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($fotoPath) {
        $caminhoFoto = __DIR__ . '/../uploads/' . $fotoPath;
        if (file_exists($caminhoFoto)) {
            unlink($caminhoFoto);
        }
    }

    if ($e instanceof PDOException && $e->getCode() === '23000') {
        erroUtilizador('Já existe um curso com esse nome.');
    }

    erroTecnico('Erro ao guardar curso no Moodle: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
header('Location: index.php?page=curso_turma');
exit;
