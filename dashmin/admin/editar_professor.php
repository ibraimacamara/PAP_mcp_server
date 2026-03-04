<?php
session_start();
include('../conexao.php');
include('menu.php');
include('nav-menu.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM professor WHERE id = ?");
$stmt->execute([$id]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    die("Professor não encontrado.");
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Editar Professor — <?= htmlspecialchars($professor['nome']) ?></h6>

                <form action="atualizar_professor.php" method="POST">
                    <input type="hidden" name="id" value="<?= $professor['id'] ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control"
                                value="<?= htmlspecialchars($professor['nome']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($professor['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control"
                                value="<?= htmlspecialchars($professor['bi'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIF</label>
                            <input type="text" name="nif" class="form-control"
                                value="<?= htmlspecialchars($professor['nif'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" class="form-control"
                                value="<?= htmlspecialchars($professor['contato'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control"
                                value="<?= htmlspecialchars($professor['data_nascimento'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control"
                                value="<?= htmlspecialchars($professor['morada'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" class="form-control"
                                value="<?= htmlspecialchars($professor['nacionalidade'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gênero</label>
                            <select name="genero" class="form-select">
                                <option value="">Escolha...</option>
                                <option value="Masculino" <?= ($professor['genero'] ?? '') == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Feminino"  <?= ($professor['genero'] ?? '') == 'Feminino'  ? 'selected' : '' ?>>Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control"
                                value="<?= htmlspecialchars($professor['distrito'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control"
                                value="<?= htmlspecialchars($professor['freguesia'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="lista_professor.php" class="btn btn-secondary w-25">Cancelar</a>
                        <button type="submit" class="btn btn-primary w-25">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
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
