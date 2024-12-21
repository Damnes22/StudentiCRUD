<?php
session_start();
require_once 'config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id']) || !$_SESSION['is_student']) {
    header("Location: student_login.php");
    exit();
}

// Get student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

// Get student's subjects and grades
try {
    // First check if grades table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'grades'");
    $gradesTableExists = $stmt->rowCount() > 0;

    if ($gradesTableExists) {
        $stmt = $pdo->prepare("
            SELECT s.name as subject_name, g.grade, g.date
            FROM subjects s
            JOIN student_subjects ss ON s.id = ss.subject_id
            LEFT JOIN grades g ON ss.student_id = g.student_id AND s.id = g.subject_id
            WHERE ss.student_id = ?
            ORDER BY s.name
        ");
    } else {
        // If grades table doesn't exist, just get subjects
        $stmt = $pdo->prepare("
            SELECT s.name as subject_name, NULL as grade, NULL as date
            FROM subjects s
            JOIN student_subjects ss ON s.id = ss.subject_id
            WHERE ss.student_id = ?
            ORDER BY s.name
        ");
    }
    
    $stmt->execute([$_SESSION['student_id']]);
    $subjects = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle any database errors
    $error = "Došlo je do greške pri učitavanju predmeta i ocena.";
    $subjects = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Studentski Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard me-2"></i>
                Studentski Portal
            </a>
            <div class="navbar-nav ms-auto">
                <a href="student_change_password.php" class="nav-link">
                    <i class="bi bi-key me-2"></i>
                    Promeni Lozinku
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Odjavi se
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-person-circle me-2"></i>
                            Informacije o Studentu
                        </h5>
                        <p class="card-text">
                            <strong>Ime:</strong> <?php echo htmlspecialchars($student['name']); ?><br>
                            <strong>Broj Indeksa:</strong> <?php echo htmlspecialchars($student['student_id']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?><br>
                            <strong>Telefon:</strong> <?php echo htmlspecialchars($student['phone']); ?><br>
                            <strong>Smer:</strong> <?php echo htmlspecialchars($student['course']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-journal-text me-2"></i>
                            Predmeti i Ocene
                        </h5>
                        <?php if(empty($subjects)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Trenutno nema dodeljenih predmeta.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Predmet</th>
                                            <th>Ocena</th>
                                            <th>Datum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                            <td>
                                                <?php if ($subject['grade']): ?>
                                                    <span class="badge bg-success"><?php echo htmlspecialchars($subject['grade']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nije ocenjeno</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $subject['date'] ? date('d.m.Y', strtotime($subject['date'])) : '-'; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
