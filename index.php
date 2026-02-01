<?php
session_start();

if (isset($_POST['banco'])) {
    $_SESSION['BANCO_ATIVO'] = $_POST['banco'];
}

require_once __DIR__ . '/db.php';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Magic – Administração</title>
<style>
body { font-family:Arial; background:#f4f4f4; }
.box {
    width:400px;
    margin:60px auto;
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,.15);
}
a {
    display:block;
    padding:12px;
    margin:10px 0;
    text-decoration:none;
    background:#2d6cdf;
    color:#fff;
    text-align:center;
    border-radius:5px;
}
a:hover { background:#1f4fb8; }
</style>
</head>
<body>

<form method="post" style="margin-bottom:15px;">
    <label><strong>Banco de dados:</strong></label>

    <select name="banco" onchange="this.form.submit()">
        <option value="local" <?= ($_SESSION['BANCO_ATIVO'] ?? 'local') === 'local' ? 'selected' : '' ?>>
            Local (MAGIC)
        </option>

        <option value="aiven" <?= ($_SESSION['BANCO_ATIVO'] ?? '') === 'aiven' ? 'selected' : '' ?>>
            AIVEN (Produção)
        </option>
    </select>
</form>

<div style="padding:8px; background:#eee; width:fit-content;">
    <strong>Banco ativo:</strong>
    <?= strtoupper($_SESSION['BANCO_ATIVO'] ?? 'LOCAL') ?>
</div>

<div class="box">
    <h2>Administração Magic</h2>

    <a href="relacionamentos/relacionamentos.php">🔗 Relacionar Edições</a>
    <a href="ligamagic/index.php">📘 CRUD LigaMagic</a>
    <a href="scryfall/index.php">🌐 CRUD Scryfall</a>
    <a href="edicoes/index.php">🌐 Numero Card Edicoes Scryfall</a>
    <a href="comparar/comparar_cartas.php">🌐 COMPARAR Scryfall X Ligamagic</a>
    <a href="comparar/comparar_imagens.php">🌐 Comparação de Cartas Por Imagens</a>

</div>

</body>
</html>

