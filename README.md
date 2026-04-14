# EcoLoop

EcoLoop is an innovative platform designed to incentivize proper waste segregation and recycling through a digital reward system. Users earn "WasteCoins" by submitting photographic proof of their segregated waste, which can then be used in a peer-to-peer community marketplace or redeemed for real-world discounts at partner businesses. It also features a B2B integration to route organic waste directly to partnered fertilizer factories.

## Features

1. **User Authentication & Profiles:** Secure registration and login system with email verification.
2. **Waste Tracking & Verification:**
   - Users upload images of sorted waste (General, Plastic, E-Waste, Organic).
   - Admins review and approve submissions, rewarding users with WasteCoins.
3. **Peer-to-Peer Marketplace:**
   - Users can list pre-loved items for sale.
   - Other users can purchase these items using their earned WasteCoins.
   - Secure, transaction-safe backend to prevent double-spending.
4. **Partner Rewards (QR Redemption):**
   - Users receive a unique, dynamically generated QR code.
   - Partner businesses can scan the code to securely deduct WasteCoins in exchange for discounts.
5. **B2B Organic Waste Factory Dashboard:**
   - A dedicated dashboard for partnered fertilizer factories to track and arrange pickups for approved organic waste.

## Tech Stack

*   **Frontend:** HTML5, Tailwind CSS (via CDN), Vanilla JavaScript.
*   **Backend:** PHP (Native).
*   **Database:** MySQL (interacted with via PDO).

## Requirements

*   PHP 7.4 or higher
*   MySQL 5.7 or higher
*   A local server environment like XAMPP, WAMP, or MAMP (or a configured Nginx/Apache setup).

## Installation & Setup

1.  **Clone the repository:**
    Clone this repository into your local server's document root (e.g., `htdocs` for XAMPP or `www` for WAMP).

2.  **Database Setup:**
    *   Create a new MySQL database named `ecoloop_db`.
    *   Execute the necessary SQL commands to create the tables. You can use the provided SQL scripts or manually set up the `users`, `waste_proofs`, `items`, and `redemptions` tables as defined in the source code.
    *   Example schema structure is available in `schema_updates.sql`.

3.  **Configuration:**
    *   Open `config/db.php`.
    *   Update the database credentials (`$host`, `$dbname`, `$username`, `$password`) if they differ from your local environment defaults.

4.  **Directory Permissions:**
    *   Ensure the `uploads/` and `uploads/items/` directories exist in the project root and have write permissions (e.g., `chmod 777 uploads`) so users can upload proof images and marketplace items.

5.  **Run the Application:**
    *   Navigate to the project directory in your web browser (e.g., `http://localhost/ecoloop/index.php`).

## Usage Notes

*   **Email Verification:** The registration script (`actions/register_action.php`) is configured to simulate sending an email. On a live server, ensure PHP's `mail()` function or an SMTP library is correctly configured. For local testing, you can manually set `is_verified = 1` in the database to bypass verification.
*   **Admin Access:** To access the admin panel (`admin.php`), you must manually set the `is_admin` flag to `1` for a specific user in the database.
*   **QR Scanner Endpoint:** The endpoint `actions/scan_qr_action.php` is available for partner integrations to POST user IDs, tokens, and coin deductions.

## License

This project is intended for educational and prototype purposes.
