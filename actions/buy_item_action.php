<?php
// This script handles buying an item from the marketplace.

session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login&error=You must be logged in to buy an item.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $buyer_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'];

    if (empty($item_id)) {
        header('Location: ../index.php?page=marketplace&error=Invalid item.');
        exit();
    }

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // 1. Fetch the item details with a row lock to prevent concurrent purchases
        $stmt_item = $pdo->prepare("SELECT * FROM items WHERE id = :item_id AND status = 'available' FOR UPDATE");
        $stmt_item->execute(['item_id' => $item_id]);
        $item = $stmt_item->fetch();

        if (!$item) {
            $pdo->rollBack();
            header('Location: ../index.php?page=marketplace&error=Item is no longer available.');
            exit();
        }

        // Prevent buying your own item
        if ($item['user_id'] == $buyer_id) {
             $pdo->rollBack();
             header('Location: ../index.php?page=marketplace&error=You cannot buy your own item.');
             exit();
        }

        $seller_id = $item['user_id'];
        $price = $item['price'];

        // 2. Fetch the buyer's balance with a row lock
        $stmt_buyer = $pdo->prepare("SELECT wastecoins FROM users WHERE id = :buyer_id FOR UPDATE");
        $stmt_buyer->execute(['buyer_id' => $buyer_id]);
        $buyer = $stmt_buyer->fetch();

        if (!$buyer || $buyer['wastecoins'] < $price) {
            $pdo->rollBack();
            header('Location: ../index.php?page=marketplace&error=Insufficient WasteCoins to complete this purchase.');
            exit();
        }

        // 3. Deduct coins from buyer
        $stmt_deduct = $pdo->prepare("UPDATE users SET wastecoins = wastecoins - :price WHERE id = :buyer_id AND wastecoins >= :price");
        $stmt_deduct->execute(['price' => $price, 'buyer_id' => $buyer_id]);
        
        if ($stmt_deduct->rowCount() === 0) {
            $pdo->rollBack();
            header('Location: ../index.php?page=marketplace&error=Insufficient WasteCoins to complete this purchase.');
            exit();
        }

        // 4. Add coins to seller
        $stmt_add = $pdo->prepare("UPDATE users SET wastecoins = wastecoins + :price WHERE id = :seller_id");
        $stmt_add->execute(['price' => $price, 'seller_id' => $seller_id]);

        // 5. Update item status to 'sold'
        $stmt_update_item = $pdo->prepare("UPDATE items SET status = 'sold' WHERE id = :item_id");
        $stmt_update_item->execute(['item_id' => $item_id]);

        // Commit the transaction
        $pdo->commit();

        header('Location: ../index.php?page=marketplace&success=Item purchased successfully! You have claimed: ' . htmlspecialchars($item['title']));
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: ../index.php?page=marketplace&error=Transaction failed. Please try again.');
        exit();
    }
} else {
    header('Location: ../index.php?page=marketplace');
    exit();
}
?>
