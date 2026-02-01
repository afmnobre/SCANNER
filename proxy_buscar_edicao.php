<?php
// Força headers HTTP corretos
header("Content-Type: text/plain; charset=utf-8");
header("Connection: close");

// Evita qualquer buffer estranho do servidor
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

$sigla  = $_GET['sigla']  ?? '';
$numero = $_GET['numero'] ?? '';

require_once "buscar_edicao.php";

// Garante envio real
$output = ob_get_clean();
echo $output;
flush();
exit;
