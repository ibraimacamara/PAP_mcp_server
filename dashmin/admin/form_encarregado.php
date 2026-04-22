<?php
session_start();
include('../conexao.php');
include('menu.php');


if (empty($_SESSION['csrf_token_encarregado'])) {
    $_SESSION['csrf_token_encarregado'] = bin2hex(random_bytes(32));
}

$alertaEncarregado = $_SESSION['alerta_encarregado'] ?? null;
unset($_SESSION['alerta_encarregado']);

?>

<!-- Form Start -->
<div class="col-12">
    <?php if (!empty($alertaEncarregado)): ?>
        <div id="alerta"
            class="alert alert-<?= htmlspecialchars($alertaEncarregado['tipo']) ?> alert-dismissible fade show"
            role="alert">
            <?= htmlspecialchars($alertaEncarregado['msg'], ENT_QUOTES, 'UTF-8') ?>
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
       

            <div class="col-12 mb-4">
                <div class="bg-white shadow rounded h-100 p-4">
                    <h6 class="mb-4">Registar Encarregado</h6>
                    <form action="salvar_encarregado.php" method="POST">
                        <input type="hidden" name="csrf_token_encarregado"
                            value="<?= htmlspecialchars($_SESSION['csrf_token_encarregado'], ENT_QUOTES, 'UTF-8') ?>">
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
                                <label class="form-label">Morada</label>
                                <input type="text" name="morada" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gênero</label>
                                <select name="genero" class="form-select" required>
                                    <option value="">Escolha...</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                </select>
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

                        <button type="submit" class="btn t btn-primary d-block mx-auto w-50">Registar</button>

                    </form>
                </div>
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
</body>

</html>