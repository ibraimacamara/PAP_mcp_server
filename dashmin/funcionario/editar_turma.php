<?php

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
    SELECT t.*, c.nome AS nome_curso, p.nome AS nome_diretor
    FROM turma t
    LEFT JOIN curso c ON c.id = t.curso_id
    LEFT JOIN professor p ON p.id = t.diretor
    WHERE t.id = ?
");
$stmt->execute([$id]);
$turma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turma) {
    die("Turma não encontrada.");
}

$cursos = $pdo->query("SELECT id, nome FROM curso ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$professores = $pdo->query("SELECT id, nome FROM professor ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Editar Turma — <?= htmlspecialchars($turma['codigo']) ?></h6>

                <form action="atualizar_turma.php" method="POST">
                    <input type="hidden" name="id" value="<?= $turma['id'] ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código da Turma</label>
                            <input type="text" name="codigo" class="form-control"
                                value="<?= htmlspecialchars($turma['codigo']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciclo de Formação</label>
                            <input type="text" name="ciclo_formacao" class="form-control"
                                value="<?= htmlspecialchars($turma['ciclo_formacao'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Curso</label>
                            <select name="curso_id" class="form-select" required>
                                <option value="">Escolha...</option>
                                <?php foreach ($cursos as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $turma['curso_id'] == $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diretor de Turma</label>
                            <select name="diretor_id" class="form-select">
                                <option value="">Escolha...</option>
                                <?php foreach ($professores as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= (int)($turma['diretor'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php?page=lista_turma" class="btn btn-secondary w-25">Cancelar</a>
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
