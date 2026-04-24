<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
    SELECT
        r.id_relacao,
        r.id_turma,
        r.id_modulo,
        r.id_professor,
        r.estado,

        t.nome_turma,
        m.nome_modulo,
        m.codigo_modulo,
        m.carga_horaria,
        c.nome AS nome_curso,
        p.nome AS nome_professor

    FROM relacao r
    INNER JOIN turma t ON t.id = r.id_turma
    INNER JOIN modulo m ON m.id = r.id_modulo
    LEFT JOIN curso c ON c.id = m.id_curso
    INNER JOIN professor p ON p.id = r.id_professor

    ORDER BY c.nome ASC, t.nome_turma ASC, m.ordem ASC, m.nome_modulo ASC
");
$stmt->execute();
$atribuicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="bg-white shadow rounded p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Lista de atribuições</h5>

            <a href="index.php?page=atribuir_mct" class="btn btn-primary btn-sm">
                Nova atribuição
            </a>
        </div>

        <?php if (empty($atribuicoes)): ?>
            <div class="alert alert-info mb-0">
                Ainda não existem atribuições registadas.
            </div>
        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Turma</th>
                            <th>Módulo</th>
                            <th>Código</th>
                            <th>Professor</th>
                            <th>Carga horária</th>
                            <th>Estado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($atribuicoes as $a): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($a['nome_curso'] ?? 'Sem curso', ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($a['nome_turma'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($a['nome_modulo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($a['codigo_modulo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($a['nome_professor'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <?= (int) $a['carga_horaria'] ?>h
                                </td>

                                <td>
                                    <?php
                                    $estado = $a['estado'] ?? 'ativo';

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
                                    <a href="editar_atribuicao.php?id=<?= (int) $a['id_relacao'] ?>"
                                       class="btn btn-sm btn-primary">
                                        Editar
                                    </a>

                                    <form action="remover_atribuicao.php"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tens a certeza que queres remover esta atribuição?');">
                                        <input type="hidden" name="id" value="<?= (int) $a['id_relacao'] ?>">

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