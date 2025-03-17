<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
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
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Time Left: <span id="timer">30</span> seconds</p>
        
        <div id="game-container">
            <div id="puzzle">
                <img id="puzzle-image" src="" alt="Puzzle Image" style="max-width: 300px;">
            </div>
            <input type="number" id="answer" placeholder="Enter the number of bananas">
            <button onclick="checkAnswer()">Submit</button>
            <p>Score: <span id="score">0</span></p>
            <button onclick="window.location.href='save_score.php'">View High Scores</button>
        </div>
        <button onclick="logout()">Logout</button>
    </div>

    <script>
        let score = 0;
        let timeLeft = 30;
        let timer;
        let correctAnswer = null;

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
                const proxyUrl = 'https://api.allorigins.win/get?url=';
                const apiUrl = encodeURIComponent('http://marcconrad.com/uob/banana/api.php');
                const response = await fetch(`${proxyUrl}${apiUrl}`);
                const responseData = await response.json();
                
                const data = JSON.parse(responseData.contents);
                console.log("API Response:", data);
                
                if (!data || !data.question || !data.solution) {
                    throw new Error("Invalid API response format");
                }
                
                document.getElementById('puzzle-image').src = data.question;
                correctAnswer = parseInt(data.solution);
                document.getElementById('answer').value = '';
                startTimer();
            } catch (error) {
                console.error("Error fetching the puzzle:", error);
                document.getElementById('puzzle-image').src = "";
                document.getElementById('puzzle').innerHTML = "<p style='color: red;'>Failed to load puzzle. Please refresh and try again.</p>";
            }
        }

        function checkAnswer() {
            const answer = parseInt(document.getElementById('answer').value);
            if (answer === correctAnswer) {
                score++;
                document.getElementById('score').textContent = score;
            }
            fetchBananaQuestion();
        }

        function saveScore() {
            clearInterval(timer);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_score.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('username=<?php echo $username; ?>&score=' + score);
        }

        function logout() {
            window.location.href = 'logout.php';
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
