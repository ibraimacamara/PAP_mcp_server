<?php
include('../conexao.php');
include('menu.php');

// Pegar apenas os campos necessários
$stmt = $pdo->prepare("
    SELECT id, nome, email, contato, morada
    FROM encarregado
    ORDER BY nome ASC
");
$stmt->execute();
$encarregado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
include 'nav-menu.php';
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h4 class="mb-4">Lista de Encarregados</h4>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Contato</th>
                                <th>Morada</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($encarregado as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['id']) ?></td>
                                    <td><?= htmlspecialchars($e['nome']) ?></td>
                                    <td><?= htmlspecialchars($e['email']) ?></td>
                                    <td><?= htmlspecialchars($e['contato']) ?></td>
                                    <td><?= htmlspecialchars($e['morada']) ?></td>
                                    <td>
                                    <td>
                                        <a href="editar_encarregado.php?id=<?= htmlspecialchars($e['id']) ?>"
                                            class="btn btn-sm btn-primary mb-1">Editar</a>

                                        <form action="remover_encarregado.php" method="POST" style="display:inline;"
                                            onsubmit="return confirm('Tem certeza que deseja remover?')">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($e['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger mb-1">Remover</button>
                                        </form>
                                    </td>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

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