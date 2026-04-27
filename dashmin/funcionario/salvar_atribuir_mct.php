<?php

declare(strict_types=1);

session_start();
include '../conexao.php';

function voltar(string $tipo, string $msg, int $idModulo = 0): void
{
    $_SESSION['alerta_mct'] = [
        'tipo' => $tipo,
        'msg'  => $msg
    ];

    $url = 'index.php?page=atribuir_mct';

    if ($idModulo > 0) {
        $url .= '&id=' . $idModulo;
    }

    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltar('danger', 'Pedido inválido.');
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    voltar('warning', 'Sessão expirada. Recarrega o formulário.');
}

$idTurma     = (int) ($_POST['id_turma'] ?? 0);
$idModulo    = (int) ($_POST['id_modulo'] ?? 0);
$idProfessor = (int) ($_POST['id_professor'] ?? 0);
$estado      = trim($_POST['estado'] ?? 'ativo');

$estadosPermitidos = ['ativo', 'concluido', 'pendente'];

if ($idModulo <= 0) {
    voltar('warning', 'Escolhe um módulo.');
}

if ($idTurma <= 0) {
    voltar('warning', 'Escolhe uma turma.', $idModulo);
}

if ($idProfessor <= 0) {
    voltar('warning', 'Escolhe um professor.', $idModulo);
}

if (!in_array($estado, $estadosPermitidos, true)) {
    voltar('warning', 'Estado inválido.', $idModulo);
}

try {
    /*
    |--------------------------------------------------------------------------
    | Verificar se o módulo existe e a que curso pertence
    |--------------------------------------------------------------------------
    */
    $stmtModulo = $pdo->prepare("
        SELECT id, id_curso
        FROM modulo
        WHERE id = :id_modulo
        LIMIT 1
    ");
    $stmtModulo->execute([
        ':id_modulo' => $idModulo
    ]);

    $modulo = $stmtModulo->fetch(PDO::FETCH_ASSOC);

    if (!$modulo) {
        voltar('warning', 'O módulo escolhido não existe.');
    }

    if ($modulo['id_curso'] === null) {
        voltar('warning', 'Este módulo não está associado a nenhum curso.', $idModulo);
    }

    $idCursoModulo = (int) $modulo['id_curso'];

    /*
    |--------------------------------------------------------------------------
    | Verificar se a turma pertence ao curso do módulo
    |--------------------------------------------------------------------------
    */
    $stmtTurma = $pdo->prepare("
        SELECT id, id_curso
        FROM turma
        WHERE id = :id_turma
        LIMIT 1
    ");
    $stmtTurma->execute([
        ':id_turma' => $idTurma
    ]);

    $turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        voltar('warning', 'A turma escolhida não existe.', $idModulo);
    }

    if ((int) $turma['id_curso'] !== $idCursoModulo) {
        voltar(
            'warning',
            'A turma escolhida não pertence ao curso associado a este módulo.',
            $idModulo
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar se o professor existe
    |--------------------------------------------------------------------------
    */
    $stmtProfessor = $pdo->prepare("
        SELECT id
        FROM professor
        WHERE id = :id_professor
        LIMIT 1
    ");
    $stmtProfessor->execute([
        ':id_professor' => $idProfessor
    ]);

    if (!$stmtProfessor->fetch(PDO::FETCH_ASSOC)) {
        voltar('warning', 'O professor escolhido não existe.', $idModulo);
    }

    /*
    |--------------------------------------------------------------------------
    | Evitar duplicados
    |--------------------------------------------------------------------------
    */
    $stmtDuplicado = $pdo->prepare("
        SELECT id_relacao
        FROM relacao
        WHERE id_turma = :id_turma
          AND id_modulo = :id_modulo
          AND id_professor = :id_professor
        LIMIT 1
    ");
    $stmtDuplicado->execute([
        ':id_turma'     => $idTurma,
        ':id_modulo'    => $idModulo,
        ':id_professor' => $idProfessor
    ]);

    if ($stmtDuplicado->fetch(PDO::FETCH_ASSOC)) {
        voltar('warning', 'Esta atribuição já existe.', $idModulo);
    }

    /*
    |--------------------------------------------------------------------------
    | Inserir atribuição
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        INSERT INTO relacao
            (id_turma, id_modulo, id_professor, estado)
        VALUES
            (:id_turma, :id_modulo, :id_professor, :estado)
    ");

    $stmt->execute([
        ':id_turma'     => $idTurma,
        ':id_modulo'    => $idModulo,
        ':id_professor' => $idProfessor,
        ':estado'       => $estado
    ]);

    unset($_SESSION['csrf_token']);

    voltar('success', 'Módulo atribuído com sucesso.', $idModulo);

} catch (PDOException $e) {
    voltar('danger', 'Erro ao guardar a atribuição.', $idModulo);
}