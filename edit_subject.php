<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php#subjects");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$id]);
$subject = $stmt->fetch();

if (!$subject) {
    $_SESSION['message'] = "Predmet nije pronađen.";
    header("Location: index.php#subjects");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE subjects SET name = ?, code = ?, description = ? WHERE id = ?");
    try {
        $stmt->execute([$name, $code, $description, $id]);
        $_SESSION['message'] = "Predmet je uspešno izmenjen!";
        header("Location: index.php#subjects");
        exit();
    } catch(PDOException $e) {
        $error = "Greška pri izmeni predmeta. Molimo pokušajte ponovo.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Izmena Predmeta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Sistem za Upravljanje Studentima
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Odjavi se
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Izmena Predmeta
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Naziv Predmeta</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($subject['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="code" class="form-label">Šifra Predmeta</label>
                                <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($subject['code']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Opis</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subject['description']); ?></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Sačuvaj Izmene
                                </button>
                                <a href="index.php#subjects" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Nazad
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
