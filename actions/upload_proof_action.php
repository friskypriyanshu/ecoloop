<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login&error=You must be logged in.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'General';
    
    // Validate category
    $allowed_categories = ['General', 'Plastic', 'E-Waste', 'Organic'];
    if (!in_array($category, $allowed_categories)) {
        $category = 'General';
    }

    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES['proof_image']['name']);
        
        // Basic check for image type to prevent RCE
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
             header('Location: ../index.php?page=dashboard&error=Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
             exit();
        }

        $unique_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
        $target_file = $upload_dir . $unique_name;

        if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
            try {
                $sql = "INSERT INTO waste_proofs (user_id, image_path, description, category) VALUES (:user_id, :image_path, :description, :category)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $user_id,
                    'image_path' => $unique_name,
                    'description' => $description,
                    'category' => $category
                ]);

                header('Location: ../index.php?page=dashboard&success=Proof uploaded successfully and is pending approval.');
                exit();
            } catch (PDOException $e) {
                header('Location: ../index.php?page=dashboard&error=Database error: ' . $e->getMessage());
                exit();
            }
        } else {
            header('Location: ../index.php?page=dashboard&error=Failed to move uploaded file.');
            exit();
        }
    } else {
        header('Location: ../index.php?page=dashboard&error=Please select an image file to upload.');
        exit();
    }
}
?>
