<?php

include('../conexao.php');
include('menu.php');

$cursos = $pdo->query(
    "SELECT id, nome FROM curso ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Form Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">

        <!-- ALERTA MÓDULO -->
        <div class="col-12">
            <?php if (!empty($_SESSION['alerta_modulo'])): ?>
                <div id="alerta_modulo"
                    class="alert alert-<?= htmlspecialchars($_SESSION['alerta_modulo']['tipo']) ?> alert-dismissible fade show"
                    role="alert">
                    <?= htmlspecialchars($_SESSION['alerta_modulo']['msg'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <script>
                    setTimeout(() => document.getElementById('alerta_modulo')?.remove(), 3000);
                </script>

                <?php unset($_SESSION['alerta_modulo']); ?>
            <?php endif; ?>
        </div>

        <!-- FORM MÓDULO -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-white shadow rounded h-100 p-4 d-flex flex-column">
                <h5 class="mb-4">Registar Módulo</h5>
                
                <form action="index.php?page=salvar_modulo" method="POST" enctype="multipart/form-data"
                    class="d-flex flex-column flex-grow-1">

                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Curso</label>
                        <select name="id_curso" class="form-select">
                            <option value="">Sem curso</option>

                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= (int) $curso['id'] ?>">
                                    <?= htmlspecialchars($curso['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome do módulo</label>
                        <input type="text" name="nome_modulo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Código do módulo</label>
                        <input type="text" name="codigo_modulo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="ordem" class="form-control" min="0" value="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Carga horária</label>
                        <input type="number" name="carga_horaria" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input class="form-control" name="foto" type="file"
                            accept="image/jpeg, image/jpg, image/png, image/gif">
                    </div>

                    <div class="mt-auto">
                        <button class="btn btn-primary w-50 d-block mx-auto">
                            Registar
                        </button>
                    </div>
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
    <?php unset($_SESSION['mensagem_sucesso']); ?>
<?php endif; ?>

<!-- Footer Start -->
<?php include 'footer.php'; ?>
<!-- Footer End -->

</div>
<!-- Content End -->

<!-- Back to Top -->
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>
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