<?php
require_once '../config/db.php';

// ... the rest of your registration code follows
// This script handles the user registration process.



// Check if the form was submitted using POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the form and sanitize it.
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // --- Validation ---
    if (empty($username) || empty($email) || empty($password)) {
        header('Location: ../index.php?page=register&error=All fields are required.');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../index.php?page=register&error=Invalid email format.');
        exit();
    }
    if (strlen($password) < 6) {
        header('Location: ../index.php?page=register&error=Password must be at least 6 characters long.');
        exit();
    }

    // --- Check if user already exists ---
    $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'email' => $email]);
    if ($stmt->fetch()) {
        header('Location: ../index.php?page=register&error=Username or email already exists.');
        exit();
    }

    // --- Create User ---
    // Securely hash the password.
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    // Generate a unique verification token.
    $verification_token = bin2hex(random_bytes(32));

    $sql = "INSERT INTO users (username, email, password_hash, verification_token) VALUES (:username, :email, :password_hash, :verification_token)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash,
            'verification_token' => $verification_token
        ]);

        // --- Send Verification Email ---
        // IMPORTANT: Update this link to match your server setup!
        $verify_link = "http://localhost/ecoloop/verify.php?token=" . $verification_token;
        $subject = "Verify your EcoLoop Account";
        $message = "Welcome to EcoLoop! Please click the link below to verify your account:\n\n" . $verify_link;
        $headers = "From: no-reply@ecoloop.com";

        // The mail() function might not work on localhost without configuration.
        // See README.md for details.
        if (mail($email, $subject, $message, $headers)) {
             header('Location: ../index.php?page=login&success=Registration successful! Please check your email to verify your account.');
        } else {
             // For development, we'll pretend the email sent successfully.
             header('Location: ../index.php?page=login&success=Registration successful! Please check your email to verify your account. (Email sending is disabled on localhost).');
        }
        exit();

    } catch (PDOException $e) {
        // In a real app, log this error.
        header('Location: ../index.php?page=register&error=An error occurred. Please try again.');
        exit();
    }
}
?>

