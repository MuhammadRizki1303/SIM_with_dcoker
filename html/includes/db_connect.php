<?php

/**
 * Database Connection File
 * This file handles the connection to the MySQL database
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters
$host = 'db';  // Docker service name from docker-compose.yml
$dbname = 'mahasiswa_db';
$username = 'dbuser';
$password = 'dbpassword';

// Create connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Set session variable to track connection status
    $_SESSION['db_connected'] = true;
} catch (PDOException $e) {
    // Log error and set session variable
    error_log("Database Connection Error: " . $e->getMessage());
    $_SESSION['db_connected'] = false;
    $_SESSION['db_error'] = $e->getMessage();
}

/**
 * Function to get database connection
 * @return PDO Database connection
 */
function getConnection()
{
    global $pdo;
    return $pdo;
}

/**
 * Function to execute query and fetch all results
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Results
 */
function executeQuery($sql, $params = [])
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Function to execute query and fetch a single row
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|bool Single row result or false on failure
 */
function executeQuerySingle($sql, $params = [])
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Function to execute query that doesn't return results (INSERT, UPDATE, DELETE)
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return bool Success status
 */
function executeNonQuery($sql, $params = [])
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Non-Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Function to get last inserted ID
 * @return int Last inserted ID
 */
function getLastInsertId()
{
    global $pdo;
    return $pdo->lastInsertId();
}