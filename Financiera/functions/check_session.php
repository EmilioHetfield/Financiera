<?php
require_once 'session_handler.php';
session_start();

header('Content-Type: application/json');

$timeout = 30 * 60; // 30 minutos
$session_valid = isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity'] < $timeout) && 
                isset($_SESSION['user']);

echo json_encode(['valid' => $session_valid]);
?> 