<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['student_id']) || !isset($_GET['subject_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_GET['student_id'];
$subject_id = $_GET['subject_id'];

$stmt = $pdo->prepare("DELETE FROM student_subjects WHERE student_id = ? AND subject_id = ?");
try {
    $stmt->execute([$student_id, $subject_id]);
    $_SESSION['message'] = "Predmet je uspešno uklonjen!";
} catch(PDOException $e) {
    $_SESSION['message'] = "Greška pri uklanjanju predmeta.";
}

header("Location: assign_subjects.php?student_id=" . $student_id);
exit();
?>
