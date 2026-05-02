
<?php 
// We must start the session on every page to remember the user.
session_start(); 
require_once 'config/db.php';
require_once 'actions/fetch_items_action.php';

// Fetch marketplace items
$marketplace_items = getAvailableItems($pdo);

// Fetch user's actual balance if logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_balance = $pdo->prepare("SELECT wastecoins, qr_token FROM users WHERE id = :user_id");
        $stmt_balance->execute(['user_id' => $_SESSION['user_id']]);
        $user_data = $stmt_balance->fetch();
        $user_balance = $user_data['wastecoins'] ?? 0;
        
        // Generate a new qr_token if they don't have one
        if (empty($user_data['qr_token'])) {
            $new_token = bin2hex(random_bytes(16));
            $stmt_update_token = $pdo->prepare("UPDATE users SET qr_token = :token WHERE id = :id");
            $stmt_update_token->execute(['token' => $new_token, 'id' => $_SESSION['user_id']]);
            $qr_token = $new_token;
        } else {
            $qr_token = $user_data['qr_token'];
        }

    } catch (PDOException $e) {
        $user_balance = 0;
        $qr_token = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLoop - Turn Waste into Value</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .page {
            display: none;
            animation: fadeIn 0.5s;
        }
        .page.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header & Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="#home" class="router-link text-2xl font-bold text-green-600">
                        EcoLoop
                    </a>
                    <!-- Desktop Menu -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="#home" class="router-link text-gray-600 hover:bg-green-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                            <a href="#marketplace" class="router-link text-gray-600 hover:bg-green-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Marketplace</a>
                            <a href="#rewards" class="router-link text-gray-600 hover:bg-green-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Rewards</a>
                             <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="#dashboard" class="router-link text-gray-600 hover:bg-green-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Desktop User Nav - NOW CONTROLLED BY PHP -->
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Shows when user is LOGGED IN -->
                            <span class="text-gray-700 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                            <a href="actions/logout_action.php" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md text-sm font-medium">Logout</a>
                        <?php else: ?>
                            <!-- Shows when user is LOGGED OUT -->
                            <a href="#login" class="router-link text-gray-600 hover:bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                            <a href="#register" class="router-link ml-4 text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-md text-sm font-medium">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden">
                    <button type="button" id="mobile-menu-button" class="bg-gray-100 inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-white hover:bg-green-500 focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#home" class="router-link mobile-link text-gray-600 hover:bg-green-500 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="#marketplace" class="router-link mobile-link text-gray-600 hover:bg-green-500 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Marketplace</a>
                <a href="#rewards" class="router-link mobile-link text-gray-600 hover:bg-green-500 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Rewards</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#dashboard" class="router-link mobile-link text-gray-600 hover:bg-green-500 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <?php endif; ?>
            </div>
            <!-- Mobile User Nav - NOW CONTROLLED BY PHP -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="px-5 pb-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-base font-medium text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <a href="actions/logout_action.php" class="mobile-link mt-2 block w-full text-center font-medium text-red-500 bg-red-50 hover:bg-red-100 p-3 rounded-md">Logout</a>
                <?php else: ?>
                    <a href="#login" class="router-link mobile-link block w-full text-left text-gray-600 hover:bg-gray-100 p-3 rounded-md text-base font-medium">Login</a>
                    <a href="#register" class="router-link mobile-link mt-2 block w-full text-center text-white bg-green-600 hover:bg-green-700 px-4 py-3 rounded-md text-base font-medium">Sign Up</a>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        <?php 
            // --- Display Success or Error Messages passed in URL ---
            if (isset($_GET['error'])): ?>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
                    </div>
                </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 my-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($_GET['success']); ?></span>
                    </div>
                </div>
        <?php endif; ?>

        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Home Page -->
            <div id="home" class="page">
                <div class="bg-green-600 text-white rounded-lg shadow-xl p-8 md:p-12 text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">Turn Your Waste into Real Value</h1>
                    <p class="text-lg md:text-xl mb-8">Join EcoLoop and get rewarded for recycling, trade items in our community marketplace, and enjoy discounts at local stores.</p>
                    <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="#register" class="router-link bg-white text-green-600 font-bold py-3 px-6 rounded-lg text-lg hover:bg-gray-100 transition duration-300">Get Started</a>
                        <a href="#marketplace" class="router-link bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg hover:bg-green-800 transition duration-300">Explore Marketplace</a>
                    </div>
                </div>

                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-md text-center">
                        <div class="text-green-500 mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-1 1m5-5l1-1m0 0l1 1m-1-1l-1-1m-1 5l-1-1m5 5l1 1m0-1l-1 1" /></svg></div>
                        <h3 class="text-xl font-bold mb-2">Earn WasteCoins</h3>
                        <p class="text-gray-600">Correctly segregate your waste, upload a photo as proof, and earn WasteCoins directly in your digital wallet.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md text-center">
                        <div class="text-blue-500 mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg></div>
                        <h3 class="text-xl font-bold mb-2">Trade in Marketplace</h3>
                        <p class="text-gray-600">Give your old items a new life. List them on our marketplace and earn more WasteCoins from fellow community members.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md text-center">
                        <div class="text-orange-500 mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></div>
                        <h3 class="text-xl font-bold mb-2">Redeem Rewards</h3>
                        <p class="text-gray-600">Use your earned WasteCoins for real discounts at partnered local cafes, stores, and businesses.</p>
                    </div>
                </div>
            </div>

            <!-- Login Page -->
            <div id="login" class="page">
                <div class="flex items-center justify-center">
                    <div class="w-full max-w-md">
                        <!-- UPDATED LOGIN FORM -->
                        <form action="actions/login_action.php" method="POST" class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
                            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Welcome Back!</h2>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500" id="email" name="email" type="email" placeholder="you@example.com" required>
                            </div>
                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500" id="password" name="password" type="password" placeholder="******************" required>
                            </div>
                            <div class="flex items-center justify-between">
                                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline w-full" type="submit">Sign In</button>
                            </div>
                            <p class="text-center text-gray-500 text-sm mt-6">Don't have an account? <a class="font-bold text-green-600 hover:text-green-800 router-link" href="#register">Sign Up</a></p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Registration Page -->
            <div id="register" class="page">
                 <div class="flex items-center justify-center">
                    <div class="w-full max-w-md">
                        <!-- UPDATED REGISTRATION FORM -->
                        <form action="actions/register_action.php" method="POST" class="bg-white shadow-lg rounded-xl px-8 pt-6 pb-8 mb-4">
                            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Join the EcoLoop Community</h2>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="reg-username">Username</label>
                                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500" id="reg-username" name="username" type="text" placeholder="Your Username" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="reg-email">Email</label>
                                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500" id="reg-email" name="email" type="email" placeholder="you@example.com" required>
                            </div>
                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="reg-password">Password</label>
                                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500" id="reg-password" name="password" type="password" placeholder="******************" required>
                            </div>
                            <div class="flex items-center justify-between">
                                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline w-full" type="submit">Create Account</button>
                            </div>
                             <p class="text-center text-gray-500 text-sm mt-6">Already have an account? <a class="font-bold text-green-600 hover:text-green-800 router-link" href="#login">Sign In</a></p>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Dashboard Page - Now only shown if logged in -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div id="dashboard" class="page">
                <div class="space-y-8">
                    <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold mb-4">Your Wallet</h2>
                        <p class="text-5xl font-bold text-green-600"><?php echo htmlspecialchars($user_balance ?? 0); ?> <span class="text-3xl text-gray-500">WC</span></p>
                        <p class="text-gray-500 mt-2">Your available WasteCoin balance.</p>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center justify-center">
                        <h2 class="text-xl font-bold mb-4">Redeem in Stores</h2>
                        <!-- Optimization: Added loading="lazy" to defer fetching the external QR code image until the dashboard tab is visible -->
                        <img loading="lazy" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=ecoloop_user:<?php echo urlencode($_SESSION['user_id']); ?>_token:<?php echo urlencode($qr_token); ?>" alt="Your QR Code">
                        <p class="text-gray-500 mt-2 text-center">Show this QR code to partner stores to get discounts.</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold mb-4">Earn More Coins</h2>
                        <p class="text-gray-600 mb-4">Upload a photo of your segregated waste before pickup to get it verified and earn WasteCoins.</p>
                        <form action="actions/upload_proof_action.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label for="proof_image" class="block text-sm font-medium text-gray-700">Proof Image</label>
                                <input type="file" name="proof_image" id="proof_image" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                            </div>
                             <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2"></textarea>
                            </div>
                             <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2">
                                    <option value="General">General</option>
                                    <option value="Plastic">Plastic</option>
                                    <option value="E-Waste">E-Waste</option>
                                    <option value="Organic">Organic (For Partnered Factories)</option>
                                </select>
                            </div>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Upload for Verification</button>
                        </form>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold mb-4">Sell on Marketplace</h2>
                        <p class="text-gray-600 mb-4">Got something you don't need? List it here for WasteCoins.</p>
                        <form action="actions/list_item_action.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="item_title" class="block text-sm font-medium text-gray-700">Item Title</label>
                                    <input type="text" name="item_title" id="item_title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2">
                                </div>
                                <div>
                                    <label for="item_price" class="block text-sm font-medium text-gray-700">Price (in WasteCoins)</label>
                                    <input type="number" name="item_price" id="item_price" min="1" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2">
                                </div>
                            </div>
                            <div>
                                <label for="item_description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="item_description" id="item_description" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2"></textarea>
                            </div>
                            <div>
                                <label for="item_image" class="block text-sm font-medium text-gray-700">Item Image</label>
                                <input type="file" name="item_image" id="item_image" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">List Item</button>
                        </form>
                    </div>

                </div>
            </div>
            <?php endif; ?>
            
            <!-- Marketplace Page -->
            <div id="marketplace" class="page">
                 <div class="bg-gray-50 p-4 sm:p-6 lg:p-8 rounded-xl min-h-screen">
                    <div class="max-w-7xl mx-auto">
                        <div class="mb-8 text-center">
                            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800">Community Marketplace</h1>
                            <p class="mt-2 text-lg text-gray-600">Use your WasteCoins to get pre-loved items from other EcoLoop members.</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                            <?php if (empty($marketplace_items)): ?>
                                <p class="text-gray-500 col-span-full text-center py-8">No items currently available in the marketplace.</p>
                            <?php else: ?>
                                <?php foreach ($marketplace_items as $item): ?>
                                    <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col group transition-all duration-300 hover:shadow-2xl hover:-translate-y-2">
                                        <div class="relative">
                                            <!-- Optimization: Added loading="lazy" to defer loading marketplace images until they are in viewport/visible -->
                                            <img loading="lazy" src="uploads/<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-56 object-cover">
                                            <div class="absolute top-2 right-2 bg-green-500 text-white text-sm font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($item['price']); ?> WC</div>
                                        </div>
                                        <div class="p-5 flex flex-col flex-grow">
                                            <h3 class="text-xl font-bold text-gray-800 truncate"><?php echo htmlspecialchars($item['title']); ?></h3>
                                            <p class="text-gray-500 text-sm mb-3">Listed by: <span class="font-medium text-gray-600"><?php echo htmlspecialchars($item['username']); ?></span></p>
                                            <p class="text-gray-600 flex-grow text-sm leading-relaxed line-clamp-3"><?php echo htmlspecialchars($item['description']); ?></p>
                                            <div class="mt-4">
                                                <form action="actions/buy_item_action.php" method="POST">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">Buy Item</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rewards Page -->
            <div id="rewards" class="page">
                <div class="space-y-6">
                    <h1 class="text-3xl font-bold">Partner Rewards</h1>
                    <p class="text-lg text-gray-600">Your WasteCoins are valuable! Use them to get real discounts and offers from our amazing partners.</p>
                </div>
            </div>

        </div>
    </main>
    
    <footer class="bg-white mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-gray-500">
            <p>&copy; 2024 EcoLoop. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // --- Simplified Router Logic ---
        document.addEventListener('DOMContentLoaded', () => {
            const pages = document.querySelectorAll('.page');

            function showPage(pageId) {
                pages.forEach(page => page.classList.remove('active'));
                const activePage = document.getElementById(pageId);
                if (activePage) {
                    activePage.classList.add('active');
                }
                window.scrollTo(0, 0);
            }

            function handleRouting() {
                const params = new URLSearchParams(window.location.search);
                const pageFromRedirect = params.get('page');
                const hash = window.location.hash || '#home';
                
                let pageId = hash.substring(1);

                if (pageFromRedirect) {
                    pageId = pageFromRedirect;
                    window.history.replaceState({}, document.title, window.location.pathname + "#" + pageId);
                }

                const dashboardPage = document.getElementById('dashboard');
                if (pageId === 'dashboard' && !dashboardPage) {
                    pageId = 'login';
                    window.location.hash = '#login';
                }
                
                showPage(pageId);
            }

            // --- Mobile Menu ---
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                mobileMenuButton.querySelectorAll('svg').forEach(icon => icon.classList.toggle('hidden'));
            });

            document.querySelectorAll('.mobile-link').forEach(link => {
                link.addEventListener('click', () => {
                    if (!mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        mobileMenuButton.querySelector('svg:first-child').classList.remove('hidden');
                        mobileMenuButton.querySelector('svg:last-child').classList.add('hidden');
                    }
                });
            });

            document.querySelectorAll('.router-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    const targetHash = new URL(link.href).hash;
                    if(window.location.hash !== targetHash){
                        window.location.hash = targetHash;
                    } else {
                        handleRouting();
                    }
                });
            });

            handleRouting();
            window.addEventListener('hashchange', handleRouting);
        });
    </script>
</body>
</html>
