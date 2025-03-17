<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: game.php');
    exit();
}
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        header('Location: game.php');
        exit();
    } else {
        $message = '<p class="error">Invalid username or password.</p>';
    }
}