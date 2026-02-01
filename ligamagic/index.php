<?php
require '../db.php';

$edicoes = $pdo->query("
    SELECT ID, ANO, NOME, SIGLA
    FROM setsligamagic
    ORDER BY ANO DESC, NOME
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>LigaMagic</title>
</head>
<body>

<h2>LigaMagic – Edições</h2>
<a href="form.php">➕ Nova edição</a>
<hr>

<table border="1" cellpadding="6">
<tr>
    <th>Ano</th>
    <th>Nome</th>
    <th>Sigla</th>
    <th>Ações</th>
</tr>

<?php foreach($edicoes as $e): ?>
<tr>
    <td><?= $e['ANO'] ?></td>
    <td><?= $e['NOME'] ?></td>
    <td><?= $e['SIGLA'] ?></td>
    <td>
        <a href="form.php?id=<?= $e['ID'] ?>">✏️</a>
        <a href="excluir.php?id=<?= $e['ID'] ?>" onclick="return confirm('Excluir?')">🗑</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>