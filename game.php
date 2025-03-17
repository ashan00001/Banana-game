<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
require_once 'config.php';

// Fetch player stats
$stmt = $pdo->prepare('SELECT COUNT(*) AS total_games, SUM(CASE WHEN score = 0 THEN 1 ELSE 0 END) AS losses, MAX(score) AS highscore FROM high_scores WHERE username = ?');
$stmt->execute([$username]);
$result = $stmt->fetch();
$totalGames = $result['total_games'] ?? 0;
$totalLosses = $result['losses'] ?? 0;
$currentHighScore = $result['highscore'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banana Puzzle Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="profile-container" onclick="toggleProfile()">
        <p>👤 <?php echo htmlspecialchars($username); ?></p>
        <div id="profile-details">
            <p>Total Plays: <?php echo $totalGames; ?></p>
            <p>Losses: <?php echo $totalLosses; ?></p>
            <p>Current Score: <span id="current-score">0</span></p>
            <p>🏆 High Score: <?php echo $currentHighScore; ?></p>
        </div>
    </div>
    <div id="highscore-container">
        <p>🏆 High Score: <span id="current-highscore"><?php echo $currentHighScore; ?></span></p>
    </div>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Time Left: <span id="timer">30</span> seconds</p>
        <div id="game-container">
            <div id="puzzle">
                <img id="puzzle-image" src="" alt="Puzzle Image">
            </div>
            <input type="number" id="answer" placeholder="Enter the number of bananas">
            <button onclick="checkAnswer()">Submit</button>
            <p>Score: <span id="score">0</span></p>
            <button onclick="fetchBananaQuestion()">New Game</button>
            <button onclick="window.location.href='save_score.php'">View High Scores</button>
        </div>
        <button onclick="logout()">Logout</button>
        <audio id="logout-sound" src="logout.mp3"></audio>
    </div>
    <script>
        let score = 0;
        let timeLeft = 30;
        let timer;
        let correctAnswer = null;

        function toggleProfile() {
            const profileDetails = document.getElementById("profile-details");
            profileDetails.style.display = (profileDetails.style.display === "block") ? "none" : "block";
        }

        function startTimer() {
            clearInterval(timer);
            timeLeft = 30;
            document.getElementById('timer').textContent = timeLeft;
            timer = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    alert('Game Over! Time ran out.');
                    saveScore();
                }
            }, 1000);
        }

        async function fetchBananaQuestion() {
            try {
                const response = await fetch('https://marcconrad.com/uob/banana/api.php', { cache: "no-store" });
                if (!response.ok) throw new Error(`HTTP Error! Status: ${response.status}`);

                const data = await response.json();
                if (!data || !data.question || !data.solution) throw new Error("Invalid API response format");

                document.getElementById('puzzle-image').src = data.question;
                correctAnswer = parseInt(data.solution);
                document.getElementById('answer').value = '';
                startTimer();
            } catch (error) {
                console.error("Error fetching the puzzle:", error);
                document.getElementById('puzzle').innerHTML = "<p style='color: red;'>Failed to load puzzle. Please refresh.</p>";
            }
        }

        function checkAnswer() {
            const answer = parseInt(document.getElementById('answer').value);
            if (answer === correctAnswer) {
                score++;
                document.getElementById('score').textContent = score;
                document.getElementById('current-score').textContent = score;
                fetchBananaQuestion();
            }
        }

        function saveScore() {
            clearInterval(timer);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_score.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('username=<?php echo $username; ?>&score=' + score);
        }

        function logout() {
            const logoutSound = document.getElementById('logout-sound');
            logoutSound.play();
            setTimeout(() => {
                window.location.href = 'logout.php';
            }, 1000);
        }

        window.onload = function() {
            fetchBananaQuestion();
        };
        document.addEventListener("DOMContentLoaded", function () {
            const clickSound = new Audio("sounds/click.wav");

            function playClickSound() {
                const soundClone = clickSound.cloneNode();
                soundClone.volume = 1.0;
                soundClone.play().catch(error => console.error("Playback error:", error));
            }

            document.body.addEventListener("click", function (event) {
                if (event.target.tagName === "BUTTON") {
                    playClickSound();
                }
            });

            document.body.addEventListener("click", function unlockAudio() {
                clickSound.play().catch(() => {});
                document.body.removeEventListener("click", unlockAudio);
            });
        });
    </script>
</body>
</html>
