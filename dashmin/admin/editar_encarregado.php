<?php
include "../conexao.php";
include "menu.php";
include "nav-menu.php";


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID inválido.");
}

$id = intval($_GET['id']);

// Buscar dados do encarregado
$stmt = $conn->prepare("SELECT * FROM encarregado WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Encarregado não encontrado.");
}

$encarregado = $result->fetch_assoc();
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="col-12 mb-4">
                <div class="bg-white shadow rounded h-100 p-4">
                    <h6 class="mb-4">Editar Encarregado</h6>

                    <form action="atualizar_encarregado.php" method="POST">
                        
                        <input type="hidden" name="id" value="<?= $encarregado['id'] ?>">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome completo</label>
                                <input type="text" name="nome" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['nome']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['email']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">BI</label>
                                <input type="text" name="bi" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['bi']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contato</label>
                                <input type="text" name="contato" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['contato']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Morada</label>
                                <input type="text" name="morada" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['morada']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gênero</label>
                                <select name="genero" class="form-select" required>
                                    <option value="">Escolha...</option>
                                    <option value="masculino"
                                        <?= $encarregado['genero'] == 'masculino' ? 'selected' : '' ?>>
                                        Masculino
                                    </option>
                                    <option value="feminino"
                                        <?= $encarregado['genero'] == 'feminino' ? 'selected' : '' ?>>
                                        Feminino
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Distrito</label>
                                <input type="text" name="distrito" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['distrito']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Freguesia</label>
                                <input type="text" name="freguesia" class="form-control"
                                    value="<?= htmlspecialchars($encarregado['freguesia']) ?>" required>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary d-block mx-auto w-50">
                            Atualizar
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
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