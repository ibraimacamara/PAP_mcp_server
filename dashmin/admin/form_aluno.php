<?php
session_start();
include '../conexao.php';
include 'menu.php';



if (empty($_SESSION['csrf_token_aluno'])) {
    $_SESSION['csrf_token_aluno'] = bin2hex(random_bytes(32));
}

$alertaAluno = $_SESSION['alerta_aluno'] ?? null;
$oldAluno = $_SESSION['old_aluno'] ?? [];
$tinhaFotoAluno = $_SESSION['tinha_foto_aluno'] ?? false;

unset($_SESSION['alerta_aluno'], $_SESSION['old_aluno'], $_SESSION['tinha_foto_aluno']);

function old_aluno(string $campo): string
{
    global $oldAluno;
    return htmlspecialchars((string) ($oldAluno[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
}

$encarregado = $pdo->query(
    "SELECT id, nome, bi FROM encarregado ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$curso = $pdo->query(
    "SELECT id, nome FROM curso ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// $turma = $pdo->query(
//     "SELECT id, codigo FROM turma ORDER BY codigo"
// )->fetchAll(PDO::FETCH_ASSOC);

$turma = $pdo->query(
    "SELECT id, codigo, curso_id FROM turma ORDER BY codigo"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Form Start -->
<div class="col-12">
    <?php if (!empty($alertaAluno)): ?>
        <div id="alerta"
            class="alert alert-<?= htmlspecialchars($alertaAluno['tipo']) ?> alert-dismissible fade show"
            role="alert">
            <?= htmlspecialchars($alertaAluno['msg'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <script>
            setTimeout(() => document.getElementById('alerta')?.remove(), 3000);
        </script>

    <?php endif; ?>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <?php if ($tinhaFotoAluno): ?>
                <small class="text-danger">Selecione novamente a foto.</small>
            <?php endif; ?>

            <div class="bg-white shadow rounded  p-4">
                <h6 class="mb-4">Registar Aluno</h6>
                <form action="salvar_aluno.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token_aluno"
                        value="<?= htmlspecialchars($_SESSION['csrf_token_aluno'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= old_aluno('nome') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old_aluno('email') ?>" required>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control" value="<?= old_aluno('bi') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" class="form-control" value="<?= old_aluno('contato') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" value="<?= old_aluno('data_nascimento') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control" value="<?= old_aluno('morada') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control" value="<?= old_aluno('distrito') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control" value="<?= old_aluno('freguesia') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gênero</label>
                            <select name="genero" class="form-select" required>
                                <option value="">Escolha...</option>
                                <option value="Masculino" <?= old_aluno('genero') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Feminino" <?= old_aluno('genero') === 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="formFile" class="form-label">Foto</label>
                            <input class="form-control" name="foto" type="file"
                                accept="image/jpeg, image/jpg, image/png, image/gif">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Curso</label>
                            <select name="curso_id" id="curso_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($curso as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= old_aluno('curso_id') === (string) $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Turma</label>
                            <select name="turma_id" id="turma_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($turma as $t): ?>
                                    <option value="<?= $t['id'] ?>" data-curso="<?= $t['curso_id'] ?>" <?= old_aluno('turma_id') === (string) $t['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['codigo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Encarregado Principal <span class="text-danger">*</span></label>
                            <select name="encarregado_principal_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($encarregado as $e): ?>
                                    <option value="<?= $e['id'] ?>" <?= old_aluno('encarregado_principal_id') === (string) $e['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['nome']) ?> — <?= $e['bi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="laco_principal" class="form-control mt-2" value="<?= old_aluno('laco_principal') ?>"
                                placeholder="Laço familiar" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Encarregado Secundário</label>
                            <select name="encarregado_secundario_id" class="form-select">
                                <option value="">Escolha...</option>
                                <?php foreach ($encarregado as $e): ?>
                                    <option value="<?= $e['id'] ?>" <?= old_aluno('encarregado_secundario_id') === (string) $e['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['nome']) ?> — <?= $e['bi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="laco_secundario" class="form-control mt-2" value="<?= old_aluno('laco_secundario') ?>"
                                placeholder="Laço familiar ">
                        </div>
                    </div>




                    <button type="submit" class="btn t btn-primary d-block mx-auto w-50">Registar-se</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Form End -->
<!-- Footer Start -->
<?php
include 'footer.php';
?>
<!-- Footer End -->
</div>
<!-- Content End -->


<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/chart/chart.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>


<!-- Template Javascript -->
<script src="js/main.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cursoSelect = document.getElementById('curso_id');
        const turmaSelect = document.getElementById('turma_id');

        const todasTurmas = Array.from(turmaSelect.options).map(option => ({
            value: option.value,
            text: option.text,
            curso: option.getAttribute('data-curso')
        }));

        function atualizarTurmas() {
            const cursoSelecionado = cursoSelect.value;

            turmaSelect.innerHTML = '<option value="">Escolha...</option>';

            todasTurmas.forEach(turma => {
                // ignora a opção vazia original
                if (!turma.value) return;

                // se não houver curso selecionado, mostra todas
                // se houver, mostra só as turmas desse curso
                if (cursoSelecionado === '' || turma.curso === cursoSelecionado) {
                    const option = document.createElement('option');
                    option.value = turma.value;
                    option.textContent = turma.text;
                    option.setAttribute('data-curso', turma.curso);
                    turmaSelect.appendChild(option);
                }
            });
        }

        cursoSelect.addEventListener('change', atualizarTurmas);

        // ao carregar a página
        atualizarTurmas();
    });
</script>
</body>

</html>