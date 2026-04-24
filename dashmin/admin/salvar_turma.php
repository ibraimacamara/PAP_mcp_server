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
    $token = 'e8401f0e06e5e7886ed1222c67589c09';

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
        throw new Exception('Erro Moodle: ' . json_encode($decoded, JSON_UNESCAPED_UNICODE));
    }

    return $decoded;
}

function gerarIdNumberTurma(int $cursoId, string $codigo): string
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $codigo), '_'));
    return 'turma_' . $cursoId . '_' . $slug;
}

function criarTurmaNoMoodle(PDO $pdo, int $cursoId, string $codigo, string $cicloFormacao): int
{
    $stmt = $pdo->prepare("
        SELECT nome, moodle_category_id
        FROM curso
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $cursoId]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$curso) {
        throw new Exception('Curso não encontrado.');
    }

    if (empty($curso['moodle_category_id'])) {
        throw new Exception('Este curso ainda não está sincronizado com o Moodle.');
    }

    $nomeTurma = $codigo . ' - ' . $curso['nome'];
    $idnumber = gerarIdNumberTurma($cursoId, $codigo);

    $resposta = moodleRequest('core_cohort_create_cohorts', [
        'cohorts[0][categorytype][type]' => 'id',
        'cohorts[0][categorytype][value]' => (int) $curso['moodle_category_id'],
        'cohorts[0][name]' => $nomeTurma,
        'cohorts[0][idnumber]' => $idnumber,
        'cohorts[0][description]' => $cicloFormacao,
        'cohorts[0][descriptionformat]' => 1,
        'cohorts[0][visible]' => 1
    ]);

    if (empty($resposta[0]['id'])) {
        throw new Exception('Moodle não devolveu o ID da coorte criada.');
    }

    return (int) $resposta[0]['id'];
}

function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta_turma'] = [
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

    $_SESSION['alerta_turma'] = [
        'tipo' => 'danger',
        'msg' => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: index.php?page=curso_turma');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'), 405);
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    erroUtilizador('Sessão expirada. Recarregue o formulário.');
}

$curso_id = (int) ($_POST['curso_id'] ?? 0);
$codigo = trim($_POST['codigo'] ?? '');
$ciclo_formacao = trim($_POST['ciclo_formacao'] ?? '');
$diretorId = (int) ($_POST['diretor_id'] ?? 0);

if ($curso_id <= 0 || $codigo === '' || $ciclo_formacao === '') {
    erroUtilizador('Preencha todos os campos obrigatórios.');
}

if (!preg_match('/^[A-Z]{2,10}\s[1-9]º\s*ano(\s[A-Z])?$/iu', $codigo)) {
    erroUtilizador('Formato do código inválido. Exemplo: PI 1ºano ou PI 1ºano A');
}

try {
    $pdo->beginTransaction();

    $idnumber = gerarIdNumberTurma($curso_id, $codigo);

    $moodleCohortId = criarTurmaNoMoodle(
        $pdo,
        $curso_id,
        $codigo,
        $ciclo_formacao
    );

    $sql = "
        INSERT INTO turma 
        (curso_id, codigo, ciclo_formacao, diretor, moodle_cohort_id, moodle_cohort_idnumber)
        VALUES 
        (:curso_id, :codigo, :ciclo_formacao, :diretor, :moodle_cohort_id, :moodle_cohort_idnumber)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':curso_id' => $curso_id,
        ':codigo' => $codigo,
        ':ciclo_formacao' => $ciclo_formacao,
        ':diretor' => $diretorId > 0 ? $diretorId : null,
        ':moodle_cohort_id' => $moodleCohortId,
        ':moodle_cohort_idnumber' => $idnumber
    ]);

    $pdo->commit();

    $_SESSION['alerta_turma'] = [
        'tipo' => 'success',
        'msg' => 'Turma registada com sucesso e sincronizada com o Moodle.'
    ];

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e instanceof PDOException && $e->getCode() === '23000') {
        erroUtilizador('Já existe uma turma com esse código neste curso.');
    }

    erroTecnico('Erro ao guardar turma/Moodle: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);

header('Location: index.php?page=curso_turma');
exit;