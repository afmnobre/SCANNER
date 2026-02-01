<?php
$host = 'mysql-b7f3b8e-afmnobre.h.aivencloud.com';
$port = 17292;

$fp = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$fp) {
    echo "FALHA: $errno - $errstr";
} else {
    echo "OK: Porta acessível";
    fclose($fp);
}
