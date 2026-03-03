<?php
include('../conexao.php');
include('menu.php');

// Validação do ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Aluno inválido.");
}

$id = (int) $_GET['id'];

// Buscar dados do aluno e foto do usuário
$stmt = $pdo->prepare("
    SELECT 
        a.numero_aluno,
        a.nome,
        a.bi,
        a.data_nascimento,
        a.email,
        a.contato,
        a.morada,
        a.genero,
        a.distrito,
        a.freguesia,
        a.status,
        a.registado_em
    FROM aluno a
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("Aluno não encontrado.");
}
?>

<style>
.aluno-card {
    display: flex;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    height: 350px;
    max-width: 900px;
    margin: auto;
}

.aluno-foto {
    width: 50%;
    height: 100%;
}

.aluno-foto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.aluno-info {
    width: 50%;
    padding: 20px;
    overflow-y: auto;
}

.aluno-info h4 {
    margin-bottom: 15px;
}

.aluno-info p {
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .aluno-card {
        flex-direction: column;
        height: auto;
    }
    .aluno-foto, .aluno-info {
        width: 100%;
        height: auto;
    }
    .aluno-foto img {
        height: 250px;
    }
}
</style>

<div class="container mt-5">
    <div class="aluno-card">

        <!-- FOTO -->
        <div class="aluno-foto">
            <img src="../uploads/<?= htmlspecialchars($aluno['foto'] ?? 'default.png') ?>" alt="Foto do Aluno">
        </div>

        <!-- INFORMAÇÕES -->
        <div class="aluno-info">
            <h4><?= htmlspecialchars($aluno['nome']) ?></h4>

            <p><strong>ID:</strong> <?= htmlspecialchars($aluno['numero_aluno']) ?></p>
            <p><strong>BI:</strong> <?= htmlspecialchars($aluno['bi']) ?></p>
            <p><strong>Data Nascimento:</strong> <?= htmlspecialchars($aluno['data_nascimento']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($aluno['email']) ?></p>
            <p><strong>Contato:</strong> <?= htmlspecialchars($aluno['contato']) ?></p>
            <p><strong>Morada:</strong> <?= htmlspecialchars($aluno['morada']) ?></p>
            <p><strong>Género:</strong> <?= htmlspecialchars($aluno['genero']) ?></p>
            <p><strong>Distrito:</strong> <?= htmlspecialchars($aluno['distrito']) ?></p>
            <p><strong>Freguesia:</strong> <?= htmlspecialchars($aluno['freguesia']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($aluno['status']) ?></p>
            <p><strong>Registado em:</strong> <?= htmlspecialchars($aluno['registado_em']) ?></p>

            <a href="lista_alunos.php" class="btn btn-secondary mt-3">Voltar</a>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>
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