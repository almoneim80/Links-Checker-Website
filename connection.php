<?php

$dsn = 'mysql:host=localhost;dbname=link_doctor';
$username = 'root';
$password = '';
$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

try {
    $connection = new PDO($dsn, $username, $password, $options);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
}

?>