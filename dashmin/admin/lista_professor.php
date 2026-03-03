<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
     SELECT 
    p.id,
    p.nome,
    p.data_nascimento,
    p.bi,
    p.email,
    p.contato,
    p.morada,
    p.nif,
    p.nacionalidade,
    p.genero,
    p.distrito,
    p.freguesia,
    p.statu,
    p.registado_em,
    u.foto AS imagem
FROM professor p
INNER JOIN users u ON u.id = p.user_id
ORDER BY p.nome ASC
    ");
$stmt->execute();
$professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <?php foreach ($professor as $p): ?>
            <div class="col-sm-12 col-xl-2">
                <a href="aluno.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                    <div class="bg-white shadow rounded overflow-hidden h-100">

                        <div class="course-card">
                            <div class="course-image">
                                <img src="uploads_prof/<?= htmlspecialchars($p['imagem'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($p['nome']) ?>">
                            </div>
                            <div class="course-text">
                                <h5><?= htmlspecialchars($p['nome']) ?></h5>
                                <p>Número de Mec: <?= htmlspecialchars($p['id']) ?></p>
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