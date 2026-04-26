<?php
session_start();
include('../conexao.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=lista_professor');
    exit;
}

/* ================================
   CSRF
================================ */
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['alerta_professor_editar'] = [
        'tipo' => 'danger',
        'msg' => 'Token CSRF inválido.'
    ];
    header('Location: index.php?page=lista_professor');
    exit;
}

/* ================================
   ID PROFESSOR
================================ */
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php?page=lista_professor');
    exit;
}

/* ================================
   BUSCAR USER_ID E FOTO ATUAL
================================ */
$stmt = $pdo->prepare("
    SELECT professor.user_id, users.foto
    FROM professor
    JOIN users ON users.id = professor.user_id
    WHERE professor.id = ?
");
$stmt->execute([$id]);

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    $_SESSION['alerta_professor_editar'] = [
        'tipo' => 'danger',
        'msg' => 'Professor não encontrado.'
    ];
    header('Location: index.php?page=lista_professor');
    exit;
}

$user_id   = $dados['user_id'];
$fotoAtual = $dados['foto'] ?? null;

/* ================================
   CAMPOS DO PROFESSOR
================================ */
$campos = [
    'nome',
    'email',
    'bi',
    'nif',
    'contato',
    'data_nascimento',
    'morada',
    'nacionalidade',
    'genero',
    'distrito',
    'freguesia',
    'grupo_d',
    'tipo_c',
    'h_profissional',
    'h_academica'
];

$set = [];
$valores = [];

foreach ($campos as $campo) {
    if (isset($_POST[$campo])) {
        $valor = trim($_POST[$campo]);

        if ($campo === 'email' && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alerta_professor_editar'] = [
                'tipo' => 'danger',
                'msg' => 'Email inválido.'
            ];
            header("Location: index.php?page=editar_professor&id=$id");
            exit;
        }

        if ($campo === 'genero' && $valor !== '' && !in_array($valor, ['Masculino', 'Feminino'], true)) {
            $_SESSION['alerta_professor_editar'] = [
                'tipo' => 'danger',
                'msg' => 'Género inválido.'
            ];
            header("Location: index.php?page=editar_professor&id=$id");
            exit;
        }

        $set[] = "`$campo` = ?";
        $valores[] = $valor;
    }
}

$temFoto = !empty($_FILES['foto']['name']);

if (empty($set) && !$temFoto) {
    $_SESSION['alerta_professor_editar'] = [
        'tipo' => 'warning',
        'msg' => 'Nenhum campo para atualizar.'
    ];
    header("Location: index.php?page=editar_professor&id=$id");
    exit;
}

try {
    $pdo->beginTransaction();

    if (!empty($set)) {
        $valores[] = $id;
        $sql = "UPDATE professor SET " . implode(', ', $set) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);
    }

    if ($temFoto) {
        $foto = $_FILES['foto'];

        if ($foto['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload da imagem.');
        }

        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif'
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($foto['tmp_name']);

        if (!array_key_exists($mime, $tiposPermitidos)) {
            throw new Exception('Formato de imagem inválido.');
        }

        $ext = $tiposPermitidos[$mime];
        $novoNome = "professor_" . $user_id . "." . $ext;

        $pasta = "../uploads/";
        if (!is_dir($pasta)) {
            throw new Exception('A pasta de uploads não existe.');
        }

        $destino = $pasta . $novoNome;

        if (!move_uploaded_file($foto['tmp_name'], $destino)) {
            throw new Exception('Erro ao guardar a imagem.');
        }

        if (!empty($fotoAtual) && $fotoAtual !== $novoNome && file_exists($pasta . $fotoAtual)) {
            if (!unlink($pasta . $fotoAtual)) {
                throw new Exception('Não foi possível apagar a foto antiga.');
            }
        }

        $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
        $stmt->execute([$novoNome, $user_id]);
    }

    $pdo->commit();

    $_SESSION['alerta_professor_editar'] = [
        'tipo' => 'success',
        'msg' => 'Professor atualizado com sucesso.'
    ];

    header('Location: index.php?page=lista_professor');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['alerta_professor_editar'] = [
        'tipo' => 'danger',
        'msg' => 'Erro ao atualizar: ' . $e->getMessage()
    ];

    header("Location: index.php?page=editar_professor&id=$id");
    exit;
}