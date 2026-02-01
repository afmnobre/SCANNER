<?php
require '../db.php';

if($_POST['id']){
    $sql = "UPDATE setsligamagic SET ANO=?, NOME=?, SIGLA=? WHERE ID=?";
    $pdo->prepare($sql)->execute([
        $_POST['ano'], $_POST['nome'], $_POST['sigla'], $_POST['id']
    ]);
} else {
    $sql = "INSERT INTO setsligamagic (ANO,NOME,SIGLA) VALUES (?,?,?)";
    $pdo->prepare($sql)->execute([
        $_POST['ano'], $_POST['nome'], $_POST['sigla']
    ]);
}

header('Location: index.php');