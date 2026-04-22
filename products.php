<?php
require_once 'config/config.php';

// Redirect legacy products.php links to index.php
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ' . BASE_URL . '/index.php' . $queryString);
exit();
