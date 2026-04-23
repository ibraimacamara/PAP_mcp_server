<?php
declare(strict_types=1);


include '../conexao.php';
include 'menu.php';

if (empty($_SESSION['csrf_token_funcionario'])) {
    $_SESSION['csrf_token_funcionario'] = bin2hex(random_bytes(32));
}

$alertaFuncionario = $_SESSION['alerta_funcionario'] ?? null;
$oldFuncionario = $_SESSION['old_funcionario'] ?? [];
$tinhaFotoFuncionario = $_SESSION['tinha_foto_funcionario'] ?? false;

/* limpar após leitura */
unset($_SESSION['alerta_funcionario']);
unset($_SESSION['old_funcionario']);
unset($_SESSION['tinha_foto_funcionario']);

function old_funcionario(string $campo): string
{
    global $oldFuncionario;
    return htmlspecialchars((string)($oldFuncionario[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="col-12">
    <?php if (!empty($alertaFuncionario)): ?>
        <div id="alerta_funcionario"
             class="alert alert-<?= htmlspecialchars((string)$alertaFuncionario['tipo'], ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show"
             role="alert">
            <?= htmlspecialchars((string)$alertaFuncionario['msg'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>

        <script>
            setTimeout(() => document.getElementById('alerta_funcionario')?.remove(), 3000);
        </script>
    <?php endif; ?>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <?php if ($tinhaFotoFuncionario): ?>
                <small class="text-danger d-block mb-2">Selecione novamente a foto.</small>
            <?php endif; ?>

            <div class="bg-white shadow rounded p-4">
                <h6 class="mb-4">Registar Funcionário</h6>

                <form action="salvar_funcionario.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden"
                           name="csrf_token_funcionario"
                           value="<?= htmlspecialchars($_SESSION['csrf_token_funcionario'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= old_funcionario('nome') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old_funcionario('email') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control" value="<?= old_funcionario('bi') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contacto</label>
                            <input type="text" name="contato" class="form-control" value="<?= old_funcionario('contato') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control" value="<?= old_funcionario('data_nascimento') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control" value="<?= old_funcionario('morada') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" class="form-control" value="<?= old_funcionario('nacionalidade') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIF</label>
                            <input type="text" name="nif" class="form-control" value="<?= old_funcionario('nif') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control" value="<?= old_funcionario('distrito') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control" value="<?= old_funcionario('freguesia') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Género</label>
                            <select name="genero" class="form-select" required>
                                <option value="">Escolha...</option>
                                <option value="Masculino" <?= old_funcionario('genero') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="Feminino" <?= old_funcionario('genero') === 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="formFile" class="form-label">Foto</label>
                            <input id="formFile" class="form-control" name="foto" type="file" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de contrato</label>
                            <select name="t_contrato" class="form-select" required>
                                <option value="">Escolha...</option>
                                <option value="Contrato com termo" <?= old_funcionario('t_contrato') === 'Contrato com termo' ? 'selected' : '' ?>>
                                    Contrato com Termo
                                </option>
                                <option value="Contrato sem termo" <?= old_funcionario('t_contrato') === 'Contrato sem termo' ? 'selected' : '' ?>>
                                    Contrato sem Termo
                                </option>
                                <option value="Prestação de serviços" <?= old_funcionario('t_contrato') === 'Prestação de serviços' ? 'selected' : '' ?>>
                                    Prestação de Serviços
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <option value="">Escolha...</option>
                                <option value="admin" <?= old_funcionario('categoria') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="funcionario" <?= old_funcionario('categoria') === 'funcionario' ? 'selected' : '' ?>>Funcionário</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control" value="<?= old_funcionario('cargo') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Profissional</label>
                            <input type="text" name="h_profissional" class="form-control" value="<?= old_funcionario('h_profissional') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Académica</label>
                            <input type="text" name="h_academica" class="form-control" value="<?= old_funcionario('h_academica') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary d-block mx-auto w-50">Registar</button>
                </form>
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