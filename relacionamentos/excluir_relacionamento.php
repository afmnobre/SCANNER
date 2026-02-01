<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=MAGIC;charset=utf8mb4",
    "root",
    "Salvador2013@",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$d = json_decode(file_get_contents('php://input'), true);

if (!empty($d['ligamagic_id'])) {
    $stmt = $pdo->prepare(
        "DELETE FROM RelacionamentosEdicoes WHERE ligamagic_id = ?"
    );
    $stmt->execute([$d['ligamagic_id']]);
}