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
    <script src="script.js"></script>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Time Left: <span id="timer">30</span> seconds</p>
        
        <div id="game-container">
            <div id="puzzle">
                <img id="puzzle-image" src="" alt="Puzzle Image">
                
            </div>
            <input type="text" id="answer" placeholder="Enter your answer">
                <button onclick="checkAnswer()">Submit</button>
            <p>Score: <span id="score">0</span></p>
            <button onclick="loadNextPuzzle()">Next Puzzle</button>
            <button onclick="window.location.href='save_score.php'">View High Scores</button>
        </div>
        <button onclick="logout()">Logout</button>
    </div>

    <script>
        let currentQuestion = 0;
        let score = 0;
        let timeLeft = 30;
        let timer;
        const puzzles = [
            { image: 'https://www.sanfoh.com/uob/banana/data/tce25c4945f7e898920620665can68.png', solution: '8' },
            { image: 'https://www.sanfoh.com/uob/banana/data/tcf78297aed7ad12fd47a985607n76.png', solution: '6' },
            { image: 'https://www.sanfoh.com/uob/banana/data/td34b97440e19a5a12be585150fn80.png', solution: '0' },
            { image: 'https://www.sanfoh.com/uob/banana/data/td395b9299083da2761ad6bde27n117.png', solution: '7' }
        ];

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

        function loadNextPuzzle() {
            if (currentQuestion >= puzzles.length) {
                alert('Game Over!');
                saveScore();
                return;
            }
            const puzzle = puzzles[currentQuestion];
            document.getElementById('puzzle-image').src = puzzle.image;
            document.getElementById('answer').value = '';
            currentQuestion++;
            startTimer();
        }

        function checkAnswer() {
            const answer = document.getElementById('answer').value;
            const correctAnswer = puzzles[currentQuestion - 1].solution;
            if (answer === correctAnswer) {
                score++;
                document.getElementById('score').textContent = score;
            }
            loadNextPuzzle();
        }

        function saveScore() {
            clearInterval(timer);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_score.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('username=<?php echo $username; ?>&score=' + score);
        }

        function logout() {
            window.location.href = 'login.php';
        }

        window.onload = function() {
            loadNextPuzzle();
        };
         
           document.addEventListener("DOMContentLoaded", function () {
    const clickSound = new Audio("sounds/click");

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
