<?php
include('../conexao.php');
include('menu.php');

// Garante que vem um ID válido, caso contrário volta para a lista
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: lista_aluno.php');
    exit;
}

$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT
            p.*,
            u.foto
        FROM professor p
        LEFT JOIN users u ON u.id = p.user_id
        WHERE p.id = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $professor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$professor) {
        // Se não encontrar o professor, volta para a lista
        header('Location: lista_professor.php');
        exit;
    }
} catch (PDOException $e) {
    // Em caso de erro de BD, mostra mensagem simples
    echo '<div class="alert alert-danger m-4">Erro ao carregar os dados do professor.</div>';
    exit;
}

// Normaliza o caminho da foto (aceita tanto "aluno_123.png" como "uploads_aluno/aluno_123.png")
$foto = isset($professor['foto']) ? trim($professor['foto']) : '';
$fotoPath = 'uploads/default.jpg';
if ($foto !== '') {
    if (strpos($foto, 'uploads/') !== false) {
        $fotoPath = $foto;
    } else {
        $fotoPath = 'uploads/' . $foto;
    }
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
        height: 50%;
        background: #ffffff;
        display: flex;
        align-items: stretch;
        justify-content: stretch;
        padding: 0;
        margin-left: 10px;
        margin-top: 10px;
        border-radius: 15px;
        border: solid #2563eb 5px;
    }

    .aluno-foto-inner {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.35);
        border-radius: 15px;
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
        ;
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
                <img src="<?= htmlspecialchars($fotoPath) ?>" alt="Foto do professor">
            </div>
        </div>
        <div class="aluno-info">
            <span class="aluno-badge">
                <i class="bi bi-person-badge"></i>
                N.º <?= htmlspecialchars($professor['id']) ?>
            </span>
            <h4><?= htmlspecialchars($professor['nome']) ?></h4>
            <div class="aluno-subtitle">
                <?= htmlspecialchars($professor['morada'] ?? 'Sem morada definida') ?>
            </div>
            <div class="aluno-fields">
                <p><span class="aluno-label">BI:</span> <?= htmlspecialchars($professor['bi'] ?? '—') ?></p>
                <p><span class="aluno-label">Data
                     Nasc.:</span><?= htmlspecialchars($professor['data_nascimento'] ?? '—') ?></p>
                <p><span class="aluno-label">Email:</span> <?= htmlspecialchars($professor['email'] ?? '—') ?></p>
                <p><span class="aluno-label">Contato:</span> <?= htmlspecialchars($professor['contato'] ?? '—') ?></p>
                <p><span class="aluno-label">Género:</span> <?= htmlspecialchars($professor['genero'] ?? '—') ?></p>
                <p><span class="aluno-label">Distrito:</span> <?= htmlspecialchars($professor['distrito'] ?? '—') ?></p>
                <p><span class="aluno-label">Freguesia:</span> <?= htmlspecialchars($professor['freguesia'] ?? '—') ?>
                </p>
                <p><span class="aluno-label">Grupo
                        Disciplinar:</span><?= htmlspecialchars($professor['grupo_d'] ?? '—') ?></p>
                <p><span class="aluno-label">Tipo de
                        Contrato:</span><?= htmlspecialchars($professor['tipo_c'] ?? '—') ?></p>
                <p><span class="aluno-label">Habilitação Profissional:</span>
                    <?= htmlspecialchars($professor['h_profissional'] ?? '—') ?></p>
                <p><span class="aluno-label">Habilitação Académica:</span>
                    <?= htmlspecialchars($professor['grupo_disciplinar'] ?? '—') ?></p>
                <p><span class="aluno-label">Registado
                        em:</span><?= htmlspecialchars($professor['inserido_em'] ?? '—') ?></p>
            </div>
            <div class="aluno-actions d-flex gap-2">
                <a href="lista_professor.php" class="btn btn-outline-secondary btn-sm">Voltar</a>
                <a href="editar_professor.php?id=<?= $professor['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                <a href="remover_professor.php?id=<?= $professor['id'] ?>" class="btn btn-danger btn-sm"
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