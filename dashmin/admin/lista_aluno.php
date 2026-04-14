<?php

include('menu.php');

$turma_id = isset($_GET['turma_id']) ? (int) $_GET['turma_id'] : null;

if ($turma_id !== null) {

    $stmt = $pdo->prepare("
    SELECT 
        a.numero_aluno AS id,
        a.nome,
        a.morada,
        u.foto AS imagem,
        c.nome AS curso,
        t.codigo AS turma,
        ep.nome AS encarregado_principal,
        es.nome AS encarregado_secundario
    FROM aluno a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN curso c ON c.id = a.curso_id
    LEFT JOIN turma t ON t.id = a.turma_id
    LEFT JOIN encarregado ep ON ep.id = a.encarregado_principal_id
    LEFT JOIN encarregado es ON es.id = a.encarregado_secundario_id
    WHERE a.turma_id = ?
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
        u.foto AS imagem,
        c.nome AS curso,
        t.codigo AS turma,
        ep.nome AS encarregado_principal,
        es.nome AS encarregado_secundario
    FROM aluno a
    LEFT JOIN users u ON u.id = a.user_id
    LEFT JOIN curso c ON c.id = a.curso_id
    LEFT JOIN turma t ON t.id = a.turma_id
    LEFT JOIN encarregado ep ON ep.id = a.encarregado_principal_id
    LEFT JOIN encarregado es ON es.id = a.encarregado_secundario_id
    ORDER BY t.codigo ASC, a.nome ASC
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
        padding: 5px;
        flex: 1;
        margin: 0px;
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
                <a href="detalhe_aluno.php?id=<?= $a['id'] ?>" class="text-decoration-none text-dark">
                    <div class="bg-white shadow rounded overflow-hidden h-100">

                        <div class="course-card">
                            <div class="course-image">
                                <img src="uploads/<?= htmlspecialchars($a['imagem'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($a['nome']) ?>">
                            </div>
                            <div class="course-text">
                                <h5><?= htmlspecialchars($a['nome']) ?></h5>

                                <h6>Número de Mec: <?= htmlspecialchars($a['id']) ?> </h6>

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