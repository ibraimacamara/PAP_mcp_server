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
           strtolower((string)$_SESSION['categoria']) === 'professor';
}

if (!estaAutenticado() || !eAdmin()) {
    session_unset();
    session_destroy();
    redirecionar('../login.php');
}



/*
|--------------------------------------------------------------------------
| SISTEMA DE REDIRECIONAMENTO
|--------------------------------------------------------------------------
*/

$page = $_GET['page'] ?? 'home';

$rotas = [
    'home'              => 'home.php',
    'form_aluno'        => 'form_aluno.php',
    'lista_aluno'       => 'lista_aluno.php',
    'editar_aluno'      => 'editar_aluno.php',
    'detalhe_aluno'     => 'detalhe_aluno.php',
   

    'form_encarregado'  => 'form_encarregado.php',
    'lista_encarregado' => 'lista_encarregado.php',
    'editar_encarregado'=> 'editar_encarregado.php',
    'remover_encarregado'=> 'remover_encarregado.php',

    'form_professor'    => 'form_prof.php',
    'lista_professor'   => 'lista_prof.php',
    'editar_professor'  => 'editar_prof.php',
    'detalhe_professor' => 'detalhe_prof.php',
    'remover_professor'     => 'remover_prof.php',

    'curso_turma'       => 'curso_turma.php',
    'lista_curso'       => 'lista_curso.php',
    'editar_curso'      => 'editar_curso.php',
    'remover_curso'      => 'remover_curso.php',

    'lista_turma'       => 'lista_turma.php',
    'editar_turma'      => 'editar_turma.php',
    'remover_turma'      => 'remover_turma.php',



    'editar_user'       => 'editar_user.php',
    'atualizar_user'       => 'atualizar_user.php',
    'logout'            => 'logout.php'
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