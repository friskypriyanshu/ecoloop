<?php
// This script handles the rejection of a waste proof submission.

session_start();
require_once '../config/db.php';

// --- SECURITY CHECK (Admins Only) ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../admin.php?error=You must be logged in.');
    exit();
}
try {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['is_admin'] != 1) {
        header('Location: ../index.php?page=dashboard&error=Unauthorized access.');
        exit();
    }
} catch (PDOException $e) {
    die("Database error. Could not verify user permissions.");
}


// --- FORM PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proof_id = $_POST['proof_id'];

    if (empty($proof_id)) {
        header('Location: ../admin.php?error=Invalid data provided.');
        exit();
    }

    try {
        // Update the status of the proof to 'rejected'. No coins are awarded.
        $sql = "UPDATE waste_proofs SET status = 'rejected' WHERE id = :proof_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['proof_id' => $proof_id]);

        header('Location: ../admin.php?success=Submission has been rejected.');
        exit();

    } catch (PDOException $e) {
        header('Location: ../admin.php?error=A database error occurred.');
        exit();
    }
} else {
    // Redirect if accessed directly
    header('Location: ../admin.php');
    exit();
}
?>
