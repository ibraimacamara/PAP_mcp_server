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

$sql = "SELECT id, nome, descricao, imagem FROM curso ORDER BY nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$curso = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <?php foreach ($curso as $c): ?>
            <div class="col-sm-12 col-xl-4 mb-4">
                <div class="card h-100 shadow p-2">

                    <!-- Imagem -->
                   <a href="lista_turma.php?curso_id=<?= $c['id'] ?>">
                        <img src="./uploads_curso/<?= htmlspecialchars($c['imagem'] ?? 'default.jpg') ?>"
                            class="card-img-top rounded" alt="<?= htmlspecialchars($c['nome']) ?>"
                            style="height:190px; object-fit:cover; font-family:'Times New Roman', serif;">
                    </a>
                    <!-- Conteúdo -->
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate" title="<?= htmlspecialchars($c['nome']) ?>">
                            <?= htmlspecialchars($c['nome']) ?>
                        </h5>

                        <!-- Descrição colapsável no próprio <p> -->
                        <?php $descId = 'desc' . $c['id']; ?>
                        <p class="card-text flex-grow-1 mb-0 collapse-text" id="<?= $descId ?>"
                            style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; cursor:pointer;">
                            <?= htmlspecialchars($c['descricao']) ?>
                        </p>
                    </div>

                </div>
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
<script>
    document.querySelectorAll('.collapse-text').forEach(p => {
        p.addEventListener('click', function () {
            const el = this;
            if (el.style.display === '-webkit-box') {
                el.style.display = 'block';          // expande
                el.style.webkitLineClamp = 'unset';  // remove limite de linhas
            } else {
                el.style.display = '-webkit-box';    // recolhe
                el.style.webkitLineClamp = 3;        // volta ao limite
            }
        });
    });
</script>

<!-- Template Javascript -->
<script src="js/main.js"></script>
</body>

</html>