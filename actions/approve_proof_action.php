<?php
// This script handles the approval of a waste proof submission.

session_start();
require_once '../config/db.php';

// --- SECURITY CHECK (Admins Only) ---
// First, check if a user is even logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../admin.php?error=You must be logged in.');
    exit();
}
// Then, verify they are an admin.
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
    // Get the data from the form on admin.php
    $proof_id = $_POST['proof_id'];
    $user_id_to_reward = $_POST['user_id'];
    $coins_to_award = $_POST['coins_awarded'];

    // Basic validation
    if (empty($proof_id) || empty($user_id_to_reward) || empty($coins_to_award) || !is_numeric($coins_to_award) || $coins_to_award <= 0) {
        header('Location: ../admin.php?error=Invalid data provided.');
        exit();
    }

    // Use a database transaction to ensure both updates succeed or neither do.
    $pdo->beginTransaction();

    try {
        // Step 1: Update the waste_proofs table.
        // Set the status to 'approved' and record how many coins were awarded.
        $sql1 = "UPDATE waste_proofs SET status = 'approved', coins_awarded = :coins_awarded WHERE id = :proof_id";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            'coins_awarded' => $coins_to_award,
            'proof_id' => $proof_id
        ]);

        // Step 2: Update the users table.
        // Add the awarded coins to the user's current balance.
        $sql2 = "UPDATE users SET wastecoins = wastecoins + :coins_awarded WHERE id = :user_id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([
            'coins_awarded' => $coins_to_award,
            'user_id' => $user_id_to_reward
        ]);

        // If both queries were successful, commit the changes to the database.
        $pdo->commit();

        header('Location: ../admin.php?success=Submission approved and coins awarded!');
        exit();

    } catch (PDOException $e) {
        // If anything goes wrong, roll back the transaction so the database is not left in a half-updated state.
        $pdo->rollBack();
        header('Location: ../admin.php?error=An error occurred: ' . $e->getMessage());
        exit();
    }
} else {
    // Redirect if accessed directly
    header('Location: ../admin.php');
    exit();
}
?>
