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
    $_SESSION['alerta_modulo_inserir'] = [
        'tipo' => 'warning',
        'msg'  => $mensagem
    ];

    header('Location: index.php?page=form_modulo');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);

    http_response_code($httpCode);

    $_SESSION['alerta_modulo_inserir'] = [
        'tipo' => 'danger',
        'msg'  => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: index.php?page=form_modulo');
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

$idCurso      = (int) ($_POST['id_curso'] ?? 0);
$nomeModulo   = trim($_POST['nome_modulo'] ?? '');
$codigoModulo = trim($_POST['codigo_modulo'] ?? '');
$ordem        = (int) ($_POST['ordem'] ?? 0);
$cargaHoraria = (int) ($_POST['carga_horaria'] ?? 0);

if ($idCurso <= 0) {
    erroUtilizador('Seleciona um curso.');
}

if ($nomeModulo === '') {
    erroUtilizador('Insere o nome do módulo.');
}

if (mb_strlen($nomeModulo) < 2) {
    erroUtilizador('O nome do módulo é demasiado curto.');
}


if ($ordem <= 0) {
    erroUtilizador('A ordem tem de ser maior que zero.');
}

if ($cargaHoraria < 0) {
    erroUtilizador('A carga horária não pode ser menor igual a zero');
}

try {
    $sql = "
        INSERT INTO modulo (
            id_curso,
            nome_modulo,
            codigo_modulo,
            ordem,
            carga_horaria
        )
        VALUES (
            :id_curso,
            :nome_modulo,
            :codigo_modulo,
            :ordem,
            :carga_horaria
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_curso'      => $idCurso,
        ':nome_modulo'   => $nomeModulo,
        ':codigo_modulo' => $codigoModulo !== '' ? $codigoModulo : null,
        ':ordem'         => $ordem,
        ':carga_horaria' => $cargaHoraria
    ]);

    $_SESSION['alerta_modulo_inserir'] = [
        'tipo' => 'success',
        'msg'  => 'Módulo registado com sucesso.'
    ];

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        erroUtilizador('Já existe um módulo com esse nome nesse curso.');
    }

    erroTecnico('Erro DB ao registar módulo: ' . $e->getMessage());
}

unset($_SESSION['csrf_token']);
header('Location:index.php?page=form_modulo');
exit;