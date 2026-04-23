<?php

include "../conexao.php";
include "menu.php";
include "nav-menu.php";

// Gera CSRF token se não existir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verifica se o ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID inválido.");
}

$id = (int) $_GET['id'];

// Busca dados do usuário
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}
?>
<?php if (isset($_SESSION['alerta_user'])): ?>
    <div class="alert alert-<?= $_SESSION['alerta_user']['tipo'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['alerta_user']['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['alerta_user']); ?>
<?php endif; ?>


<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Editar Meus Dados</h6>

                <form action="index.php?page=atualizar_user" method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'], ENT_QUOTES) ?>">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <!-- Username -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome de utilizador</label>
                            <input type="username" name="username" class="form-control"
                                value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                        </div>

                        <!-- Senha -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Senha</label>
                            <div class="input-wrapper">
                                <input type="password" id="senha" name="senha" class="form-control"
                                    placeholder="Nova senha (opcional)">
                                <span class="toggle" onclick="toggleSenha('senha')">👁</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Imagem (deixa vazio para manter a atual)</label>
                            <input type="file" name="foto" class="form-control"
                                accept="image/jpeg, image/jpg, image/png, image/gif">
                            <?php if (!empty($user['foto'])): ?>
                                <small class="text-muted">Atual: <?= htmlspecialchars($user['foto']) ?></small>
                            <?php endif; ?>
                        </div>
                        <!-- Confirmar Senha -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Senha</label>
                            <div class="input-wrapper">
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control"
                                    placeholder="Repita a senha">
                                <span class="toggle" onclick="toggleSenha('confirmar_senha')">👁</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php?page=home" class="btn btn-secondary w-25">Cancelar</a>
                        <button type="submit" class="btn btn-primary w-25">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CSS para os botões dentro do input -->
<style>
    .input-wrapper {
        position: relative;
    }

    .input-wrapper input {
        padding-right: 40px;
        /* espaço para o ícone */
    }

    .toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
    }
</style>

<!-- JS para alternar visibilidade da senha -->
<script>
    function toggleSenha(id) {
        const input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }
</script>

<?php include 'footer.php'; ?>
<a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

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