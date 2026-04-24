<?php
include('../conexao.php');
include('menu.php');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT 
        m.*,
        c.nome AS curso_nome
    FROM modulo m
    LEFT JOIN curso c ON c.id = m.id_curso
    WHERE m.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$modulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$modulo) {
    die('Módulo não encontrado.');
}

$fotoPath = '../uploads/default.jpg';

if (!empty($modulo['foto'])) {
    $fotoPath = '../uploads' . $modulo['foto'];
}

$stmtRelacoes = $pdo->prepare("
    SELECT 
        r.id_relacao,
        r.estado,
        t.id AS turma_id,
        t.codigo AS turma_codigo,
        p.id AS professor_id,
        p.nome AS professor_nome
    FROM relacao r
    INNER JOIN turma t ON t.id = r.id_turma
    INNER JOIN professor p ON p.id = r.id_professor
    WHERE r.id_modulo = ?
    ORDER BY t.codigo ASC, p.nome ASC
");
$stmtRelacoes->execute([$id]);
$relacoes = $stmtRelacoes->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'nav-menu.php'; ?>

<style>
    .modulo-wrapper {
        max-width: 1100px;
        margin: 30px auto;
    }

    .modulo-card {
        display: flex;
        background: linear-gradient(135deg, #ffffff, #f3f6ff);
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }

    .modulo-foto {
        width: 35%;
        background: #fff;
    }

    .modulo-foto img {
        width: 100%;
        height: 100%;
        min-height: 280px;
        object-fit: cover;
        display: block;
    }

    .modulo-info {
        width: 65%;
        padding: 24px 28px;
    }

    .modulo-info h4 {
        font-weight: 800;
        margin-bottom: 10px;
    }

    .modulo-badge {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .modulo-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 18px;
        margin-top: 12px;
    }

    .modulo-label {
        font-weight: 600;
        color: #374151;
    }

    @media (max-width: 768px) {
        .modulo-card {
            flex-direction: column;
        }

        .modulo-foto,
        .modulo-info {
            width: 100%;
        }
    }
</style>

<div class="container-fluid px-4 modulo-wrapper">

    <div class="modulo-card mb-4">
        <div class="modulo-foto">
            <img src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Foto do módulo">
        </div>

        <div class="modulo-info">
            <span class="modulo-badge">
                Código <?= htmlspecialchars($modulo['codigo_modulo'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
            </span>

            <h4><?= htmlspecialchars($modulo['nome_modulo'], ENT_QUOTES, 'UTF-8') ?></h4>

            <div class="modulo-fields">
                <p>
                    <span class="modulo-label">Curso:</span>
                    <?= htmlspecialchars($modulo['curso_nome'] ?? 'Sem curso', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <span class="modulo-label">Ordem:</span>
                    <?= (int) $modulo['ordem'] ?>
                </p>

                <p>
                    <span class="modulo-label">Carga horária:</span>
                    <?= (int) $modulo['carga_horaria'] ?> horas
                </p>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="index.php?page=lista_modulo" class="btn btn-outline-secondary btn-sm">
                    Voltar
                </a>

                <a href="index.php?page=atribuir_mct&id=<?= (int) $modulo['id'] ?>" class="btn btn-primary btn-sm">
                    Atribuir a turma/professor
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded p-4">
        <h5 class="mb-3">Turmas e professores ligados a este módulo</h5>

        <?php if (empty($relacoes)): ?>
            <div class="alert alert-info mb-0">
                Este módulo ainda não está atribuído a nenhuma turma/professor.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Turma</th>
                            <th>Professor</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($relacoes as $r): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($r['turma_codigo'] ?? $r['nome_turma'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['professor_nome'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?php
                                    $estado = $r['estado'] ?? 'ativo';

                                    $badge = match ($estado) {
                                        'ativo' => 'success',
                                        'pendente' => 'warning',
                                        'concluido' => 'secondary',
                                        default => 'dark'
                                    };
                                    ?>

                                    <span class="badge bg-<?= $badge ?>">
                                        <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="editar_atribuicao.php?id=<?= (int) $r['id_relacao'] ?>"
                                       class="btn btn-sm btn-primary">
                                        Editar
                                    </a>

                                    <form action="remover_atribuicao.php"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tens a certeza que queres remover esta atribuição?');">
                                        <input type="hidden" name="id" value="<?= (int) $r['id_relacao'] ?>">

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Remover
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer.php'; ?>

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>

</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>