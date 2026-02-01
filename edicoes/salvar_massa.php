<?php
require_once __DIR__ . "/../db.php";

$stmt = $pdo->prepare("
    UPDATE EdicoesScryfall
       SET NumeroCardInicial = :ini,
           NumeroCardFinal   = :fim,
           TotalCards        = :total
     WHERE Sigla = :sigla
       AND TipoEdicao = :tipo
");

$stmt->execute([
    'ini'   => $_POST['NumeroCardInicial'],
    'fim'   => $_POST['NumeroCardFinal'],
    'total' => $_POST['TotalCards'],
    'sigla' => $_POST['Sigla'],
    'tipo'  => $_POST['TipoEdicao']
]);

header("Location: index.php?busca=" . urlencode($_POST['busca']));
exit;

➜  edicoes cat salvar_massa.php
<?php
require_once "../db.php";

$sets = [];
$params = [];

if ($_POST['NumeroCardInicial'] !== '') {
    $sets[] = "NumeroCardInicial = :ini";
    $params['ini'] = $_POST['NumeroCardInicial'];
}

if ($_POST['NumeroCardFinal'] !== '') {
    $sets[] = "NumeroCardFinal = :fim";
    $params['fim'] = $_POST['NumeroCardFinal'];
}

if ($_POST['TotalCards'] !== '') {
    $sets[] = "TotalCards = :total";
    $params['total'] = $_POST['TotalCards'];
}

if ($sets) {
    $sql = "
        UPDATE EdicoesScryfall
           SET " . implode(', ', $sets) . "
         WHERE NomeSet LIKE :busca
    ";

    $params['busca'] = '%' . $_POST['busca'] . '%';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

header("Location: index.php?busca=" . urlencode($_POST['busca']));
exit;