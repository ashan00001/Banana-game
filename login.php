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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['new_username'];
    $password = $_POST['new_password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        $message = '<p class="error">Username already taken.</p>';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->execute(['username' => $username, 'password' => $hashed_password]);
        $message = '<p class="success">Registration successful! You can now log in.</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Banana Puzzle Game</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleForm() {
            document.getElementById("loginForm").classList.toggle("hidden");
            document.getElementById("registerForm").classList.toggle("hidden");
        }
    </script>
    <style>
        .hidden { display: none; }
        .container { text-align: center; width: 300px; margin: auto; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Banana Puzzle Game</h2>

        
        <?php if (!empty($message)) echo $message; ?>
        <div id="loginForm">
            <h3>Login</h3>
            <form action="" method="POST">
                <label for="username">Username:</label>
                <input type="text" name="username" required><br><br>
                <label for="password">Password:</label>
                <input type="password" name="password" required><br><br>
                <button type="submit" name="login">Login</button>
            </form>
            <p>Don't have an account? <a href="#" onclick="toggleForm()">Create an account</a></p>
        </div>
        <div id="registerForm" class="hidden">
            <h3>Create an Account</h3>
            <form action="" method="POST">
                <label for="new_username">Username:</label>
                <input type="text" name="new_username" required><br><br>
                <label for="new_password">Password:</label>
                <input type="password" name="new_password" required><br><br>
                <button type="submit" name="register">Register</button>
            </form>
            <p>Already have an account? <a href="#" onclick="toggleForm()">Login here</a></p>
        </div>
    </div>
</body>
</html>