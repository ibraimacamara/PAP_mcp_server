<?php

include('../conexao.php');
include('menu.php');

$curso = $pdo->query(
"SELECT id, nome FROM curso ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Professores para coordenador/diretor
$professores = $pdo->query(
    "SELECT id, nome FROM professor ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);



if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Form Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4 justify-content-center">

        <!-- ALERTA_curso_inserir (linha inteira) -->
        <div class="col-12">
            <?php if (!empty($_SESSION['alerta_curso_inserir'])): ?>
                <div id="alerta_curso_inserir"
                    class="alert alert-<?= htmlspecialchars($_SESSION['alerta_curso_inserir']['tipo']) ?> alert-dismissible fade show"
                    role="alert">
                    <?= htmlspecialchars($_SESSION['alerta_curso_inserir']['msg'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <script>
                    setTimeout(() => document.getElementById('alerta_curso_inserir')?.remove(), 3000);
                </script>

                <?php unset($_SESSION['alerta_curso_inserir']); ?>
            <?php endif; ?>
        </div>

        <!-- FORM CURSO -->

        <div class="col-lg-8 col-12">
            <div class="bg-white shadow rounded h-100 p-4 d-flex flex-column">
                <h5 class="mb-4">Inserir Curso</h5>

                <form action="index.php?page=salvar_curso" method="POST" enctype="multipart/form-data"
                    class="d-flex flex-column flex-grow-1">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Coordenador</label>
                        <select name="coordenador_id" class="form-select">
                            <option value="">Escolha...</option>
                            <?php foreach ($professores as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea type="text" name="descricao" class="form-control"></textarea>
                    </div>
                    <div class=" mb-3">
                        <label for="formFile" class="form-label">Foto</label>
                        <input class="form-control" name="foto" type="file"
                            accept="image/jpeg, image/jpg, image/png, image/gif" required>
                    </div>

                    <!-- empurra o botão para baixo -->
                    <div class="mt-auto">
                        <button class="btn btn-primary w-50 d-block mx-auto">Registar</button>
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
    <?php unset($_SESSION['mensagem_sucesso']); endif; ?>


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