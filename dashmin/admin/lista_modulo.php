<?php
include('../conexao.php');
include('menu.php');

// Buscar módulos com o nome do curso
$stmt = $pdo->prepare("
    SELECT 
        m.id,
        m.nome_modulo,
        m.codigo_modulo,
        m.ordem,
        m.carga_horaria,
        c.nome AS nome_curso
    FROM modulo m
    INNER JOIN curso c ON m.id_curso = c.id
    ORDER BY c.nome ASC, m.ordem ASC
");
$stmt->execute();
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'nav-menu.php'; ?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h4 class="mb-4">Lista de Módulos</h4>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Curso</th>
                                <th>Módulo</th>
                                <th>Código</th>
                                <th>Ordem</th>
                                <th>Carga Horária</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($modulos)): ?>
                                <?php foreach ($modulos as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['id']) ?></td>
                                        <td><?= htmlspecialchars($m['nome_curso']) ?></td>
                                        <td><?= htmlspecialchars($m['nome_modulo']) ?></td>
                                        <td><?= htmlspecialchars($m['codigo_modulo'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($m['ordem']) ?></td>
                                        <td><?= htmlspecialchars($m['carga_horaria']) ?></td>
                                        <td>
                                            <a href="index.php?page=editar_modulo&id=<?= htmlspecialchars($m['id']) ?>"
                                               class="btn btn-sm btn-primary mb-1">Editar</a>

                                            <form action="index.php?page=remover_modulo" method="POST" style="display:inline;"
                                                  onsubmit="return confirm('Tens a certeza que queres remover este módulo?')">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($m['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger mb-1">Remover</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum módulo registado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</div>

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