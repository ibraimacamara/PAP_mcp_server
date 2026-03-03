<?php
include('../conexao.php');
include('menu.php');

$stmt = $pdo->prepare("
     SELECT 
    e.id,
    e.nome,
    e.bi,
    e.email,
    e.contato,
    e.morada,
    e.genero,
    e.distrito,
    e.freguesia,
    e.status,
    e.registado_em
FROM encarregado e
ORDER BY e.nome ASC
    ");
$stmt->execute();
$encarregado = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h4 class="mb-4">Lista de Encarregados</h4>
                <table class="table table-striped table-bordered table-responsive">
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
                                    <a href="editar_encarregado.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-primary mb-1">Editar</a>
                                    <a href="apagar_encarregado.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-danger mb-1"
                                       onclick="return confirm('Tem certeza que deseja apagar?')">Apagar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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