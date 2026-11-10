<?php
/* ============================================================
   db.php: one shared connection to the MySQL database (PDO).
   ============================================================ */
defined('APP_RUNNING') or exit('No direct access.');

/* Returns the PDO connection. It is made the first time it is
   needed and then reused, so a page only connects once. */
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // an SQL error throws an exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // rows come back as ['column' => value]
            PDO::ATTR_EMULATE_PREPARES => false,               // use real prepared statements
        ]);
        /* strict mode: MySQL refuses wrong data instead of quietly changing it */
        $pdo->exec("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        /* use the same clock as PHP (Asia/Dhaka is +06:00), even if the
           host's MySQL server runs on UTC */
        $pdo->exec('SET time_zone = ' . $pdo->quote(date('P')));
    }

    return $pdo;
}
