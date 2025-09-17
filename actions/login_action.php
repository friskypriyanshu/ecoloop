<?php
// This script handles the user login process.

// We MUST start a session to store login information.
session_start();

require_once '../config/db.php';

// Check if the form was submitted using POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the form.
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- Validation ---
    if (empty($email) || empty($password)) {
        header('Location: ../index.php?page=login&error=All fields are required.');
        exit();
    }

    try {
        // --- Find The User ---
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // --- Verify User and Password ---
        // 1. Check if a user with that email exists.
        // 2. If yes, check if the password matches the stored hash.
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // 3. Check if the account has been verified.
            if ($user['is_verified'] == 0) {
                header('Location: ../index.php?page=login&error=Please verify your email before logging in.');
                exit();
            }

            // --- Login Successful ---
            // Store user info in the session.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to the dashboard or home page.
            header('Location: ../index.php?page=dashboard');
            exit();

        } else {
            // --- Login Failed ---
            header('Location: ../index.php?page=login&error=Invalid email or password.');
            exit();
        }

    } catch (PDOException $e) {
        // Handle database errors.
        header('Location: ../index.php?page=login&error=An error occurred. Please try again.');
        exit();
    }
}
?>