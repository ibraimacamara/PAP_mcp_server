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



$curso_id = (int) ($_POST['curso_id'] ?? 0);
$codigo    = trim($_POST['codigo'] ?? '');
$ciclo_formacao = trim($_POST['ciclo_formacao'] ?? '');



if ($curso_id === '' || $codigo === '' || $ciclo_formacao === '') {
    erroUtilizador('Preencha todos os campos obrigatórios.');
}

if (!preg_match('/^[A-Z]{2,10}\s[1-9]º\s*ano(\s[A-Z])?$/iu', $codigo)) {
    erroUtilizador('Formato do código inválido. Exemplo: PI 1ºano ou PI 1ºano A');
}

try {
    $sql = "
        INSERT INTO turma (curso_id, codigo, ciclo_formacao)
        VALUES (:curso_id, :codigo, :ciclo_formacao)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':curso_id'      => $curso_id,
        ':codigo'    => $codigo,
        ':ciclo_formacao' => $ciclo_formacao
    ]);

    $_SESSION['alerta'] = [
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
header('Location: forms.php');
exit;
