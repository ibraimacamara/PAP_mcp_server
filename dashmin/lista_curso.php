<?php
require("conexao.php");
require("inicio.php");


$stmt = $pdo->query("SELECT COUNT(*) AS total FROM aluno");
$totalAlunos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM curso");
$totalCurso = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM turma");
$totalTurma = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM professor");
$totalProfessor = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$sql = "SELECT id, nome, descricao, imagem FROM curso";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$curso = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
    .course-card {
        background: #fff;
        border-radius: 8px;
        /* arredonda o card inteiro */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 5px;
        /* padding interno do card */
        overflow: hidden;
        /* garante que nada vaze */
        width: 300px;
        /* opcional, controla o tamanho do card */
    }

    .course-image {
        width: 100%;
        height: 180px;
        /* altura fixa */
        overflow: hidden;
        border-radius: 8px;
        /* arredonda a borda da imagem */
    }

    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* mantém proporção e corta excesso */
        display: block;
        border-radius: 8px;
        /* arredonda a imagem dentro do div */
    }

    .course-text {
        padding: 10px 5px;
        /* padding interno do texto */
    }
</style>
<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <a href="alunos.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total de Alunos</p>
                        <h6 class="mb-0">
                            <?php echo $totalAlunos; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="lista_curso.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total Cursos</p>
                        <h6 class="mb-0">
                            <?php echo $totalCurso; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-area fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Total Turmas</p>
                    <h6 class="mb-0">
                        <?php echo $totalTurma; ?>
                    </h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-pie fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Professor</p>
                    <h6 class="mb-0">
                        <?php echo $totalProfessor; ?>
                    </h6>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Sale & Revenue End -->


<!-- Sales Chart Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <!-- <div class="col-sm-12 col-xl-4">
            <a href="#">
                <div class="bg-white shadow text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">


                    </div>

                </div>
            </a>
        </div> -->
        <?php foreach ($curso as $c): ?>
            <div class="col-sm-12 col-xl-4">
                <a href="curso.php?id=<?= $c['id'] ?>" class="text-decoration-none text-dark">
                    <div class="bg-white shadow rounded overflow-hidden h-100">

                        <!-- Imagem -->

                        <div class="course-card">
                            <div class="course-image">
                                <img src="../uploads_curso/<?= htmlspecialchars($c['imagem'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($c['nome']) ?>">
                            </div>
                            <div class="course-text">
                                <h5><?= htmlspecialchars($c['nome']) ?></h5>
                                <p><?= htmlspecialchars($c['descricao']) ?></p>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
        <?php endforeach; ?>


    </div>
</div>
<!-- Sales Chart End -->




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
                </br>
                Distributed By <a class="border-bottom" href="https://themewagon.com" target="_blank">ThemeWagon</a>
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