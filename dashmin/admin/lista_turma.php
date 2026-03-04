<?php
include('../conexao.php');
include('menu.php');

$curso_id = $_GET['curso_id'] ?? null;

if ($curso_id !== null) {
    $stmt = $pdo->prepare("
        SELECT t.id, t.curso_id, c.nome AS nome_curso, c.imagem,
               t.codigo, t.ciclo_formacao, t.statu
        FROM turma t
        INNER JOIN curso c ON c.id = t.curso_id
        WHERE c.id = ?
        ORDER BY t.codigo ASC
    ");
    $stmt->execute([$curso_id]);
} else {
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
    .col-xl-2 { flex: 0 0 auto; width: calc(25% + 20px); }
    .course-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 5px; overflow: hidden;
        width: 100%; height: 330px;
        display: flex; flex-direction: column;
    }
    .course-image { width: 100%; height: 160px; overflow: hidden; border-radius: 8px; }
    .course-image img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 8px; }
    .course-text { padding: 10px; flex: 1; overflow: hidden; }
</style>
<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <?php foreach ($turma as $t): ?>
            <div class="col-sm-12 col-xl-4">
                <div class="course-card">
                    <a href="lista_aluno.php?turma_id=<?= $t['id'] ?>">
                        <div class="course-image">
                            <img src="uploads_curso/<?= htmlspecialchars($t['imagem'] ?? 'default.jpg') ?>"
                                alt="<?= htmlspecialchars($t['nome_curso']) ?>">
                        </div>
                    </a>
                    <div class="course-text">
                        <h5><?= htmlspecialchars($t['nome_curso']) ?></h5>
                        <h6>Código: <?= htmlspecialchars($t['codigo']) ?></h6>
                        <small>
                            Ciclo: <?= htmlspecialchars($t['ciclo_formacao']) ?><br>
                            Status: <?= htmlspecialchars($t['statu']) ?>
                        </small>
                    </div>
                    <div class="px-2 pb-2">
                        <a href="editar_turma.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary w-100">Editar</a>
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
