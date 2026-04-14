<?php
// This endpoint simulates a partner business scanning a QR code to deduct WasteCoins

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data passed from the QR scan
    $user_id = $_POST['user_id'] ?? null;
    $qr_token = $_POST['token'] ?? null;
    $coins_to_deduct = $_POST['coins_to_deduct'] ?? null;

    if (!$user_id || !$qr_token || !$coins_to_deduct || !is_numeric($coins_to_deduct) || $coins_to_deduct <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters. Need user_id, token, and coins_to_deduct.']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch user and lock the row
        $stmt_user = $pdo->prepare("SELECT wastecoins, qr_token FROM users WHERE id = :user_id FOR UPDATE");
        $stmt_user->execute(['user_id' => $user_id]);
        $user = $stmt_user->fetch();

        if (!$user) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            exit();
        }

        // Validate the QR token to prevent IDOR
        if ($user['qr_token'] !== $qr_token) {
             $pdo->rollBack();
             http_response_code(403);
             echo json_encode(['error' => 'Invalid QR token.']);
             exit();
        }

        if ($user['wastecoins'] < $coins_to_deduct) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Insufficient balance.']);
            exit();
        }

        // 2. Deduct coins
        $stmt_deduct = $pdo->prepare("UPDATE users SET wastecoins = wastecoins - :coins WHERE id = :user_id AND wastecoins >= :coins");
        $stmt_deduct->execute(['coins' => $coins_to_deduct, 'user_id' => $user_id]);

        if ($stmt_deduct->rowCount() === 0) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => 'Transaction failed due to concurrent update.']);
            exit();
        }

        // Optional: Log the redemption
        $stmt_log = $pdo->prepare("INSERT INTO redemptions (user_id, coins_deducted) VALUES (:user_id, :coins)");
        $stmt_log->execute(['user_id' => $user_id, 'coins' => $coins_to_deduct]);

        $pdo->commit();

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => "Successfully deducted $coins_to_deduct WasteCoins."]);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit();
}
?>
