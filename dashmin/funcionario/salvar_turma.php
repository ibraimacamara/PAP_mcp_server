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



function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta_turma'] = [
        'tipo' => 'warning',
        'msg'  => $mensagem
    ];
    header('Location: curso_turma.php');
    exit;
}



function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);

    http_response_code($httpCode);

    $_SESSION['alerta_turma'] = [
        'tipo' => 'danger',
        'msg'  => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: curso_turma.php');
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



$curso_id       = (int) ($_POST['curso_id'] ?? 0);
$codigo         = trim($_POST['codigo'] ?? '');
$ciclo_formacao = trim($_POST['ciclo_formacao'] ?? '');
$diretorId      = (int) ($_POST['diretor_id'] ?? 0);



if ($curso_id === '' || $codigo === '' || $ciclo_formacao === '') {
    erroUtilizador('Preencha todos os campos obrigatórios.');
}

if (!preg_match('/^[A-Z]{2,10}\s[1-9]º\s*ano(\s[A-Z])?$/iu', $codigo)) {
    erroUtilizador('Formato do código inválido. Exemplo: PI 1ºano ou PI 1ºano A');
}

try {
    $sql = "
        INSERT INTO turma (curso_id, codigo, ciclo_formacao, diretor)
        VALUES (:curso_id, :codigo, :ciclo_formacao, :diretor)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':curso_id'       => $curso_id,
        ':codigo'         => $codigo,
        ':ciclo_formacao' => $ciclo_formacao,
        ':diretor'        => $diretorId > 0 ? $diretorId : null
    ]);

    $_SESSION['alerta_turma'] = [
        'tipo' => 'success',
        'msg'  => 'Turma registado com sucesso.'
    ];

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        erroUtilizador('Já existe um turma com esse código.');
    }

    erroTecnico('Erro DB: ' . $e->getMessage());
}


unset($_SESSION['csrf_token']);
header('Location: curso_turma.php');
exit;
