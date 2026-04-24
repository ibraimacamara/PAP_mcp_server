<?php

include('../conexao.php');
include('menu.php');

$idModuloSelecionado = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$idCursoModulo = null;

if ($idModuloSelecionado > 0) {
    $stmtModulo = $pdo->prepare("
        SELECT id, nome_modulo, id_curso
        FROM modulo
        WHERE id = :id
        LIMIT 1
    ");
    $stmtModulo->execute([':id' => $idModuloSelecionado]);
    $moduloSelecionado = $stmtModulo->fetch(PDO::FETCH_ASSOC);

    if ($moduloSelecionado) {
        $idCursoModulo = $moduloSelecionado['id_curso'] !== null
            ? (int) $moduloSelecionado['id_curso']
            : null;
    }
}

$modulos = $pdo->query("
    SELECT id, nome_modulo
    FROM modulo
    ORDER BY nome_modulo ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($idCursoModulo !== null) {
    $stmtTurmas = $pdo->prepare("
        SELECT id, codigo
        FROM turma
        WHERE curso_id = :id_curso
        ORDER BY codigo ASC
    ");
    $stmtTurmas->execute([':id_curso' => $idCursoModulo]);
    $turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);
} else {
    $turmas = [];
}

$professores = $pdo->query("
    SELECT id, nome
    FROM professor
    ORDER BY nome ASC
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">

        <div class="col-12">
            <?php if (!empty($_SESSION['alerta_mct'])): ?>
                <div id="alerta_mct"
                    class="alert alert-<?= htmlspecialchars($_SESSION['alerta_mct']['tipo']) ?> alert-dismissible fade show"
                    role="alert">
                    <?= htmlspecialchars($_SESSION['alerta_mct']['msg'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <script>
                    setTimeout(() => document.getElementById('alerta_mct')?.remove(), 3000);
                </script>

                <?php unset($_SESSION['alerta_mct']); ?>
            <?php endif; ?>
        </div>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-white shadow rounded h-100 p-4 d-flex flex-column">

                <h5 class="mb-4">Atribuir módulo à turma e professor</h5>

                <form action="index.php?page=salvar_mct" method="POST" class="d-flex flex-column flex-grow-1">

                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Módulo</label>

                        <select name="id_modulo" class="form-select" required>
                            <option value="">Escolha o módulo...</option>

                            <?php foreach ($modulos as $modulo): ?>
                                <option value="<?= (int) $modulo['id'] ?>"
                                    <?= $idModuloSelecionado === (int) $modulo['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($modulo['nome_modulo'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Turma</label>

                        <select name="id_turma" class="form-select" required <?= empty($turmas) ? 'disabled' : '' ?>>
                            <option value="">
                                <?= empty($turmas)
                                    ? 'Nenhuma turma disponível para este curso'
                                    : 'Escolha a turma...' ?>
                            </option>

                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= (int) $turma['id'] ?>">
                                    <?= htmlspecialchars($turma['codigo'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($idModuloSelecionado > 0 && $idCursoModulo === null): ?>
                            <small class="text-danger">
                                Este módulo não está associado a nenhum curso.
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Professor</label>

                        <select name="id_professor" class="form-select" required>
                            <option value="">Escolha o professor...</option>

                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= (int) $professor['id'] ?>">
                                    <?= htmlspecialchars($professor['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>

                        <select name="estado" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="pendente">Pendente</option>
                            <option value="concluido">Concluído</option>
                        </select>
                    </div>

                    <div class="mt-auto">
                        <button type="submit" class="btn btn-primary w-50 d-block mx-auto">
                            Guardar atribuição
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>

</div>

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>

</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

</body>

</html>