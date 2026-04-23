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

// Curso selecionado
$stmt = $pdo->prepare("SELECT * FROM curso WHERE id = ?");
$stmt->execute([$id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

// Lista de professores para coordenador
$professores = $pdo->query("SELECT id, nome FROM professor ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Curso não encontrado.");
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Editar Curso — <?= htmlspecialchars($curso['nome']) ?></h6>

                <form action="atualizar_curso.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $curso['id'] ?>">
                    <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($curso['imagem'] ?? '') ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Curso</label>
                            <input type="text" name="nome" class="form-control"
                                value="<?= htmlspecialchars($curso['nome']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coordenador</label>
                            <select name="coordenador_id" class="form-select">
                                <option value="">Escolha...</option>
                                <?php foreach ($professores as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= (int)($curso['coordenador'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Imagem (deixa vazio para manter a atual)</label>
                            <input type="file" name="imagem" class="form-control"
                                accept="image/jpeg, image/jpg, image/png, image/gif">
                            <?php if (!empty($curso['imagem'])): ?>
                                <small class="text-muted">Atual: <?= htmlspecialchars($curso['imagem']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="4"><?= htmlspecialchars($curso['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php?page=lista_curso" class="btn btn-secondary w-25">Cancelar</a>
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
