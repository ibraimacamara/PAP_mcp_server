<?php
session_start();
include('../conexao.php');
include('menu.php');


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function old($campo)
{
    return htmlspecialchars($_SESSION['old'][$campo] ?? '', ENT_QUOTES, 'UTF-8');
}
?>


<!-- Form Start -->
<div class="col-12">
    <?php if (!empty($_SESSION['alerta'])): ?>
        <div id="alerta"
            class="alert alert-<?= htmlspecialchars($_SESSION['alerta']['tipo']) ?> alert-dismissible fade show"
            role="alert">
            <?= htmlspecialchars($_SESSION['alerta']['msg'], ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <script>
            setTimeout(() => document.getElementById('alerta')?.remove(), 3000);
        </script>

        <?php unset($_SESSION['alerta']); ?>
    <?php endif; ?>
</div>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <?php
            $funcionarioInserido = isset($_GET['funcionario_inserido']) && $_GET['funcoinario_inserido'] == 1;
            ?>

            <?php if ($funcionarioInserido): ?>
                <div class="alert alert-success">
                    Funcionário inserido com sucesso.
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['mensagem_sucesso'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['mensagem_sucesso']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['old']['foto'])): ?>
                <small class="text-danger">Selecione novamente a foto.</small>
            <?php endif; ?>
            <div class="bg-white shadow rounded  p-4">
                <h6 class="mb-4">Registar Funcionário</h6>
                <form action="salvar_funcionario.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= old('nome') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BI</label>
                            <input type="text" name="bi" class="form-control" value="<?= old('bi') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contacto</label>
                            <input type="text" name="contato" class="form-control" value="<?= old('contato') ?>"
                                required>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control"
                                value="<?= old('data_nascimento') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Morada</label>
                            <input type="text" name="morada" class="form-control" value="<?= old('morada') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" class="form-control"
                                value="<?= old('nacionalidade') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIF</label>
                            <input type="text" name="nif" class="form-control" value="<?= old('nif') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Distrito</label>
                            <input type="text" name="distrito" class="form-control" value="<?= old('distrito') ?>"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Freguesia</label>
                            <input type="text" name="freguesia" class="form-control" value="<?= old('freguesia') ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gênero</label>
                            <select name="genero" class="form-select" required>
                                <option value="">Escolha...</option>

                                <option value="Masculino" <?= old('genero') == 'Masculino' ? 'selected' : '' ?>>
                                    Masculino
                                </option>

                                <option value="Feminino" <?= old('genero') == 'Feminino' ? 'selected' : '' ?>>
                                    Feminino
                                </option>

                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="formFile" class="form-label">Foto</label>
                            <input class="form-control" name="foto" type="file"
                                accept="image/*">
                        </div>
                    </div>

                    <div class="row">
                   
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de contrato</label>
                            <select name="t_contrato" class="form-select" required>
                                <option value="">Escolha...</option>

                                <option value="Contrato com termo" <?= old('t_contrato') == 'Contrato com termo' ? 'selected' : '' ?>>
                                    Contrato com Termo
                                </option>

                                <option value="Contrato sem termo" <?= old('t_contrato') == 'Contrato sem termo' ? 'selected' : '' ?>>
                                    Contrato sem Termo
                                </option>

                                <option value="Prestação de serviços" <?= old('t_contrato') == 'Prestação de serviços' ? 'selected' : '' ?>>
                                    Prestação de Serviços
                                </option>

                            </select>
                        </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <option value="">Escolha...</option>

                                <option value="admin" <?= old('categoria') == 'admin' ? 'selected' : '' ?>>
                                    Admin
                                </option>

                                <option value="funcionario" <?= old('categoria') == 'funcionario' ? 'selected' : '' ?>>
                                    Funcionário
                                </option>
                            </select>
                        </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                value="<?= old('cargo') ?>" required>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Profissional</label>
                            <input type="text" name="h_profissional" class="form-control"
                                value="<?= old('h_profissional') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Habilitação Académica</label>
                            <input type="text" name="h_academica" class="form-control" value="<?= old('h_academica') ?>"
                                required>
                        </div>
                    </div>
                    <button type="submit" class="btn t btn-primary d-block mx-auto w-50">Registar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Form End -->
<?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['mensagem_sucesso'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensagem_sucesso']); endif; ?>


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