<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO subjects (name, code, description) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$name, $code, $description]);
        http_response_code(200);
        echo "success";
    } catch(PDOException $e) {
        http_response_code(500);
        echo "error";
    }
    exit();
}

http_response_code(405);
exit('Method Not Allowed');
?>
