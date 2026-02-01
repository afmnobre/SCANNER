<?php
require '../db.php';

$edicao = [
    'Sigla' => '',
    'NomeSet' => '',
    'TipoEdicao' => '',
    'NumeroCardInicial' => '',
    'NumeroCardFinal' => '',
    'TotalCards' => ''
];

if(isset($_GET['sigla'], $_GET['ini'])){
    $stmt = $pdo->prepare("
        SELECT * FROM EdicoesScryfall
        WHERE Sigla = ? AND NumeroCardInicial = ?
    ");
    $stmt->execute([$_GET['sigla'], $_GET['ini']]);
    $edicao = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Scryfall – Formulário</title>
</head>
<body>

<h2><?= isset($_GET['sigla']) ? 'Editar' : 'Nova' ?> Edição Scryfall</h2>

<form method="post" action="salvar.php">
<input type="hidden" name="sigla_original" value="<?= $_GET['sigla'] ?? '' ?>">
<input type="hidden" name="ini_original" value="<?= $_GET['ini'] ?? '' ?>">

Sigla:<br>
<input name="sigla" value="<?= $edicao['Sigla'] ?>"><br><br>

Nome do Set:<br>
<input name="nomeset" value="<?= htmlspecialchars($edicao['NomeSet']) ?>"><br><br>

Tipo da Edição:<br>
<input name="tipo" value="<?= htmlspecialchars($edicao['TipoEdicao']) ?>"><br><br>

Número Inicial:<br>
<input name="inicio" type="number" value="<?= $edicao['NumeroCardInicial'] ?>"><br><br>

Número Final:<br>
<input name="fim" type="number" value="<?= $edicao['NumeroCardFinal'] ?>"><br><br>

Total de Cartas:<br>
<input name="total" type="number" value="<?= $edicao['TotalCards'] ?>"><br><br>

<button>Salvar</button>
</form>

</body>
</html>

