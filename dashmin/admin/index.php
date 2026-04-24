<?php
declare(strict_types=1);

session_start();

function redirecionar(string $destino): void
{
    header("Location: $destino");
    exit;
}

function estaAutenticado(): bool
{
    return !empty($_SESSION['auth']) && $_SESSION['auth'] === true;
}

function eAdmin(): bool
{
    return isset($_SESSION['categoria']) &&
        strtolower((string) $_SESSION['categoria']) === 'admin';
}

if (!estaAutenticado() || !eAdmin()) {
    session_unset();
    session_destroy();
    redirecionar('../login.php');
}

/*
|--------------------------------------------------------------------------
| PRIMEIRO LOGIN
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SISTEMA DE REDIRECIONAMENTO
|--------------------------------------------------------------------------
*/

$page = $_GET['page'] ?? 'home';

$rotas = [
    'home' => 'home.php',
    'form_aluno' => 'form_aluno.php',
    'lista_aluno' => 'lista_aluno.php',
    'editar_aluno' => 'editar_aluno.php',
    'detalhe_aluno' => 'detalhe_aluno.php',
    'remover_aluno' => 'remover_aluno.php',
    'salvar_aluno' => 'salvar_aluno.php',

    'form_encarregado' => 'form_encarregado.php',
    'lista_encarregado' => 'lista_encarregado.php',
    'editar_encarregado' => 'editar_encarregado.php',
    'remover_encarregado' => 'remover_encarregado.php',
    'salvar_encarregado' => 'salvar_encarregado.php',

    'form_professor' => 'form_prof.php',
    'lista_professor' => 'lista_prof.php',
    'editar_professor' => 'editar_prof.php',
    'detalhe_professor' => 'detalhe_prof.php',
    'remover_professor' => 'remover_prof.php',
    'salvar_professor' => 'salvar_prof.php',
    'atualizar_professor' => 'atualizar_prof.php',

    'curso_turma' => 'curso_turma.php',
    'lista_curso' => 'lista_curso.php',
    'editar_curso' => 'editar_curso.php',
    'remover_curso' => 'remover_curso.php',
    'salvar_curso' => 'salvar_curso.php',

    'lista_turma' => 'lista_turma.php',
    'editar_turma' => 'editar_turma.php',
    'remover_turma' => 'remover_turma.php',
    'salvar_turma' => 'salvar_turma.php',

    'form_modulo' => 'form_modulo.php',
    'lista_modulo' => 'lista_modulo.php',
    'editar_modulo' => 'editar_modulo.php',
    'remover_modulo' => 'remover_modulo.php',
    'salvar_modulo' => 'salvar_modulo.php',

    'form_funcionario' => 'form_funcionario.php',
    'lista_funcionario' => 'lista_funcionario.php',
    'editar_funcionario' => 'editar_funcionario.php',
    'detalhe_funcionario' => 'detalhe_funcionario.php',
    'remover_funcionario' => 'remover_funcionario.php',
    'salvar_funcionario' => 'salvar_funcionario.php',

    'editar_user' => 'editar_user.php',
    'atualizar_user' => 'atualizar_user.php',
    'logout' => 'logout.php'
];

// 1. Verifica se a página existe no array, se não, define como home
if (!array_key_exists($page, $rotas)) {
    $page = 'home';
}

$arquivoDestino = $rotas[$page];

// 2. IMPORTANTE: Os parâmetros (id, etc) já estão no $_GET. 
// Não precisamos concatená-los, pois o arquivo incluído herdará o $_GET atual.

if (file_exists($arquivoDestino)) {
    // Aqui você carrega o conteúdo sem mudar a barra de endereço
    include $arquivoDestino;
} else {
    // Caso o arquivo físico não exista
    echo "Erro: O arquivo correspondente à rota '$page' não foi encontrado.";
}

// Interrompe aqui para não carregar nada extra do index
exit;