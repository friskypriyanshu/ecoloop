<?php
// This is the main page for the Admin Panel.

session_start();
require_once 'config/db.php';

// --- SECURITY CHECK ---
// 1. Check if a user is logged in at all.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login&error=You must be logged in to view this page.');
    exit();
}

// 2. Fetch the user's details from the DB to check their admin status.
// This is more secure than just trusting a session variable.
try {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    // 3. If the user is not an admin (is_admin is not 1), redirect them.
    if (!$user || $user['is_admin'] != 1) {
        header('Location: index.php?page=dashboard&error=You do not have permission to access this page.');
        exit();
    }
} catch (PDOException $e) {
    // If there's a database error, it's safer to deny access.
    die("Database error. Could not verify user permissions.");
}


// --- FETCH PENDING PROOFS ---
// If the security check passes, get all submissions with the 'pending' status.
$pending_proofs = [];
try {
    $stmt = $pdo->query("SELECT wp.*, u.username FROM waste_proofs wp JOIN users u ON wp.user_id = u.id WHERE wp.status = 'pending' ORDER BY wp.created_at ASC");
    $pending_proofs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - EcoLoop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="text-2xl font-bold text-gray-800">EcoLoop Admin Panel</div>
                <div>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Back to Main Site</a>
                    <a href="actions/logout_action.php" class="ml-4 text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Pending Submissions</h1>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($pending_proofs) && !isset($error_message)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg">
                There are no pending submissions to review. All caught up!
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($pending_proofs as $proof): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <a href="uploads/<?php echo htmlspecialchars($proof['image_path']); ?>" target="_blank">
                            <!-- Bolt: Native lazy loading defers offscreen images -->
                            <img src="uploads/<?php echo htmlspecialchars($proof['image_path']); ?>" alt="Waste Proof Image" loading="lazy" class="w-full h-48 object-cover">
                        </a>
                        <div class="p-4">
                            <p class="text-sm text-gray-500">Submitted by: <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($proof['username']); ?></span></p>
                            <p class="text-sm text-gray-500 mb-2">On: <?php echo date('F j, Y, g:i a', strtotime($proof['created_at'])); ?></p>
                            
                            <?php if (!empty($proof['description'])): ?>
                                <p class="text-gray-700 mb-4 h-20 overflow-y-auto border p-2 rounded-md"><?php echo htmlspecialchars($proof['description']); ?></p>
                            <?php else: ?>
                                <p class="text-gray-500 italic mb-4">No description provided.</p>
                            <?php endif; ?>

                            <div class="mt-4 space-y-2">
                                <!-- APPROVE FORM -->
                                <form action="actions/approve_proof_action.php" method="POST" class="flex items-center space-x-2">
                                    <input type="hidden" name="proof_id" value="<?php echo $proof['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $proof['user_id']; ?>">
                                    <input type="number" name="coins_awarded" min="1" value="10" class="w-24 border-gray-300 rounded-md shadow-sm text-sm" required>
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Approve</button>
                                </form>
                                <!-- REJECT FORM -->
                                <form action="actions/reject_proof_action.php" method="POST">
                                     <input type="hidden" name="proof_id" value="<?php echo $proof['id']; ?>">
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg text-sm">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
