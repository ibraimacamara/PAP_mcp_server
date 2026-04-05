<?php
include('../conexao.php');
include('menu.php');

$id = $_GET['id'] ?? 0; // pega direto, sem checagem

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.foto AS imagem,
        c.curso AS nome_curso,
        t.nome AS nome_turma
    FROM aluno a
    INNER JOIN users u ON u.id = a.user_id
    INNER JOIN aluno_curso ac ON ac.numero_aluno = a.numero_aluno
    INNER JOIN curso c ON c.id = ac.curso_id
    INNER JOIN aluno_turma alt ON alt.numero_aluno = a.numero_aluno
    INNER JOIN turma t ON t.id = alt.turma_id
    WHERE a.numero_aluno = ?
    LIMIT 1
");
$stmt->execute([$id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

// Normaliza foto
$fotoPath = 'uploads/default.jpg';
if (!empty($aluno['imagem'])) {
    $fotoPath = (strpos($aluno['imagem'], 'uploads/') !== false) ? $aluno['imagem'] : 'uploads/' . $aluno['imagem'];
}
?>

<style>
    .aluno-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 180px);
    }

    .aluno-card {
        display: flex;
        background: linear-gradient(135deg, #ffffff, #f3f6ff);
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        max-width: 900px;
        width: 100%;
        transform: translateY(0);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .aluno-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
    }

    .aluno-foto {
        width: 40%;
        background: #ffffff;
        display: flex;
        align-items: stretch;
        justify-content: stretch;
        padding: 0;
    }

    .aluno-foto-inner {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.35);
    }

    .aluno-foto-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .aluno-info {
        width: 60%;
        padding: 24px 28px;
    }

    .aluno-info h4 {
        margin-bottom: 8px;
        font-weight: 800;
        font-size: 1.6rem;
    }

    .aluno-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 18px;
    }

    .aluno-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .aluno-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px 18px;
        font-size: 1rem;
    }

    .aluno-fields p {
        margin-bottom: 2px;
    }

    .aluno-label {
        font-weight: 600;
        color: #374151;
    }

    .aluno-actions {
        margin-top: 18px;
    }

    @media (max-width: 768px) {
        .aluno-wrapper {
            min-height: auto;
        }

        .aluno-card {
            flex-direction: column;
        }

        .aluno-foto,
        .aluno-info {
            width: 100%;
        }

        .aluno-foto {
            padding: 16px 16px 8px;
        }

        .aluno-info {
            padding: 16px 18px 20px;
        }
    }
</style>

<div class="container mt-5 aluno-wrapper">
    <div class="aluno-card">
        <div class="aluno-foto">
            <div class="aluno-foto-inner">
                <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto do Aluno">
            </div>
        </div>
        <div class="aluno-info">
            <span class="aluno-badge">
                <i class="bi bi-person-badge"></i>
                N.º <?= htmlspecialchars($aluno['numero_aluno']) ?>
            </span>
            <h4><?= htmlspecialchars($aluno['nome']) ?></h4>
            <div class="aluno-subtitle">
                <?= htmlspecialchars($aluno['curso'] ?? 'Sem morada definida') ?>
            </div>
            <div class="aluno-fields">
                <p><span class="aluno-label">BI:</span> <?= htmlspecialchars($aluno['bi'] ?? '—') ?></p>
                <p><span class="aluno-label">Data Nasc.:</span>
                    <?= htmlspecialchars($aluno['data_nascimento'] ?? '—') ?></p>
                <p><span class="aluno-label">Email:</span> <?= htmlspecialchars($aluno['email'] ?? '—') ?></p>
                <p><span class="aluno-label">Contato:</span> <?= htmlspecialchars($aluno['contato'] ?? '—') ?></p>
                <p><span class="aluno-label">Género:</span> <?= htmlspecialchars($aluno['genero'] ?? '—') ?></p>
                <p><span class="aluno-label">Distrito:</span> <?= htmlspecialchars($aluno['distrito'] ?? '—') ?></p>
                <p><span class="aluno-label">Freguesia:</span> <?= htmlspecialchars($aluno['freguesia'] ?? '—') ?></p>
                <p><span class="aluno-label">Inserido em:</span> <?= htmlspecialchars($aluno['inserido_em'] ?? '—') ?>
                </p>
            </div>
            <div class="aluno-actions d-flex gap-2">
                <a href="lista_aluno.php" class="btn btn-outline-secondary btn-sm">Voltar</a>
                <a href="editar_aluno.php?id=<?= $aluno['numero_aluno'] ?>" class="btn btn-primary btn-sm">Editar</a>
                <a href="remover_aluno.php?id=<?= $aluno['numero_aluno'] ?>" class="btn btn-danger btn-sm"
                    onclick="return confirm('Tem certeza que deseja remover este aluno?');">
                    Remover
                </a>
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