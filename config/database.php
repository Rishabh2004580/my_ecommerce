<?php

function get_db_connection(): ?PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = 'localhost';
    $dbname = 'ecommerce';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Keep existing installations compatible with the gift catalog without
        // recreating tables or changing any existing product records.
        $columns = $pdo->query(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products'"
        )->fetchAll(PDO::FETCH_COLUMN);
        $missingColumns = [
            'mrp' => "ALTER TABLE products ADD COLUMN mrp DECIMAL(10,2) NULL AFTER price",
            'discount' => "ALTER TABLE products ADD COLUMN discount DECIMAL(5,2) NULL AFTER mrp",
            'occasion' => "ALTER TABLE products ADD COLUMN occasion VARCHAR(120) NULL AFTER category_id",
        ];
        foreach ($missingColumns as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                $pdo->exec($sql);
            }
        }

        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        return null;
    }
}
