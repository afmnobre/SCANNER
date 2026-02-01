<?php
require_once "../db.php";

$busca = $_GET['busca'] ?? '';
$edicoes = [];

if ($busca !== '') {
    $stmt = $pdo->prepare("
        SELECT Sigla, NomeSet, TipoEdicao,
               NumeroCardInicial, NumeroCardFinal, TotalCards
        FROM EdicoesScryfall
        WHERE NomeSet LIKE :busca
        ORDER BY NomeSet, NumeroCardInicial
    ");
    $stmt->execute(['busca' => "%$busca%"]);
    $edicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Edições Scryfall</title>

<style>
    body { font-family: Arial; margin: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
    th { background: #eee; }
    tr:hover { background-color: #f5f5f5; }
    input[type=number] { width: 90px; }
    .massa { background: #f0f0f0; padding: 10px; margin: 15px 0; }
</style>
</head>

<body>

<h2>Pesquisar Edições (Scryfall)</h2>

<form method="get">
    <input type="text"
           name="busca"
           size="50"
           placeholder="Digite o nome da edição"
           value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Pesquisar</button>
</form>

<?php if ($edicoes): ?>

<!-- EDIÇÃO EM MASSA -->
<div class="massa">
<form method="post" action="salvar_massa.php">
    <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">

    Inicial:
    <input type="number" name="NumeroCardInicial">

    Final:
    <input type="number" name="NumeroCardFinal">

    Total:
    <input type="number" name="TotalCards">

    <button type="submit">
        Atualizar todas as edições encontradas
    </button>
</form>
</div>

<table>
<tr>
    <th>Sigla</th>
    <th>Nome</th>
    <th>Tipo</th>
    <th>Inicial</th>
    <th>Final</th>
    <th>Total</th>
    <th>Ação</th>
</tr>

<?php foreach ($edicoes as $e): ?>
<tr>
<form method="post" action="salvar.php">
    <td><?= $e['Sigla'] ?></td>
    <td><?= htmlspecialchars($e['NomeSet']) ?></td>
    <td><?= htmlspecialchars($e['TipoEdicao']) ?></td>

    <td>
        <input type="number"
               name="NumeroCardInicial"
               value="<?= $e['NumeroCardInicial'] ?>">
    </td>

    <td>
        <input type="number"
               name="NumeroCardFinal"
               value="<?= $e['NumeroCardFinal'] ?>">
    </td>

    <td>
        <input type="number"
               name="TotalCards"
               value="<?= $e['TotalCards'] ?>">
    </td>

    <td>
        <input type="hidden" name="Sigla" value="<?= $e['Sigla'] ?>">
        <input type="hidden" name="TipoEdicao" value="<?= htmlspecialchars($e['TipoEdicao']) ?>">
        <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
        <button type="submit">Salvar</button>
    </td>
</form>
</tr>
<?php endforeach; ?>

</table>

<?php endif; ?>

</body>
</html>

➜  edicoes cat salvar.php
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