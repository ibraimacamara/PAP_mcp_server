<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.email, p.contato, u.foto AS imagem
    FROM professor p
    LEFT JOIN users u ON u.id = p.user_id
    ORDER BY p.nome ASC
");
$stmt->execute();
$professor = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .col-xl-2 { flex: 0 0 auto; width: 225px; }
    .course-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 5px; overflow: hidden;
        width: 200px; height: 280px;
        display: flex; flex-direction: column;
    }
    .course-image { width: 100%; height: 140px; overflow: hidden; border-radius: 8px; }
    .course-image img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 8px; }
    .course-text { padding: 10px; flex: 1; overflow: hidden; }
</style>
<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <?php foreach ($professor as $p): ?>
            <div class="col-sm-12 col-xl-2">
                <div class="bg-white shadow rounded overflow-hidden h-100">
                    <div class="course-card">
                        <div class="course-image">
                            <img src="uploads_prof/<?= htmlspecialchars($p['imagem'] ?? 'default.jpg') ?>"
                                alt="<?= htmlspecialchars($p['nome']) ?>">
                        </div>
                        <div class="course-text">
                            <h5><?= htmlspecialchars($p['nome']) ?></h5>
                            <p class="mb-1">ID: <?= htmlspecialchars($p['id']) ?></p>
                        </div>
                        <div class="px-2 pb-2">
                            <a href="editar_professor.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary w-100">Editar</a>
                        </div>
                    </div>
                </div>
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
