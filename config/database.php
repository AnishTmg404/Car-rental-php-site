<?php
/**
 * Database Configuration
 * Car Rental System
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_rental_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions for database operations
function db_query($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

function db_fetch($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

function db_insert($table, $data) {
    global $pdo;

    $columns = implode(',', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    
    $stmt = db_query($sql, $data);
    return $stmt ? $pdo->lastInsertId() : false;
}

function db_update($table, $data, $where) {
    global $pdo;

    // Build SET clause
    $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));

    // Build WHERE clause
    $whereClause = implode(' AND ', array_map(fn($k) => "$k = :where_$k", array_keys($where)));

    // Merge parameters
    $params = [];
    foreach ($data as $k => $v) $params[":$k"] = $v;
    foreach ($where as $k => $v) $params[":where_$k"] = $v;

    $sql = "UPDATE $table SET $set WHERE $whereClause";
    $stmt = db_query($sql, $params);

    return $stmt ? $stmt->rowCount() : false;
}


function db_delete($table, $where, $params = []) {
    global $pdo;

    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}

function db_execute($sql, $params = []) {
    $stmt = db_query($sql, $params);
    // Returns true if the query ran, false otherwise
    return $stmt ? true : false;
}
?>
