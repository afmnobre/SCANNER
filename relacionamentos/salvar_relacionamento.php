<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* =========================
   Ler JSON corretamente
========================= */
$dados = json_decode(file_get_contents("php://input"), true);

$campos = [
    'ligamagic_id',
    'ligamagic_sigla',
    'scryfall_sigla',
    'scryfall_tipo',
    'scryfall_inicio'
];

foreach ($campos as $c) {
    if (!isset($dados[$c]) || $dados[$c] === '') {
        echo json_encode([
            'ok' => false,
            'error' => "Campo ausente ou vazio: $c"
        ]);
        exit;
    }
}

/* =========================
   Conexão
========================= */
$pdo = new PDO(
    "mysql:host=localhost;dbname=MAGIC;charset=utf8mb4",
    "root",
    "Salvador2013@",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/* =========================
   Inserção
========================= */
$stmt = $pdo->prepare("
    INSERT INTO RelacionamentosEdicoes
    (ligamagic_id, ligamagic_sigla, scryfall_sigla, scryfall_tipo, scryfall_inicio)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $dados['ligamagic_id'],
    $dados['ligamagic_sigla'],
    $dados['scryfall_sigla'],
    $dados['scryfall_tipo'],
    $dados['scryfall_inicio']
]);

echo json_encode(['ok' => true]);