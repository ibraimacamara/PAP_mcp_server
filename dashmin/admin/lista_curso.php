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

$stmt = $pdo->prepare("
    SELECT c.id, c.nome, c.descricao, c.coordenador, c.imagem,
           p.nome AS nome_coordenador
    FROM curso c
    LEFT JOIN professor p ON p.id = c.coordenador
    ORDER BY c.nome ASC
");
$stmt->execute();
$curso = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .col-xl-2 { flex: 0 0 auto; width: calc(25% + 20px); }
    .course-card {
        background: #fff; border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 5px; overflow: hidden;
        width: 100%; height: 300px;
        display: flex; flex-direction: column;
    }
    .course-image { width: 100%; height: 180px; overflow: hidden; border-radius: 8px; }
    .course-image img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 8px; }
    .course-text { padding: 10px; flex: 1; overflow: hidden; }
</style>

<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <?php foreach ($curso as $c): ?>
            <div class="col-sm-12 col-xl-4 mb-4">
                <div class="card h-100 shadow p-2">
                    <a href="lista_turma.php?curso_id=<?= $c['id'] ?>">
                        <img src="./uploads/<?= htmlspecialchars($c['imagem'] ?? 'default.jpg') ?>"
                            class="card-img-top rounded" alt="<?= htmlspecialchars($c['nome']) ?>"
                            style="height:190px; object-fit:cover;">
                    </a>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate" title="<?= htmlspecialchars($c['nome']) ?>">
                            <?= htmlspecialchars($c['nome']) ?>
                        </h5>
                        <?php if (!empty($c['nome_coordenador'])): ?>
                            <p class="mb-1"><small><strong>Coordenador:</strong> <?= htmlspecialchars($c['nome_coordenador']) ?></small></p>
                        <?php endif; ?>
                        <?php $descId = 'desc' . $c['id']; ?>
                        <p class="card-text flex-grow-1 mb-0 collapse-text" id="<?= $descId ?>"
                            style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;cursor:pointer;">
                            <?= htmlspecialchars($c['descricao']) ?>
                        </p>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <a href="editar_curso.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                        <form action="remover_curso.php" method="POST"
                            onsubmit="return confirm('Tens certeza que deseja remover este curso?');">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                        </form>
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
<script>
document.querySelectorAll('.collapse-text').forEach(p => {
    p.addEventListener('click', function () {
        if (this.style.display === '-webkit-box') {
            this.style.display = 'block';
            this.style.webkitLineClamp = 'unset';
        } else {
            this.style.display = '-webkit-box';
            this.style.webkitLineClamp = 3;
        }
    });
});
</script>
<script src="js/main.js"></script>
</body>
</html>
