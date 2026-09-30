<?php
/**
 * Demo seed data for the Campus Lost & Found portal.
 *
 * Inserts 20 realistic active items (10 lost / 10 found) with the app's
 * required campus locations and a local SVG illustration per item.
 *
 * Safe to re-run: it only inserts when the items table is empty.
 *
 * Usage:  php database/seed_demo_items.php
 */

require_once __DIR__ . '/../config/database.php';

$db = getDB();

$existing = (int)$db->query('SELECT COUNT(*) FROM items')->fetchColumn();
if ($existing > 0) {
    fwrite(STDOUT, "Items table already has {$existing} rows; skipping demo seed.\n");
    exit(0);
}

$now = time();

$items = [
    // ------------------------------ LOST (10) ------------------------------
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Black Wallet',
        'category' => 'wallets',
        'description' => 'Black leather bifold wallet with a small silver clasp. Holds a few empty card slots and has a torn inner zip pocket.',
        'brand_model' => 'Leather bifold', 'color' => 'Black', 'serial_number' => 'WB-2219',
        'campus_zone' => 'K D Jadhav Sports Arena', 'specific_location' => 'Main gate, near entry steps',
        'date_lost_found' => '2026-09-26', 'time_period' => 'afternoon',
        'image_1' => 'demo-01.svg', 'custody_status' => null, 'status' => 'active', 'views' => 84,
        'hours_ago' => 74,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Blue Water Bottle',
        'category' => 'accessories',
        'description' => 'Matte blue 750ml insulated steel bottle with a faint sunrise sticker on the side and a black silicone sip lid.',
        'brand_model' => 'Insulated 750ml', 'color' => 'Blue', 'serial_number' => null,
        'campus_zone' => 'Music Room', 'specific_location' => 'Practice room 2, near the window sill',
        'date_lost_found' => '2026-09-27', 'time_period' => 'morning',
        'image_1' => 'demo-02.svg', 'custody_status' => null, 'status' => 'active', 'views' => 61,
        'hours_ago' => 50,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Wireless Earbuds',
        'category' => 'electronics',
        'description' => 'White true-wireless earbuds in a round charging case with a braided keyring loop. Case has a small scratch near the hinge.',
        'brand_model' => 'BOAT Airdopes 141', 'color' => 'White', 'serial_number' => 'BT-552-1098',
        'campus_zone' => 'Jimmy George Volleyball Court', 'specific_location' => 'North court, on the bench',
        'date_lost_found' => '2026-09-28', 'time_period' => 'evening',
        'image_1' => 'demo-03.svg', 'custody_status' => null, 'status' => 'active', 'views' => 132,
        'hours_ago' => 5,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'College ID Card',
        'category' => 'id-cards',
        'description' => 'Laminated student ID card on a navy lanyard with a white clip. Photo on the right and a barcode strip on the back.',
        'brand_model' => 'Campus ID card', 'color' => 'Navy', 'serial_number' => 'ID-2024-7718',
        'campus_zone' => 'Cosunselling Room', 'specific_location' => 'Lobby seating area',
        'date_lost_found' => '2026-09-25', 'time_period' => 'afternoon',
        'image_1' => 'demo-04.svg', 'custody_status' => null, 'status' => 'active', 'views' => 118,
        'hours_ago' => 26,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Black Backpack',
        'category' => 'backpacks',
        'description' => 'Black padded laptop backpack with a small tear in the front zip pocket and a red carabiner clipped to the main handle.',
        'brand_model' => 'Padded laptop pack', 'color' => 'Black', 'serial_number' => null,
        'campus_zone' => 'Major Dhyanchand Sports Arena - Basketball', 'specific_location' => 'Court B, sideline',
        'date_lost_found' => '2026-09-24', 'time_period' => 'morning',
        'image_1' => 'demo-05.svg', 'custody_status' => null, 'status' => 'active', 'views' => 96,
        'hours_ago' => 98,
    ],
    [
        'user_id' => 1, 'item_type' => 'lost', 'title' => 'Set of Keys',
        'category' => 'keys',
        'description' => 'Ring of three keys plus a gold key fob with a rubber dorm-room tag. One key has a small red plastic cap.',
        'brand_model' => null, 'color' => 'Silver', 'serial_number' => null,
        'campus_zone' => 'Janardhan Singh Traditional Sports Arena - A', 'specific_location' => 'Arena A, aisle 3',
        'date_lost_found' => '2026-09-26', 'time_period' => 'late-night',
        'image_1' => 'demo-06.svg', 'custody_status' => null, 'status' => 'active', 'views' => 73,
        'hours_ago' => 122,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Spectacles',
        'category' => 'accessories',
        'description' => 'Round tortoiseshell-framed reading glasses kept in a soft blue pouch. Mild prescription with a faint engraving on the left temple.',
        'brand_model' => 'Vintage round frames', 'color' => 'Brown', 'serial_number' => 'RX-8840',
        'campus_zone' => 'Badminton Court', 'specific_location' => 'Court 2, under the net',
        'date_lost_found' => '2026-09-23', 'time_period' => 'afternoon',
        'image_1' => 'demo-07.svg', 'custody_status' => null, 'status' => 'active', 'views' => 40,
        'hours_ago' => 146,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Notebook',
        'category' => 'books',
        'description' => 'A4 spiral notebook with a violet cover and a white label sticker reading PHY-301. First few pages are handwritten notes.',
        'brand_model' => 'A4 spiral notebook', 'color' => 'Violet', 'serial_number' => null,
        'campus_zone' => 'M S Dhoni Cricket Wicket', 'specific_location' => 'Pavilion benches',
        'date_lost_found' => '2026-09-22', 'time_period' => 'morning',
        'image_1' => 'demo-08.svg', 'custody_status' => null, 'status' => 'active', 'views' => 57,
        'hours_ago' => 170,
    ],
    [
        'user_id' => 1, 'item_type' => 'lost', 'title' => 'Smartphone',
        'category' => 'electronics',
        'description' => 'Black smartphone in a clear shock-proof case with a cracked corner. Lock screen shows a mountain wallpaper.',
        'brand_model' => 'Android 5G', 'color' => 'Black', 'serial_number' => 'IMEI-35xxxx-90',
        'campus_zone' => 'Kapildev Cricket Ground', 'specific_location' => 'South stands, row 12',
        'date_lost_found' => '2026-09-25', 'time_period' => 'evening',
        'image_1' => 'demo-09.svg', 'custody_status' => null, 'status' => 'active', 'views' => 145,
        'hours_ago' => 74,
    ],
    [
        'user_id' => 20, 'item_type' => 'lost', 'title' => 'Calculator',
        'category' => 'electronics',
        'description' => 'Scientific calculator with slightly worn number keys and a small doodle of a cat drawn on the back panel.',
        'brand_model' => 'Scientific calc', 'color' => 'Gray', 'serial_number' => null,
        'campus_zone' => 'Music Room', 'specific_location' => 'Piano room, chair 4',
        'date_lost_found' => '2026-09-24', 'time_period' => 'afternoon',
        'image_1' => 'demo-10.svg', 'custody_status' => null, 'status' => 'active', 'views' => 34,
        'hours_ago' => 194,
    ],
    // ------------------------------ FOUND (10) -----------------------------
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Sports Shoes',
        'category' => 'clothing',
        'description' => 'Red and white running shoes, size UK 9, with grey laces and a muddy scuff on the left toe.',
        'brand_model' => 'Running shoe UK 9', 'color' => 'Red', 'serial_number' => null,
        'campus_zone' => 'Janardhan Singh Traditional Sports Arena - B', 'specific_location' => 'Locker area, shelf 2',
        'date_lost_found' => '2026-09-27', 'time_period' => 'morning',
        'image_1' => 'demo-11.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 88,
        'hours_ago' => 98,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'USB Drive',
        'category' => 'electronics',
        'description' => 'Blue 32GB USB flash drive with a silver cap and a small lanyard hole. A sticker on the case reads PHYSICS SEM4.',
        'brand_model' => '32GB pendrive', 'color' => 'Blue', 'serial_number' => null,
        'campus_zone' => 'Badminton Court', 'specific_location' => 'Court 3, near the shoe rack corner',
        'date_lost_found' => '2026-09-26', 'time_period' => 'afternoon',
        'image_1' => 'demo-12.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 52,
        'hours_ago' => 122,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Watch',
        'category' => 'accessories',
        'description' => 'Navy analog wristwatch with a steel case and white dial. The leather strap shows light wear on the inside.',
        'brand_model' => 'Analog strap watch', 'color' => 'Navy', 'serial_number' => null,
        'campus_zone' => 'Jimmy George Beach Volleyball Court', 'specific_location' => 'Beach court, side stand',
        'date_lost_found' => '2026-09-28', 'time_period' => 'evening',
        'image_1' => 'demo-13.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 77,
        'hours_ago' => 50,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Lunch Box',
        'category' => 'other',
        'description' => 'Green two-tier steel lunch box with a black carrying handle and a small dent on the top tier.',
        'brand_model' => 'Two-tier steel', 'color' => 'Green', 'serial_number' => null,
        'campus_zone' => 'Music Room', 'specific_location' => 'Near the water cooler',
        'date_lost_found' => '2026-09-25', 'time_period' => 'afternoon',
        'image_1' => 'demo-14.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 29,
        'hours_ago' => 146,
    ],
    [
        'user_id' => 1, 'item_type' => 'found', 'title' => 'Hoodie',
        'category' => 'clothing',
        'description' => 'Violet university hoodie, size M, with drawstrings and a faint white print on the chest. Freshly laundered and stored.',
        'brand_model' => 'Campus hoodie, size M', 'color' => 'Violet', 'serial_number' => null,
        'campus_zone' => 'Major Dhyanchand Sports Arena - Basketball', 'specific_location' => 'Court A, bleachers',
        'date_lost_found' => '2026-09-27', 'time_period' => 'evening',
        'image_1' => 'demo-15.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 102,
        'hours_ago' => 26,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Umbrella',
        'category' => 'other',
        'description' => 'Compact blue folding umbrella with a curved hook handle and a small white logo printed on the canopy.',
        'brand_model' => 'Compact fold', 'color' => 'Blue', 'serial_number' => null,
        'campus_zone' => 'Cosunselling Room', 'specific_location' => 'By the entrance door',
        'date_lost_found' => '2026-09-24', 'time_period' => 'morning',
        'image_1' => 'demo-16.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 21,
        'hours_ago' => 218,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Charging Adapter',
        'category' => 'electronics',
        'description' => 'Grey 65W USB-C laptop charger with a two-metre cable coiled neatly. Minor scratches near the connector end.',
        'brand_model' => '65W USB-C charger', 'color' => 'Gray', 'serial_number' => null,
        'campus_zone' => 'Kapildev Cricket Ground', 'specific_location' => 'Scoreboard room',
        'date_lost_found' => '2026-09-26', 'time_period' => 'morning',
        'image_1' => 'demo-17.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 64,
        'hours_ago' => 170,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Cricket Bat',
        'category' => 'other',
        'description' => 'Grade willow cricket bat with a worn toe and a padded grip. Small initials are carved near the splice.',
        'brand_model' => 'Grade willow', 'color' => 'Brown', 'serial_number' => null,
        'campus_zone' => 'M S Dhoni Cricket Wicket', 'specific_location' => 'Practice nets',
        'date_lost_found' => '2026-09-23', 'time_period' => 'afternoon',
        'image_1' => 'demo-18.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 48,
        'hours_ago' => 242,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Earphones',
        'category' => 'electronics',
        'description' => 'White wired earphones with a 3.5mm jack; the right bud has a tiny red ring and the cable is slightly twisted.',
        'brand_model' => 'Wired earphones', 'color' => 'White', 'serial_number' => null,
        'campus_zone' => 'K D Jadhav Sports Arena', 'specific_location' => 'Under the hoop, court C',
        'date_lost_found' => '2026-09-28', 'time_period' => 'morning',
        'image_1' => 'demo-19.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 59,
        'hours_ago' => 74,
    ],
    [
        'user_id' => 20, 'item_type' => 'found', 'title' => 'Pencil Case',
        'category' => 'other',
        'description' => 'Yellow zipper pencil case containing blue and black ballpoint pens, a ruler and a set of highlighters.',
        'brand_model' => 'Zipper pouch', 'color' => 'Yellow', 'serial_number' => null,
        'campus_zone' => 'Janardhan Singh Traditional Sports Arena - A', 'specific_location' => 'Arena A, row 2',
        'date_lost_found' => '2026-09-29', 'time_period' => 'morning',
        'image_1' => 'demo-20.svg', 'custody_status' => 'At campus custody desk', 'status' => 'active', 'views' => 17,
        'hours_ago' => 6,
    ],
];

$sql = 'INSERT INTO items
    (user_id, item_type, title, category, description, brand_model, color,
     serial_number, campus_zone, specific_location, date_lost_found, time_period,
     image_1, custody_status, status, views, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

$stmt = $db->prepare($sql);

foreach ($items as $item) {
    $createdAt = date('Y-m-d H:i:s', $now - ($item['hours_ago'] * 3600));
    $stmt->execute([
        $item['user_id'],
        $item['item_type'],
        $item['title'],
        $item['category'],
        $item['description'],
        $item['brand_model'],
        $item['color'],
        $item['serial_number'],
        $item['campus_zone'],
        $item['specific_location'],
        $item['date_lost_found'],
        $item['time_period'],
        $item['image_1'],
        $item['custody_status'],
        $item['status'],
        $item['views'],
        $createdAt,
    ]);
}

$count = (int)$db->query('SELECT COUNT(*) FROM items')->fetchColumn();
fwrite(STDOUT, "Demo seed complete: {$count} items inserted.\n");