<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=MAGIC;charset=utf8mb4",
        "root",
        "Salvador2013@",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $d = json_decode(file_get_contents('php://input'), true);

    foreach ([
        'ligamagic_id',
        'ligamagic_sigla',
        'scryfall_sigla',
        'scryfall_tipo',
        'scryfall_inicio'
    ] as $c) {
        if (empty($d[$c])) {
            throw new Exception("Campo ausente: $c");
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO RelacionamentosEdicoes
        (ligamagic_id, ligamagic_sigla, scryfall_sigla, scryfall_tipo, scryfall_inicio)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $d['ligamagic_id'],
        $d['ligamagic_sigla'],
        $d['scryfall_sigla'],
        $d['scryfall_tipo'],
        $d['scryfall_inicio']
    ]);

    echo json_encode(['ok'=>true]);

} catch (Exception $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
