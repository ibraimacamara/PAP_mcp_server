<?php
session_start();
require('conexao.php');
require('inicio.php');

$curso = $pdo->query(
    "SELECT id, nome FROM curso ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Form Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">

        <!-- ALERTA (linha inteira) -->
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

        <!-- FORM CURSO -->
 
        <div class="col-lg-6 col-12">
            <div class="bg-white shadow rounded h-100 p-4 d-flex flex-column">
                <h5 class="mb-4">Curso</h5>

                <form action="salvar_curso.php" method="POST" class="d-flex flex-column flex-grow-1">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" name="descricao" class="form-control">
                    </div>

                    <!-- empurra o botão para baixo -->
                    <div class="mt-auto">
                        <button class="btn btn-primary w-50 d-block mx-auto">Registar</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- FORM TURMA -->
        <div class="col-lg-6 col-12">
            <div class="bg-white shadow rounded h-100 p-4">
                <h5 class="mb-4">Turma</h5>

                <form action="salvar_turma.php" method="POST">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class=" mb-3">
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

                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" name="codigo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ciclo de Formação</label>
                        <input type="text" name="ciclo_formacao" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-50 d-block mx-auto">Registar</button>
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