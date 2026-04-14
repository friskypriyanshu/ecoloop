-- 1. Peer-to-Peer Marketplace Backend
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price INT NOT NULL, -- price in WasteCoins
    image_path VARCHAR(255),
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 2. QR Code Redemption System
-- We might add a column to 'users' table if we want a static QR token,
-- or just use the existing verification_token / generate dynamically.
-- Let's add a unique qr_token column for businesses to scan.
ALTER TABLE users ADD COLUMN qr_token VARCHAR(255) UNIQUE AFTER verification_token;

-- For tracking redemptions (optional but good practice):
CREATE TABLE IF NOT EXISTS redemptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    business_id INT, -- Assuming we might have a businesses table later
    coins_deducted INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Organic Waste & Fertilizer Factory B2B Integration
-- We need to add a category column to waste_proofs
ALTER TABLE waste_proofs ADD COLUMN category ENUM('General', 'Plastic', 'E-Waste', 'Organic') DEFAULT 'General' AFTER description;

