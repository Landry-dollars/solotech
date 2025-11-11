<?php
// db_migrate.php - simple safe migration for `products` table
// Run manually from CLI or browser (prefer CLI). It will create the table if missing
// and add any missing columns used by the application.

require_once __DIR__ . '/db.php';

function column_exists($conn, $table, $column) {
    $sql = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $db = $conn->real_escape_string($conn->query("SELECT DATABASE() as db")->fetch_object()->db);
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return ((int)$r['cnt']) > 0;
}

$table = 'products';

// Create table if it doesn't exist
$create = "CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) DEFAULT 0.00,
    `image` VARCHAR(255) DEFAULT NULL,
    `rate` INT DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($create) === false) {
    echo "Failed to create table: " . $conn->error . PHP_EOL;
    exit(1);
}

$needed = [
    'name' => "VARCHAR(255) NOT NULL",
    'category' => "VARCHAR(100) DEFAULT NULL",
    'description' => "TEXT",
    'price' => "DECIMAL(10,2) DEFAULT 0.00",
    'image' => "VARCHAR(255) DEFAULT NULL",
    'rate' => "INT DEFAULT 0",
];

foreach ($needed as $col => $def) {
    if (!column_exists($conn, $table, $col)) {
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}";
        if ($conn->query($sql) === false) {
            echo "Failed to add column {$col}: " . $conn->error . PHP_EOL;
        } else {
            echo "Added column {$col}." . PHP_EOL;
        }
    } else {
        echo "Column {$col} exists." . PHP_EOL;
    }
}

echo "Migration complete." . PHP_EOL;

?>