<?php
include('../conexao.php');
include('menu.php');


$stmt = $pdo->query("SELECT COUNT(*) AS total FROM aluno");
$totalAlunos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM curso");
$totalCurso = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM turma");
$totalTurma = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM professor");
$totalProfessor = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// $sql = "
//     SELECT 
//         a.numero_aluno AS id,
//         a.nome,
//         a.morada AS descricao,
//         u.foto AS imagem
//     FROM aluno a
//     INNER JOIN users u ON u.id = a.user_id
// ";

// $stmt = $pdo->prepare($sql);
// $stmt->execute();

// $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$turma_id = isset($_GET['turma_id']) ? (int) $_GET['turma_id'] : null;

if ($turma_id !== null) {

    $stmt = $pdo->prepare("
     SELECT 
    a.numero_aluno AS id,
    a.nome,
    a.morada,
    u.foto AS imagem
FROM aluno a
INNER JOIN aluno_turma at ON at.numero_aluno = a.numero_aluno
INNER JOIN users u ON u.id = a.user_id
WHERE at.turma_id = ?
ORDER BY a.nome ASC
    ");

    $stmt->execute([$turma_id]);

} else {

    // Se não passar turma_id, lista todos os alunos
    $stmt = $pdo->query("
        SELECT 
            a.numero_aluno AS id,
            a.nome,
            a.morada,
            u.foto As imagem
        FROM aluno a
        INNER JOIN users u ON u.id = a.user_id
        ORDER BY a.nome ASC
    ");
}

$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .col-xl-2 {
        flex: 0 0 auto;
        /* garante que não encolha automaticamente */
        width: 225px;
        /* aumenta 20px além da largura padrão */
    }

    .course-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 5px;
        overflow: hidden;
        width: 200px;
        /* O card ocupa toda a largura da coluna */
        height: 250px;
        /* Define altura fixa para todos os cards */
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .course-image {
        width: 100%;
        height: 150px;
        /* altura fixa */
        overflow: hidden;
        object-fit: cover;
        border-radius: 8px;

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
        padding: 10px;
        flex: 1;
        /* ocupa o espaço restante do card */
        overflow: hidden;
    }
</style>
<?php
include 'nav-menu.php';
?>

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
        <?php foreach ($alunos as $a): ?>
            <div class="col-sm-12 col-xl-2">
                <a href="aluno.php?id=<?= $a['id'] ?>" class="text-decoration-none text-dark">
                    <div class="bg-white shadow rounded overflow-hidden h-100">

                        <div class="course-card">
                            <div class="course-image">
                                <img src="uploads_aluno/<?= htmlspecialchars($a['imagem'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($a['nome']) ?>">
                            </div>
                            <div class="course-text">
                                <h5><?= htmlspecialchars($a['nome']) ?></h5>
                                <p>Número de Mec: <?= htmlspecialchars($a['id']) ?></p>
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