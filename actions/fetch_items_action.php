<?php
// This script fetches available items for the marketplace.

require_once 'config/db.php'; // Path relative to where index.php includes it

function getAvailableItems($pdo) {
    try {
        $stmt = $pdo->query("SELECT items.*, users.username FROM items JOIN users ON items.user_id = users.id WHERE items.status = 'available' ORDER BY items.created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return []; // Return empty array on error, could log it
    }
}
?>
