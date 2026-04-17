<?php
session_start();
require_once 'config/db.php';

// In a real scenario, you'd check for a 'factory' role.
// For this prototype, we'll just allow admins or provide a public view for demo purposes.
$is_factory_admin = true; // Simulating a factory admin login

if (!$is_factory_admin) {
    die("Access Denied. Factory Admins only.");
}

$organic_waste_data = [];
try {
    // Fetch approved organic waste entries
    $stmt = $pdo->query("SELECT wp.*, u.username FROM waste_proofs wp JOIN users u ON wp.user_id = u.id WHERE wp.status = 'approved' AND wp.category = 'Organic' ORDER BY wp.created_at DESC");
    $organic_waste_data = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Factory Dashboard - Organic Waste</title>
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
                <div class="text-2xl font-bold text-green-700">EcoLoop Factory Dashboard</div>
                <div>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Back to Main Site</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Aggregated Organic Waste Supply</h1>
        <p class="mb-8 text-gray-600">Track and manage approved organic waste collected by EcoLoop users for fertilizer processing.</p>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($organic_waste_data) && !isset($error_message)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg">
                No approved organic waste data available at the moment.
            </div>
        <?php else: ?>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <?php foreach ($organic_waste_data as $entry): ?>
                        <li>
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                         <a href="uploads/<?php echo htmlspecialchars($entry['image_path']); ?>" target="_blank">
                                            <img loading="lazy" class="h-12 w-12 rounded object-cover" src="uploads/<?php echo htmlspecialchars($entry['image_path']); ?>" alt="Organic Waste">
                                         </a>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-green-600 truncate">
                                            Supplier: <?php echo htmlspecialchars($entry['username']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($entry['description'] ?: 'No description provided'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                    <p class="text-sm text-gray-500">
                                        Approved on: <?php echo date('M j, Y', strtotime($entry['created_at'])); ?>
                                    </p>
                                    <button class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Arrange Pickup
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
