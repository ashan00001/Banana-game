<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

$username = $_POST['username'] ?? '';
$score = $_POST['score'] ?? 0;

if (!empty($username)) {
    $stmt = $pdo->prepare('SELECT * FROM high_scores WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $existing_score = $stmt->fetch();

    if ($existing_score) {
        if ($existing_score['score'] < $score) {
            $stmt = $pdo->prepare('UPDATE high_scores SET score = :score WHERE username = :username');
            $stmt->execute(['score' => $score, 'username' => $username]);
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO high_scores (username, score) VALUES (:username, :score)');
        $stmt->execute(['username' => $username, 'score' => $score]);
    }
}

// Fetch top 3 high scores
$stmt = $pdo->query('SELECT username, score FROM high_scores ORDER BY score DESC LIMIT 3');
$high_scores = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top High Scores</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Top 3 High Scores</h2>
        <table border="1">
            <tr>
                <th>Rank</th>
                <th>User</th>
                <th>High Score</th>
            </tr>
            <?php
            $rank = 1;
            foreach ($high_scores as $row) {
                echo "<tr><td>{$rank}</td><td>" . htmlspecialchars($row['username']) . "</td><td>" . htmlspecialchars($row['score']) . "</td></tr>";
                $rank++;
            }
            ?>
        </table>
        <button onclick="window.location.href='game.php'">Back to Game</button>
    </div>
</body>
</html>
