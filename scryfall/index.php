<?php
require '../db.php';

$edicoes = $pdo->query("
    SELECT
        Sigla,
        NomeSet,
        TipoEdicao,
        NumeroCardInicial,
        NumeroCardFinal,
        TotalCards
    FROM EdicoesScryfall
    ORDER BY Sigla, NumeroCardInicial
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Scryfall – Edições</title>
<style>
table { border-collapse:collapse; width:100%; }
th, td { border:1px solid #ccc; padding:6px; font-size:14px; }
th { background:#eee; }
a { text-decoration:none; margin:0 4px; }
</style>
</head>
<body>

<h2>Scryfall – Edições</h2>

<a href="form.php">➕ Nova edição</a>
<hr>

<table>
<tr>
    <th>Sigla</th>
    <th>Nome do Set</th>
    <th>Tipo da Edição</th>
    <th>Início</th>
    <th>Fim</th>
    <th>Total</th>
    <th>Ações</th>
</tr>

<?php foreach($edicoes as $e): ?>
<tr>
    <td><?= $e['Sigla'] ?></td>
    <td><?= htmlspecialchars($e['NomeSet']) ?></td>
    <td><?= htmlspecialchars($e['TipoEdicao']) ?></td>
    <td><?= $e['NumeroCardInicial'] ?></td>
    <td><?= $e['NumeroCardFinal'] ?></td>
    <td><?= $e['TotalCards'] ?></td>
    <td>
        <a href="form.php?sigla=<?= $e['Sigla'] ?>&ini=<?= $e['NumeroCardInicial'] ?>">✏️</a>
        <a href="excluir.php?sigla=<?= $e['Sigla'] ?>&ini=<?= $e['NumeroCardInicial'] ?>"
           onclick="return confirm('Excluir esta edição?')">🗑</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>

