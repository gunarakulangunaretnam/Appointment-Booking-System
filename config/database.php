<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'appointment_booking');

function get_db()
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $port = defined('DB_PORT') ? (int) DB_PORT : 3307;
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
        } catch (mysqli_sql_exception $e) {
            if ($port !== 3306) {
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3306);
            } else {
                throw $e;
            }
        }
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(500);

        if (defined('API_REQUEST') && API_REQUEST) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => false,
                'error' => 'Unable to connect to the database.'
            ));
        } else {
            echo 'Unable to connect to the database. Import database.sql in phpMyAdmin and check config/database.php.';
        }
        exit;
    }
}
