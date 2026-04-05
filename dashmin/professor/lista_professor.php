<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.email, p.contato,p.grupo_d, u.foto AS imagem
    FROM professor p
    LEFT JOIN users u ON u.id = p.user_id
    ORDER BY p.nome ASC
");
$stmt->execute();
$professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .col-xl-2 {
        flex: 0 0 auto;
        width: 225px;
    }

    .course-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 5px;
        overflow: hidden;
        width: 200px;
        height: 260px;
        display: flex;
        flex-direction: column;
    }

    .course-image {
        width: 100%;
        height: 140px;
        overflow: hidden;
        border-radius: 8px;
    }

    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 8px;
    }

    .course-text {
        padding: 10px;
        flex: 1;
        overflow: hidden;
    }
    .card-footer-custom{
    background: #0180f7;
    color: white;
    text-align: center;
    padding: 8px;
    margin-top: 1px;

    border-top-left-radius: 120px 40px;
    border-top-right-radius: 120px 40px;
}
</style>
<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <?php foreach ($professor as $p): ?>
            <div class="col-sm-12 col-xl-2">
                <a href="detalhe_prof.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
                    <div class="bg-white shadow rounded overflow-hidden h-100">
                        <div class="course-card">
                            <div class="course-image">
                                <img src="uploads/<?= htmlspecialchars($p['imagem'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($p['nome']) ?>">
                            </div>
                            <div class="course-text">
                                <?php
                                $nomes = explode(' ', trim($p['nome']));
                                $primeiro = $nomes[0];
                                $ultimo = end($nomes);
                                ?>

                                <h5><?= htmlspecialchars($primeiro . ' ' . $ultimo) ?></h5>
                                <h6 class="mb-1">ID: <?= htmlspecialchars($p['id']) ?></h6>
                                
                            </div>
                            <div class="card-footer-custom">
                                GP:<?= htmlspecialchars($p['grupo_d']) ?>
                            
                            </div>
                        </div>

                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/chart/chart.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="js/main.js"></script>
</body>

</html>