<?php
session_start();
require('conexao.php');
require('inicio.php');



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$encarregado = $pdo->query(
    "SELECT id, nome, bi FROM encarregado ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$curso = $pdo->query(
    "SELECT id, nome FROM curso ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$turma = $pdo->query(
    "SELECT id, codigo FROM turma ORDER BY codigo"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- Form Start -->
<div class="col-12">
    <?php if (!empty($_SESSION['alerta'])): ?>
        <div id="alerta"
            class="alert alert-<?= htmlspecialchars($_SESSION['alerta']['tipo']) ?> alert-dismissible fade show"
            role="alert">
            <?= htmlspecialchars($_SESSION['alerta']['msg'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <script>
            setTimeout(() => document.getElementById('alerta')?.remove(), 3000);
        </script>

        <?php unset($_SESSION['alerta']); ?>
    <?php endif; ?>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <?php
            $alunoInserido = isset($_GET['aluno_inserido']) && $_GET['aluno_inserido'] == 1;
            $idAluno = $_GET['idAluno'] ?? null;
            ?>

            <?php if ($alunoInserido && $idAluno): ?>
                <div class="alert alert-success">
                    Aluno inserido com sucesso. ID do aluno: <strong><?= htmlspecialchars($idAluno) ?></strong>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['mensagem_sucesso'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['mensagem_sucesso']); ?>
            <?php endif; ?>

            <div class="bg-white shadow rounded  p-4">
                <h6 class="mb-4">Registar Aluno</h6>
                <form action="salvar_aluno.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gênero</label>
                            <select name="genero" class="form-select" required>
                                <option value="">Escolha...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Feminino">Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="formFile" class="form-label">Foto</label>
                            <input class="form-control" name="foto" type="file"
                                accept="image/png, image/jpeg, image/jpg, image/webp" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Curso</label>
                            <select name="curso_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($curso as $c): ?>
                                    <option value="<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Turma</label>
                            <select name="turma_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($turma as $t): ?>
                                    <option value="<?= $t['id'] ?>">
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
                                    <option value="<?= $e['id'] ?>">
                                        <?= htmlspecialchars($e['nome']) ?> — <?= $e['bi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="laco_principal" class="form-control mt-2"
                                placeholder="Laço familiar" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Encarregado Secundário</label>
                            <select name="encarregado_secundario_id" class="form-select">
                                <option value="">Escolha...</option>
                                <?php foreach ($encarregado as $e): ?>
                                    <option value="<?= $e['id'] ?>">
                                        <?= htmlspecialchars($e['nome']) ?> — <?= $e['bi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="laco_secundario" class="form-control mt-2"
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
<?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['mensagem_sucesso'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensagem_sucesso']); endif; ?>


<!-- Footer Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded-top p-4">
        <div class="row">
            <div class="col-12 col-sm-6 text-center text-sm-start">
                &copy; <a href="#">Your Site Name</a>, All Right Reserved.
            </div>
            <div class="col-12 col-sm-6 text-center text-sm-end">
                <!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                Designed By <a href="https://htmlcodex.com">HTML Codex</a>
            </div>
        </div>
    </div>
</div>
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
</body>

</html>