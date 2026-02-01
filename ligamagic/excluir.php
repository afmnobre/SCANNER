<?php
require '../db.php';
$pdo->prepare("DELETE FROM setsligamagic WHERE ID=?")->execute([$_GET['id']]);
header('Location: index.php');