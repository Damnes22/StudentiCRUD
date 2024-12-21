<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistem za Upravljanje Studentima</title>
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
        
        /* Button hover enhancement */
        .btn {
            transition: all 0.2s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard me-2"></i>
                Sistem za Upravljanje Studentima
            </a>
            <div class="navbar-nav ms-auto">
                <a href="student_login.php" class="nav-link">
                    <i class="bi bi-person-circle me-2"></i>
                    Studentski Portal
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Odjavi se
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

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] !== 'subjects') ? 'active' : ''; ?>" href="#students" data-bs-toggle="tab" id="nav-students-tab">
                    <i class="bi bi-people-fill me-2"></i>Studenti
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'subjects') ? 'active' : ''; ?>" href="#subjects" data-bs-toggle="tab" id="nav-subjects-tab">
                    <i class="bi bi-book-fill me-2"></i>Predmeti
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Students Tab -->
            <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] !== 'subjects') ? 'show active' : ''; ?>" id="students">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>
                        Lista Studenata
                    </h2>
                    <a href="add_student.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        Dodaj Novog Studenta
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Pretraži studente...">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ime i Prezime</th>
                                        <th>Email</th>
                                        <th>Telefon</th>
                                        <th>Smer</th>
                                        <th>Akcije</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTable">
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
                                    while ($row = $stmt->fetch()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-2">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Izmeni
                                                </a>
                                                <a href="assign_subjects.php?student_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success me-2">
                                                    <i class="bi bi-book"></i>
                                                    Predmeti
                                                </a>
                                                <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Da li ste sigurni da želite da obrišete ovog studenta?');">
                                                    <i class="bi bi-trash"></i>
                                                    Obriši
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'subjects') ? 'show active' : ''; ?>" id="subjects">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-plus-circle me-2"></i>Dodaj Novi Predmet
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="add_subject.php">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Naziv Predmeta</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Šifra Predmeta</label>
                                        <input type="text" class="form-control" id="code" name="code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Opis</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-2"></i>Dodaj Predmet
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list me-2"></i>Lista Predmeta
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Šifra</th>
                                                <th>Naziv</th>
                                                <th>Opis</th>
                                                <th>Akcije</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
                                            while ($row = $stmt->fetch()) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['code']); ?></td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_subject.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-2">
                                                            <i class="bi bi-pencil-square"></i>
                                                            Izmeni
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-danger delete-subject" data-id="<?php echo $row['id']; ?>">
                                                            <i class="bi bi-trash"></i>
                                                            Obriši
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle subject deletion
            document.querySelectorAll('.delete-subject').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Da li ste sigurni da želite da obrišete ovaj predmet?')) {
                        const subjectId = this.dataset.id;
                        window.location.href = `delete_subject.php?id=${subjectId}&tab=subjects`;
                    }
                });
            });

            // Handle subject form submission
            const subjectForm = document.querySelector('form[action="add_subject.php"]');
            if (subjectForm) {
                subjectForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    fetch('add_subject.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(() => {
                        subjectForm.reset();
                        
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-check-circle me-2"></i>
                            Predmet je uspešno dodat!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        subjectForm.insertBefore(alertDiv, subjectForm.firstChild);
                        
                        window.location.href = 'index.php?tab=subjects#subjects';
                    })
                    .catch(() => {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Greška pri dodavanju predmeta.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        subjectForm.insertBefore(alertDiv, subjectForm.firstChild);
                    });
                });
            }

            const searchInput = document.getElementById('searchInput');
            const studentsTable = document.getElementById('studentsTable');

            searchInput.addEventListener('input', () => {
                const searchValue = searchInput.value.toLowerCase();
                const rows = studentsTable.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const columns = row.getElementsByTagName('td');

                    let isVisible = false;

                    for (let j = 0; j < columns.length; j++) {
                        const column = columns[j];
                        const text = column.textContent.toLowerCase();

                        if (text.includes(searchValue)) {
                            isVisible = true;
                            break;
                        }
                    }

                    row.style.display = isVisible ? '' : 'none';
                }
            });
        });
    </script>
</body>
</html>
