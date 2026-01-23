<?php
session_start();
require('conexao.php');
require('inicio.php');


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
            $professorInserido = isset($_GET['professor_inserido']) && $_GET['professor_inserido'] == 1;
            ?>

            <?php if ($professorInserido): ?>
                <div class="alert alert-success">
                    Professor inserido com sucesso.
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['mensagem_sucesso'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['mensagem_sucesso']); ?>
            <?php endif; ?>

            <div class="bg-white shadow rounded  p-4">
                <h6 class="mb-4">Registar Professor</h6>
                <form action="salvar_professor.php" method="POST" enctype="multipart/form-data">
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
                            <label class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIF</label>
                            <input type="text" name="nif" class="form-control" required>
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
                            <input class="form-control" name="foto" type="file" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn t btn-primary d-block mx-auto w-50">Registar</button>
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