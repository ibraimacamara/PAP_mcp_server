<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

date_default_timezone_set('Europe/Lisbon');
session_start();

require_once 'conexao.php';



$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

define('LOG_FILE', $logDir . '/app.log');

function logErro(string $mensagem): void
{
    error_log(
        '[' . date('Y-m-d H:i:s') . '] ' . $mensagem . PHP_EOL,
        3,
        LOG_FILE
    );
}




function erroUtilizador(string $mensagem): void
{
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'msg' => $mensagem
    ];
    header('Location: form_encarregado.php');
    exit;
}



function erroTecnico(string $logMsg, int $httpCode = 500): void
{
    logErro($logMsg);
    http_response_code($httpCode);

    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'msg' => 'Ocorreu um erro interno. Tente novamente mais tarde.'
    ];

    header('Location: form_encarregado.php');
    exit;
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    erroTecnico('Método HTTP inválido', 405);
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    erroUtilizador('Sessão expirada. Recarregue o formulário.');
}



$nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$bi = trim(filter_input(INPUT_POST, 'bi', FILTER_SANITIZE_SPECIAL_CHARS));
$morada = trim(filter_input(INPUT_POST, 'morada', FILTER_SANITIZE_SPECIAL_CHARS));
$contato = trim(filter_input(INPUT_POST, 'contato', FILTER_SANITIZE_NUMBER_INT));
$genero = $_POST['genero'] ?? '';
$distrito = trim(filter_input(INPUT_POST, 'distrito', FILTER_SANITIZE_SPECIAL_CHARS));
$freguesia = trim(filter_input(INPUT_POST, 'freguesia', FILTER_SANITIZE_SPECIAL_CHARS));



if (
    $nome === '' || $email === '' || $bi === '' ||
    $morada === '' || $contato === '' || $genero === '' ||
    $distrito === '' || $freguesia === ''
) {
    erroUtilizador('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    erroUtilizador('Email inválido.');
}



try {
    
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
    $categoria = "encarregado";
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

    $sql = "
        INSERT INTO encarregado
        (user_id, nome, email, bi, morada, contato, genero, distrito, freguesia)
        VALUES
        (:user_id, :nome, :email, :bi, :morada, :contato, :genero, :distrito, :freguesia)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $userId,
        ':nome' => $nome,
        ':email' => $email,
        ':bi' => $bi,
        ':morada' => $morada,
        ':contato' => $contato,
        ':genero' => $genero,
        ':distrito' => $distrito,
        ':freguesia' => $freguesia
    ]);


    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'msg' => 'Encarregado registado com sucesso.'
    ];

} catch (PDOException $e) {

    if ($e->getCode() === '23000') {
        erroUtilizador('Já existe este encarregado.');
    }

    erroTecnico('Erro BD: ' . $e->getMessage());
}



unset($_SESSION['csrf_token']);
header('Location: form_encarregado.php');
exit;
