<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/plain");

echo "PHP_OK\n";
echo "sigla=" . ($_GET['sigla'] ?? 'NULL') . "\n";
echo "numero=" . ($_GET['numero'] ?? 'NULL') . "\n";