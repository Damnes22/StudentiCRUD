<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['student_id']) || !isset($_GET['subject_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_GET['student_id'];
$subject_id = $_GET['subject_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grade = $_POST['grade'];
    
    // First, check if a grade already exists
    $stmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND subject_id = ?");
    $stmt->execute([$student_id, $subject_id]);
    $existing_grade = $stmt->fetch();
    
    try {
        if ($existing_grade) {
            // Update existing grade
            $stmt = $pdo->prepare("UPDATE grades SET grade = ?, date = CURRENT_DATE WHERE student_id = ? AND subject_id = ?");
            $stmt->execute([$grade, $student_id, $subject_id]);
        } else {
            // Insert new grade
            $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject_id, grade, date) VALUES (?, ?, ?, CURRENT_DATE)");
            $stmt->execute([$student_id, $subject_id, $grade]);
        }
        
        $_SESSION['message'] = "Ocena je uspešno uneta!";
        header("Location: assign_subjects.php?student_id=" . $student_id);
        exit();
    } catch(PDOException $e) {
        $error = "Greška pri unosu ocene: " . $e->getMessage();
    }
}

// Get student and subject info
$stmt = $pdo->prepare("
    SELECT s.name as student_name, sub.name as subject_name, sub.code, g.grade
    FROM students s
    JOIN student_subjects ss ON s.id = ss.student_id
    JOIN subjects sub ON sub.id = ss.subject_id
    LEFT JOIN grades g ON g.student_id = s.id AND g.subject_id = sub.id
    WHERE s.id = ? AND sub.id = ?
");
$stmt->execute([$student_id, $subject_id]);
$info = $stmt->fetch();

if (!$info) {
    $_SESSION['message'] = "Predmet nije pronađen za ovog studenta.";
    header("Location: assign_subjects.php?student_id=" . $student_id);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unos Ocene</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard me-2"></i>
                Sistem za Upravljanje Studentima
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Unos Ocene
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5>Informacije:</h5>
                            <p class="mb-1"><strong>Student:</strong> <?php echo htmlspecialchars($info['student_name']); ?></p>
                            <p class="mb-1"><strong>Predmet:</strong> <?php echo htmlspecialchars($info['subject_name']); ?> (<?php echo htmlspecialchars($info['code']); ?>)</p>
                            <p class="mb-1"><strong>Trenutna ocena:</strong> 
                                <?php if ($info['grade']): ?>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($info['grade']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nije ocenjen</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="grade" class="form-label">Nova Ocena</label>
                                <select class="form-select" id="grade" name="grade" required>
                                    <option value="">Izaberite ocenu</option>
                                    <?php for($i = 6; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($info['grade'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Sačuvaj Ocenu
                                </button>
                                <a href="assign_subjects.php?student_id=<?php echo htmlspecialchars($student_id); ?>" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Otkaži
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
