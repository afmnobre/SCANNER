<?php
require '../db.php';

$pdo->prepare("
    DELETE FROM EdicoesScryfall
    WHERE Sigla = ? AND NumeroCardInicial = ?
")->execute([
    $_GET['sigla'],
    $_GET['ini']
]);

header('Location: index.php');

