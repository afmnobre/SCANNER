<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/plain");

echo "PHP_OK\n";
echo "sigla=" . ($_GET['sigla'] ?? 'NULL') . "\n";
echo "numero=" . ($_GET['numero'] ?? 'NULL') . "\n";
    
header('Content-Type: application/json');

$host = "localhost";
$user = "sq_40883683";
$pass = "SALVADOR2013@";
$db   = "sq_40883686_MAGIC";

$sigla  = $_GET['sigla'] ?? '';
$numero = intval($_GET['numero'] ?? 0);

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB error"]);
    exit;
}

$sql = "
SELECT ligamagic_sigla, ligamagic_id
FROM vw_edicoes_resolvidas
WHERE scryfall_sigla = ?
AND ? BETWEEN NumeroCardInicial AND NumeroCardFinal
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $sigla, $numero);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["ligamagic_sigla" => null, "ligamagic_id" => null]);
}
