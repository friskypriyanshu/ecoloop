<?php
// This script handles listing an item on the marketplace.

session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login&error=You must be logged in to list an item.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['item_title']);
    $description = trim($_POST['item_description']);
    $price = $_POST['item_price'];

    // Validation
    if (empty($title) || empty($price) || !is_numeric($price) || $price <= 0) {
        header('Location: ../index.php?page=dashboard&error=Invalid item details.');
        exit();
    }

    // Image Upload Handling
    $image_path = '';
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/items/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $file_name = basename($_FILES['item_image']['name']);
        // Sanitize file name and add unique ID to prevent overwriting
        $file_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        $unique_name = uniqid() . '_' . $file_name;
        $target_file = $upload_dir . $unique_name;

        // Basic check for image type (optional but recommended)
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
             header('Location: ../index.php?page=dashboard&error=Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
             exit();
        }

        if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            $image_path = 'items/' . $unique_name; // Store relative path
        } else {
            header('Location: ../index.php?page=dashboard&error=Sorry, there was an error uploading your file.');
            exit();
        }
    } else {
        header('Location: ../index.php?page=dashboard&error=Item image is required.');
        exit();
    }

    try {
        // Insert item into database
        $sql = "INSERT INTO items (user_id, title, description, price, image_path) VALUES (:user_id, :title, :description, :price, :image_path)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'image_path' => $image_path
        ]);

        header('Location: ../index.php?page=marketplace&success=Item listed successfully!');
        exit();

    } catch (PDOException $e) {
        header('Location: ../index.php?page=dashboard&error=Database error: ' . $e->getMessage());
        exit();
    }
} else {
    header('Location: ../index.php?page=dashboard');
    exit();
}
?>
