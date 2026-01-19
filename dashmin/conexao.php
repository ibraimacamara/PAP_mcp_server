<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'gestor_escola';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}
?>