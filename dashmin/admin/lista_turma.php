<?php
include('../conexao.php');
include('menu.php');

// Verifica se foi passado um curso_id via GET
$curso_id = $_GET['curso_id'] ?? null;

if ($curso_id !== null) {
    // Lista apenas as turmas daquele curso
    $stmt = $pdo->prepare("
        SELECT 
        t.id, 
        t.curso_id,
        c.nome AS nome_curso,
        c.imagem,
        t.codigo, 
        t.ciclo_formacao, 
        t.statu
        FROM turma t
        INNER JOIN curso c ON c.id = t.curso_id
        WHERE c.id = ?
        ORDER BY t.codigo ASC
    ");
    $stmt->execute([$curso_id]);
} else {
    // Lista todas as turmas ordenadas alfabeticamente pelo nome do curso
    $stmt = $pdo->query("
        SELECT t.id, t.curso_id, c.nome AS nome_curso, c.imagem,
               t.codigo, t.ciclo_formacao, t.statu
        FROM turma t
        INNER JOIN curso c ON c.id = t.curso_id
        ORDER BY c.nome ASC, t.codigo ASC
    ");
}
$turma = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .col-xl-2 {
        flex: 0 0 auto;
        /* garante que não encolha automaticamente */
        width: calc(25% + 20px);
        /* aumenta 20px além da largura padrão */
    }

    .course-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 5px;
        overflow: hidden;
        width: 100%;
        /* O card ocupa toda a largura da coluna */
        height: 300px;
        /* Define altura fixa para todos os cards */
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .course-image {
        width: 100%;
        height: 180px;
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
        <?php foreach ($turma as $t): ?>
            <div class="col-sm-12 col-xl-4">
                <div class="course-card">

                    <!-- Imagem do curso como link para filtrar turmas -->
                    <a href="lista_aluno.php?turma_id=<?= $t['id'] ?>">
                        <div class="course-image">
                            <img src="uploads_curso/<?= htmlspecialchars($t['imagem'] ?? 'default.jpg') ?>"
                                alt="<?= htmlspecialchars($t['nome_curso']) ?>">
                        </div>
                    </a>

                    <!-- Conteúdo da turma -->
                    <div class="course-text">
                        <h5><?= htmlspecialchars($t['nome_curso']) ?></h5>
                        <h6> Código: <?= htmlspecialchars($t['codigo']) ?><br></h6>
                        <small>

                            Ciclo: <?= htmlspecialchars($t['ciclo_formacao']) ?><br>
                            Status: <?= htmlspecialchars($t['statu']) ?>
                        </small>
                    </div>

                </div>
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