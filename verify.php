<?php
// This page handles the account verification from the email link.

require_once 'config/db.php';

// We need to get the token from the URL.
// For example: verify.php?token=xxxxxxxxxxxx
$token = $_GET['token'] ?? null;

if (!$token) {
    die("Verification token not provided.");
}

try {
    // Find the user with this verification token
    $sql = "SELECT * FROM users WHERE verification_token = :token AND is_verified = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if ($user) {
        // User found, let's update their status to verified.
        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute(['id' => $user['id']]);

        // Redirect them to the login page with a success message
        header('Location: index.php?page=login&success=Account verified successfully! You can now log in.');
        exit();
    } else {
        // If the token is invalid or the account is already verified
        header('Location: index.php?page=login&error=Invalid or expired verification link.');
        exit();
    }

} catch (PDOException $e) {
    // Handle database errors
    die("Database error: " . $e->getMessage());
}
?>