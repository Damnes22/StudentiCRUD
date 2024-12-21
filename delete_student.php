<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $_SESSION['message'] = "Student je uspešno obrisan!";
    } catch(PDOException $e) {
        $_SESSION['message'] = "Greška pri brisanju studenta.";
    }
}

header("Location: index.php");
exit();
?>
