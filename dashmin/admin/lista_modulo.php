<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.id_curso,
        m.nome_modulo,
        m.codigo_modulo,
        m.ordem,
        m.carga_horaria,
        m.foto,
        c.nome AS nome_curso
    FROM modulo m
    LEFT JOIN curso c ON c.id = m.id_curso
    ORDER BY c.nome ASC, m.ordem ASC, m.nome_modulo ASC
");
$stmt->execute();
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .modulo-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        padding: 8px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .modulo-img {
        width: 100%;
        height: 190px;
        object-fit: cover;
        border-radius: 8px;
    }

    .modulo-info {
        font-size: 14px;
    }
</style>

<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">

        <?php foreach ($modulos as $m): ?>
            <div class="col-sm-12 col-xl-4 mb-4">
                <div class="card h-100 shadow p-2 modulo-card">
                    <a href="index.php?page=detalhe_modulo&id=<?= $m['id'] ?>" class="text-decoration-none text-dark">
                    <img src="../uploads<?= htmlspecialchars($m['foto'] ?? 'default.jpg') ?>"
                         class="modulo-img"
                         alt="<?= htmlspecialchars($m['nome_modulo']) ?>">

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate"
                            title="<?= htmlspecialchars($m['nome_modulo']) ?>">
                            <?= htmlspecialchars($m['nome_modulo']) ?>
                        </h5>

                        <p class="mb-1 modulo-info">
                            <strong>Curso:</strong>
                            <?= !empty($m['nome_curso'])
                                ? htmlspecialchars($m['nome_curso'])
                                : '<span class="text-muted">Sem curso</span>' ?>
                        </p>

                        <p class="mb-1 modulo-info">
                            <strong>Código:</strong>
                            <?= htmlspecialchars($m['codigo_modulo']) ?>
                        </p>

                        <p class="mb-1 modulo-info">
                            <strong>Ordem:</strong>
                            <?= (int) $m['ordem'] ?>
                        </p>

                        <p class="mb-1 modulo-info">
                            <strong>Carga horária:</strong>
                            <?= (int) $m['carga_horaria'] ?> horas
                        </p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <a href="editar_modulo.php?id=<?= (int) $m['id'] ?>"
                               class="btn btn-sm btn-primary">
                                Editar
                            </a>

                            <form action="remover_modulo.php" method="POST" class="m-0"
                                  onsubmit="return confirm('Tens a certeza que desejas remover este módulo?');">
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">

                                <button type="submit" class="btn btn-sm btn-danger">
                                    Remover
                                </button>
                            </form>
                        </div>
                    </div>
                </a>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php include 'footer.php'; ?>

<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>

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