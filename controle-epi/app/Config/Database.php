<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
private static ?PDO $instance = null;

private function __construct() {}

public static function getConnection(): PDO {
if (self::$instance === null) {
$host = 'localhost';
$dbname = 'sistema_epi';
$user = 'root';
$pass = '';

try {
self::$instance = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
} catch (PDOException $e) {
die("Erro na conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
}
}
return self::$instance;
}
}