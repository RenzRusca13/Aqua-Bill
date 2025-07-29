<?php
session_start();
require 'db.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: households.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
$stmt->execute([$id]);
$resident = $stmt->fetch();

if (!$resident) {
    echo "Household not found.";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $age = $_POST['age'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $meter_no = $_POST['meter_no'] ?? '';

    $stmt = $pdo->prepare("UPDATE residents SET name = ?, email = ?, age = ?, gender = ?, contact = ?, meter_no = ? WHERE id = ?");
    $stmt->execute([$name, $email, $age, $gender, $contact, $meter_no, $id]);

    header("Location: households.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Household | Aqua-Bill</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0077B6;
            padding: 2rem;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #1a237e;
        }

        label {
            display: block;
            margin-top: 1rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        select {
            width: 100%;
            padding: 0.7rem;
            margin-top: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 1.5rem;
            padding: 0.7rem 1.5rem;
            background: #0077B6;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #005f90;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            text-decoration: none;
            color: #1a237e;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Household</h2>
    <form method="POST">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($resident['name'] ?? '') ?>" required>

        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($resident['email'] ?? '') ?>" required>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" value="<?= htmlspecialchars($resident['age'] ?? '') ?>">

        <label for="gender">Gender:</label>
        <select name="gender" id="gender">
            <option value="">-- Select Gender --</option>
            <option value="Male" <?= ($resident['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($resident['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>

        <label for="contact">Contact No.:</label>
        <input type="text" name="contact" id="contact" value="<?= htmlspecialchars($resident['contact'] ?? '') ?>">

        <label for="meter_no">Meter No:</label>
        <input type="text" name="meter_no" id="meter_no" value="<?= htmlspecialchars($resident['meter_no'] ?? '') ?>">

        <button type="submit">Update</button>
    </form>

    <a href="households.php" class="back-link">← Back to Household List</a>
</div>

</body>
</html>
