<?php
session_start();
require 'db.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $gender   = $_POST['gender'] ?? '';
    $age      = (int)($_POST['age'] ?? 0);
    $meter_no = trim($_POST['meter_no'] ?? '');
    $is_verified = 0;

    $check = $pdo->prepare("SELECT COUNT(*) FROM residents WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetchColumn() > 0) {
        $errorMessage = 'Email already exists!';
    } else {
        $defaultPassword = bin2hex(random_bytes(4));
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO residents (name, email, contact, gender, age, meter_no, is_verified, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $inserted = $stmt->execute([$name, $email, $contact, $gender, $age, $meter_no, $is_verified, $hashedPassword]);

        if ($inserted) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USERNAME;
                $mail->Password   = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom(SMTP_USERNAME, 'AquaBill Admin');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Your Aqua-Bill Login Details';
                $mail->Body = "
                    <h3>Welcome to Aqua-Bill!</h3>
                    <p>Hello <strong>$name</strong>,</p>
                    <p>Your account has been successfully created.</p>
                    <p><strong>Login Credentials:</strong><br>
                    Email: $email<br>
                    Password: $defaultPassword</p>
                    <p>Please log in and change your password immediately.</p>
                ";

                $mail->send();
                $successMessage = 'Household added successfully! Login details sent to client email.';
                $_POST = [];
            } catch (Exception $e) {
                $errorMessage = "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            $errorMessage = "Failed to add household to the database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Household | Aqua-Bill</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0077B6; padding: 2rem; }
        .form-container { max-width: 500px; margin: 0 auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #1a237e; }
        label { display: block; margin-top: 1rem; font-weight: bold; }
        input, select { width: 100%; padding: 0.7rem; margin-top: 0.5rem; border: 1px solid #ccc; border-radius: 5px; }
        button { margin-top: 1.5rem; padding: 0.7rem 1.5rem; background: #0077B6; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; }
        button:hover { background: #005f8a; }
        .back-link { display: block; text-align: center; margin-top: 1rem; text-decoration: none; color: #1a237e; }
        .back-link:hover { text-decoration: underline; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Household</h2>

    <?php if ($successMessage): ?>
        <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">

        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <label for="contact">Contact Number:</label>
        <input type="text" name="contact" id="contact" required value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>">

        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
            <option value="">Select</option>
            <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
        </select>

        <label for="age">Age:</label>
        <input type="number" name="age" id="age" min="1" required value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">

        <label for="meter_no">Meter No.:</label>
        <input type="text" name="meter_no" id="meter_no" required value="<?php echo htmlspecialchars($_POST['meter_no'] ?? ''); ?>">

        <button type="submit">Add Household</button>
    </form>

    <a href="households.php" class="back-link">← Back to Household List</a>
</div>

</body>
</html>
