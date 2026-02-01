<?php
require '../db.php';

$data = [
    $_POST['sigla'],
    $_POST['nomeset'],
    $_POST['tipo'],
    $_POST['inicio'],
    $_POST['fim'],
    $_POST['total']
];

if($_POST['sigla_original']){
    $sql = "
        UPDATE EdicoesScryfall
        SET
            Sigla = ?,
            NomeSet = ?,
            TipoEdicao = ?,
            NumeroCardInicial = ?,
            NumeroCardFinal = ?,
            TotalCards = ?
        WHERE Sigla = ? AND NumeroCardInicial = ?
    ";
    $pdo->prepare($sql)->execute(array_merge(
        $data,
        [$_POST['sigla_original'], $_POST['ini_original']]
    ));
} else {
    $sql = "
        INSERT INTO EdicoesScryfall
        (Sigla, NomeSet, TipoEdicao, NumeroCardInicial, NumeroCardFinal, TotalCards)
        VALUES (?,?,?,?,?,?)
    ";
    $pdo->prepare($sql)->execute($data);
}

header('Location: index.php');

