<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $_SESSION['message'] = "Predmet je uspešno obrisan!";
    } catch(PDOException $e) {
        $_SESSION['message'] = "Greška pri brisanju predmeta.";
    }
}

header("Location: index.php?tab=subjects#subjects");
exit();
?>
