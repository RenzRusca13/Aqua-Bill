<?php
session_start();
require 'db.php';

// Check if logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Optional: check if resident exists before deleting

    $stmt = $pdo->prepare("DELETE FROM residents WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirect back to households
header("Location: households.php");
exit();
