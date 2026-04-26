<?php

declare(strict_types=1);

date_default_timezone_set('Europe/Lisbon');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

if ($decoded === null && trim($response) === 'null') {
    return [];
}

if (!is_array($decoded)) {
    throw new Exception('Resposta inválida do Moodle: ' . $response);
}

if (isset($decoded['exception'])) {
    throw new Exception('Erro Moodle: ' . json_encode($decoded, JSON_UNESCAPED_UNICODE));
}

return $decoded;
}
function criarOuObterAlunoMoodle(array $dados): int
{
    $email = strtolower(trim($dados['email']));

    $existente = moodleRequest('core_user_get_users_by_field', [
        'field' => 'email',
        'values[0]' => $email
    ]);

    if (!empty($existente[0]['id'])) {
        return (int) $existente[0]['id'];
    }

    $partesNome = preg_split('/\s+/', trim($dados['nome']), 2);
    $firstname = $partesNome[0] ?? 'Aluno';
    $lastname = $partesNome[1] ?? 'Sem apelido';

    $biNumerico = preg_replace('/[^0-9]/', '', $dados['bi']);
    $password = 'Aluno@' . ($biNumerico !== '' ? $biNumerico : time()) . 'a';

    $criado = moodleRequest('core_user_create_users', [
        'users[0][username]' => $email,
        'users[0][password]' => $password,
        'users[0][firstname]' => $firstname,
        'users[0][lastname]' => $lastname,
        'users[0][email]' => $email,
        'users[0][auth]' => 'manual',
        'users[0][idnumber]' => $dados['bi'],
        'users[0][city]' => $dados['distrito'],
        'users[0][country]' => 'PT'
    ]);

    if (empty($criado[0]['id'])) {
        throw new Exception('Moodle não devolveu o ID do aluno criado.');
    }

    return (int) $criado[0]['id'];
}

function obterCursoTurmaMoodle(PDO $pdo, int $cursoId, int $turmaId): array
{
    $stmt = $pdo->prepare("
        SELECT 
            c.moodle_course_id,
            t.moodle_group_id
        FROM curso c
        INNER JOIN turma t ON t.curso_id = c.id
        WHERE c.id = :curso_id
          AND t.id = :turma_id
        LIMIT 1
    ");

    $stmt->execute([
        ':curso_id' => $cursoId,
        ':turma_id' => $turmaId
    ]);

    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        throw new Exception('Curso ou turma inválidos.');
    }

    if (empty($dados['moodle_course_id'])) {
        throw new Exception('O curso ainda não está sincronizado com o Moodle.');
    }

    if (empty($dados['moodle_group_id'])) {
        throw new Exception('A turma ainda não está sincronizada com o Moodle.');
    }

    return [
        'moodle_course_id' => (int) $dados['moodle_course_id'],
        'moodle_group_id' => (int) $dados['moodle_group_id']
    ];
}

function inscreverAlunoNoCursoMoodle(int $moodleUserId, int $moodleCourseId): void
{
    moodleRequest('enrol_manual_enrol_users', [
        'enrolments[0][roleid]' => 5,
        'enrolments[0][userid]' => $moodleUserId,
        'enrolments[0][courseid]' => $moodleCourseId
    ]);
}

function adicionarAlunoAoGrupoMoodle(int $moodleUserId, int $moodleGroupId): void
{
    moodleRequest('core_group_add_group_members', [
        'members[0][groupid]' => $moodleGroupId,
        'members[0][userid]' => $moodleUserId
    ]);
}

function erroUtilizador(string $mensagem, array $dados = [], bool $tinhaFoto = false): void
{
    $_SESSION['alerta_aluno_inserir'] = [
        'tipo' => 'warning',
        'msg' => $mensagem
    ];

    $_SESSION['old_aluno'] = $dados;
    $_SESSION['tinha_foto_aluno'] = $tinhaFoto;

    header('Location: index.php?page=form_aluno');
    exit;
}

function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);

    http_response_code($httpCode);

    $_SESSION['alerta_aluno_inserir'] = [
        'tipo' => 'danger',
        'msg' => 'Ocorreu um erro interno.'
    ];

    unset($_SESSION['old_aluno'], $_SESSION['tinha_foto_aluno']);

    header('Location: index.php?page=form_aluno');
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
    $dados['nome'] === '' ||
    $dados['data_nascimento'] === '' ||
    $dados['contato'] === '' ||
    $dados['bi'] === '' ||
    $dados['email'] === '' ||
    $dados['morada'] === '' ||
    $dados['genero'] === '' ||
    $dados['distrito'] === '' ||
    $dados['freguesia'] === '' ||
    $dados['curso_id'] <= 0 ||
    $dados['turma_id'] <= 0
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

    if (
        !isset($foto['tmp_name']) ||
        !is_uploaded_file($foto['tmp_name']) ||
        $foto['error'] !== UPLOAD_ERR_OK
    ) {
        erroUtilizador('Erro no upload da foto.', $dados, true);
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

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
    $moodle = obterCursoTurmaMoodle(
        $pdo,
        $dados['curso_id'],
        $dados['turma_id']
    );

    $moodleUserId = criarOuObterAlunoMoodle($dados);

    inscreverAlunoNoCursoMoodle(
        $moodleUserId,
        $moodle['moodle_course_id']
    );

    adicionarAlunoAoGrupoMoodle(
        $moodleUserId,
        $moodle['moodle_group_id']
    );

    $pdo->beginTransaction();

    $senhaHash = password_hash($dados['bi'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (username, senha, categoria, foto)
        VALUES 
            (:username, :senha, :categoria, :foto)
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
            (
                user_id, nome, data_nascimento, contato, bi, email, morada, genero,
                distrito, freguesia, curso_id, turma_id, encarregado_principal_id,
                encarregado_secundario_id, moodle_user_id
            )
        VALUES 
            (
                :user_id, :nome, :data_nascimento, :contato, :bi, :email, :morada, :genero,
                :distrito, :freguesia, :curso_id, :turma_id, :encarregado_principal_id,
                :encarregado_secundario_id, :moodle_user_id
            )
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':nome' => $dados['nome'],
        ':data_nascimento' => $dados['data_nascimento'],
        ':contato' => $dados['contato'],
        ':bi' => $dados['bi'],
        ':email' => $dados['email'],
        ':morada' => $dados['morada'],
        ':genero' => $dados['genero'],
        ':distrito' => $dados['distrito'],
        ':freguesia' => $dados['freguesia'],
        ':curso_id' => $dados['curso_id'],
        ':turma_id' => $dados['turma_id'],
        ':encarregado_principal_id' => $dados['encarregado_principal_id'],
        ':encarregado_secundario_id' => $dados['encarregado_secundario_id'] > 0
            ? $dados['encarregado_secundario_id']
            : null,
        ':moodle_user_id' => $moodleUserId
    ]);

    $pdo->commit();

    $_SESSION['alerta_aluno_inserir'] = [
        'tipo' => 'success',
        'msg' => 'Aluno registado com sucesso, inscrito no curso e associado à turma no Moodle.'
    ];

    unset(
        $_SESSION['old_aluno'],
        $_SESSION['tinha_foto_aluno'],
        $_SESSION['csrf_token_aluno']
    );

    header('Location: index.php?page=form_aluno');
    exit;

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
        $msg = strtolower($e->getMessage());

        if (str_contains($msg, 'username') || str_contains($msg, 'email')) {
            erroUtilizador('Email já registado.', $dados, $tinhaFoto);
        }

        if (str_contains($msg, 'bi')) {
            erroUtilizador('BI já registado.', $dados, $tinhaFoto);
        }

        erroUtilizador('Já existe um registo com os dados informados.', $dados, $tinhaFoto);
    }

    erroTecnico('Erro ao guardar aluno/Moodle em salvar_aluno.php: ' . $e->getMessage());
}