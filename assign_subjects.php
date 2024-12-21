<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_GET['student_id'];

// Get student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['message'] = "Student nije pronađen.";
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subject_id'])) {
        $subject_id = $_POST['subject_id'];
        
        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT * FROM student_subjects WHERE student_id = ? AND subject_id = ?");
        $stmt->execute([$student_id, $subject_id]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
            try {
                $stmt->execute([$student_id, $subject_id]);
                $_SESSION['message'] = "Predmet je uspešno dodeljen studentu!";
                header("Location: assign_subjects.php?student_id=" . $student_id);
                exit();
            } catch(PDOException $e) {
                $error = "Greška pri dodeli predmeta.";
            }
        } else {
            $error = "Student je već upisan na ovaj predmet.";
            header("Location: assign_subjects.php?student_id=" . $student_id);
            exit();
        }
    }
}

// Get student's subjects
$stmt = $pdo->prepare("
    SELECT s.*, g.grade, g.date
    FROM subjects s
    JOIN student_subjects ss ON s.id = ss.subject_id
    LEFT JOIN grades g ON g.subject_id = s.id AND g.student_id = ?
    WHERE ss.student_id = ?
    ORDER BY s.name
");
$stmt->execute([$student_id, $student_id]);
$assigned_subjects = $stmt->fetchAll();

// Get available subjects
$available_subjects = $pdo->prepare("
    SELECT * FROM subjects 
    WHERE id NOT IN (
        SELECT subject_id 
        FROM student_subjects 
        WHERE student_id = ?
    )
");
$available_subjects->execute([$student_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dodela Predmeta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Table hover effects */
        .table tbody tr {
            transition: all 0.2s ease-in-out;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        /* Card hover effects */
        .card {
            transition: all 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Button hover effects */
        .btn {
            transition: all 0.2s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* List group item hover effects */
        .list-group-item {
            transition: all 0.2s ease-in-out;
            border-left: 3px solid transparent;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
            border-left: 3px solid #0d6efd;
            transform: translateX(3px);
        }
        
        /* Grade badge hover effect */
        .badge {
            transition: all 0.2s ease-in-out;
        }
        .badge:hover {
            transform: scale(1.1);
        }
        
        /* Subject list hover animation */
        .list-group-item form {
            opacity: 0.9;
            transition: all 0.2s ease-in-out;
        }
        .list-group-item:hover form {
            opacity: 1;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Sistem za Upravljanje Studentima
            </a>
            <div class="d-flex">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-people-fill me-2"></i>Studenti
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-2"></i>Odjavi se
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">
            <i class="bi bi-person-badge me-2"></i>
            Predmeti Studenta: <?php echo htmlspecialchars($student['name']); ?>
        </h2>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Dodaj Novi Predmet
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Izaberi Predmet</label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Izaberi predmet...</option>
                                    <?php while ($subject = $available_subjects->fetch()): ?>
                                        <option value="<?php echo $subject['id']; ?>">
                                            <?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['code']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Dodaj Predmet
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-check me-2"></i>Upisani Predmeti
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Šifra</th>
                                        <th>Naziv</th>
                                        <th>Ocena</th>
                                        <th>Akcije</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assigned_subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['code']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td>
                                            <?php echo $subject['grade'] ? htmlspecialchars($subject['grade']) : 'Nije ocenjen'; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="grade_subject.php?student_id=<?php echo $student_id; ?>&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-success me-2">
                                                    <i class="bi bi-check-lg"></i>
                                                    Oceni
                                                </a>
                                                <a href="remove_subject.php?student_id=<?php echo $student_id; ?>&subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Da li ste sigurni da želite da uklonite ovaj predmet?');">
                                                    <i class="bi bi-trash"></i>
                                                    Ukloni
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
