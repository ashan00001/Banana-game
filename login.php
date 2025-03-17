<?php
session_start();
if (isset($_SESSION['username'])) {
    header('Location: game.php');
    exit();
}
$message = "";
