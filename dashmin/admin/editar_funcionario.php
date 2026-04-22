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

$stmt = $pdo->prepare("
    SELECT 
        f.*,
        u.foto,
        u.categoria 
    FROM funcionario f
    LEFT JOIN users u ON u.id = f.user_id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcionario) {
    die("funcionário não encontrado.");
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Editar Funcionário — <?= htmlspecialchars($funcionario['nome']) ?></h6>

                <form action="atualizar_funcionario.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $funcionario['id'] ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control"
                                value="<?= htmlspecialchars($funcionario['nome']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control"
                                value="<?= htmlspecialchars($funcionario['bi'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" name="contato" class="form-control"
                                value="<?= htmlspecialchars($funcionario['contato'] ?? '') ?>">
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control"
                                value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control"
                                value="<?= htmlspecialchars($funcionario['morada'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" class="form-control"
                                value="<?= htmlspecialchars($funcionario['nacionalidade'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIF</label>
                            <input type="text" name="nif" class="form-control"
                                value="<?= htmlspecialchars($funcionario['nif'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control"
                                value="<?= htmlspecialchars($funcionario['distrito'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control"
                                value="<?= htmlspecialchars($funcionario['freguesia'] ?? '') ?>">
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gênero</label>
                            <select name="genero" class="form-select">
                                <option value="">Escolha...</option>
                                <option value="masculino" <?= ($funcionario['genero'] ?? '') == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="feminino" <?= ($funcionario['genero'] ?? '') == 'feminino' ? 'selected' : '' ?>>Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Imagem (deixa vazio para manter a atual)</label>
                            <input type="file" name="foto" class="form-control"
                                accept="image/jpeg, image/jpg, image/png, image/gif">
                            <?php if (!empty($funcionario['foto'])): ?>
                                <small class="text-muted">Atual: <?= htmlspecialchars($funcionario['foto']) ?></small>
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                value="<?= htmlspecialchars($funcionario['cargo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Contrato</label>
                            <input type="text" name="tipo_c" class="form-control"
                                value="<?= htmlspecialchars($funcionario['tipo_c'] ?? '') ?>">
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Profissional</label>
                            <input type="text" name="h_profissional" class="form-control"
                                value="<?= htmlspecialchars($funcionario['h_profissional'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Académica</label>
                            <input type="text" name="h_academica" class="form-control"
                                value="<?= htmlspecialchars($funcionario['h_academica'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="lista_funcionario.php" class="btn btn-secondary w-25">Cancelar</a>
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