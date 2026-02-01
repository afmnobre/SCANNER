<?php
require_once __DIR__ . '/db.php';

$sets = $pdo->query("
    SELECT DISTINCT Sigla
    FROM EdicoesScryfall
")->fetchAll(PDO::FETCH_COLUMN);

$dir = __DIR__ . '/cache/sets';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($sets as $sigla) {
    $sigla = strtolower(trim($sigla));
    $file = "$dir/$sigla.svg";

    if (file_exists($file)) {
        echo "✔ $sigla já existe\n";
        continue;
    }

    $url = "https://svgs.scryfall.io/sets/$sigla.svg";
    echo "⬇ Baixando $sigla... ";

    $svg = @file_get_contents($url);
    if ($svg) {
        file_put_contents($file, $svg);
        echo "OK\n";
    } else {
        echo "ERRO\n";
    }

    sleep(1); // evita bloqueio da Scryfall
}