<?php
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM aluno");
$totalAlunos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM curso");
$totalCurso = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM turma");
$totalTurma = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) AS total FROM professor");
$totalProfessor = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>


<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <a href="lista_aluno.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total de Alunos</p>
                        <h6 class="mb-0">
                            <?php echo $totalAlunos; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="lista_curso.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total de Cursos</p>
                        <h6 class="mb-0">
                            <?php echo $totalCurso; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="lista_turma.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-area fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-1">Total de Turma</p>
                        <h6 class="mb-0">
                            <?php echo $totalTurma; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="lista_professor.php" class="text-decoration-none text-dark">
                <div class="bg-white shadow rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-pie fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total de Prof</p>
                        <h6 class="mb-0">
                            <?php echo $totalProfessor; ?>
                        </h6>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
<!-- Sale & Revenue End -->