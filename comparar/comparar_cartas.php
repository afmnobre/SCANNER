<?php
        require_once __DIR__ . '/../db.php';

/* =========================
   INPUTS
========================= */
$buscaCarta = $_GET['carta'] ?? '';
$edicaoLiga = $_GET['ed_liga'] ?? '';
$edicaoScry = $_GET['ed_scry'] ?? '';

/* =========================
   EDIÇÕES
========================= */
$setsLiga = $pdo->query("
    SELECT SIGLA, NOME
    FROM setsligamagic
    ORDER BY NOME
")->fetchAll();

$setsScry = $pdo->query("
    SELECT code, name
    FROM setsscryfall
    ORDER BY name
")->fetchAll();

/* =========================
   RELACIONAMENTO
========================= */
$relacionamento = null;

if ($edicaoLiga && $edicaoScry) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM RelacionamentosEdicoes
        WHERE ligamagic_sigla = ?
          AND scryfall_sigla  = ?
        LIMIT 1
    ");
    $stmt->execute([$edicaoLiga, $edicaoScry]);
    $relacionamento = $stmt->fetch();
}

/* =========================
   RESULTADOS
========================= */
$resultLiga = [];
$resultScry = [];

/* --- BUSCA POR NOME --- */
if ($buscaCarta !== '') {

    $stmt = $pdo->prepare("
        SELECT *
        FROM cardsligamagic
        WHERE NomeCartaIngles LIKE ?
        ORDER BY SiglaEdicao, NumCarta
    ");
    $stmt->execute(["%$buscaCarta%"]);
    $resultLiga = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT c.*, s.code, s.name AS set_name
        FROM cardsscryfall c
        JOIN setsscryfall s ON s.scryfall_set_id = c.scryfall_set_id
        WHERE c.name LIKE ?
        ORDER BY
            CASE
                WHEN c.collector_number REGEXP '^[0-9]+$' THEN 0
                ELSE 1
            END,
            CAST(
                CASE
                    WHEN c.collector_number REGEXP '^[0-9]+$'
                    THEN c.collector_number
                    ELSE NULL
                END AS UNSIGNED
            ),
            c.collector_number
    ");
    $stmt->execute(["%$buscaCarta%"]);
    $resultScry = $stmt->fetchAll();
}

/* --- BUSCA POR EDIÇÃO --- */
if ($edicaoLiga !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM cardsligamagic
        WHERE SiglaEdicao = ?
        ORDER BY NumCarta
    ");
    $stmt->execute([$edicaoLiga]);
    $resultLiga = $stmt->fetchAll();
}

if ($edicaoScry !== '') {
    $stmt = $pdo->prepare("
        SELECT c.*, s.code, s.name AS set_name
        FROM cardsscryfall c
        JOIN setsscryfall s ON s.scryfall_set_id = c.scryfall_set_id
        WHERE s.code = ?
        ORDER BY
            CASE
                WHEN c.collector_number REGEXP '^[0-9]+$' THEN 0
                ELSE 1
            END,
            CAST(
                CASE
                    WHEN c.collector_number REGEXP '^[0-9]+$'
                    THEN c.collector_number
                    ELSE NULL
                END AS UNSIGNED
            ),
            c.collector_number
    ");
    $stmt->execute([$edicaoScry]);
    $resultScry = $stmt->fetchAll();
}

/* =========================
   MAPA DE NUMERAÇÃO (SÓ SE HOUVER RELACIONAMENTO)
========================= */
$numsMatch = [];

if ($relacionamento) {

    $numsLiga = [];
    foreach ($resultLiga as $c) {
        if (is_numeric($c['NumCarta'])) {
            $numsLiga[(string)(int)$c['NumCarta']] = true;
        }
    }

    $numsScry = [];
    foreach ($resultScry as $c) {
        if (preg_match('/^[0-9]+$/', $c['collector_number'])) {
            $numsScry[(string)(int)$c['collector_number']] = true;
        }
    }

    $numsMatch = array_intersect(array_keys($numsLiga), array_keys($numsScry));
    $numsMatch = array_flip($numsMatch);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Comparar Cartas</title>

<style>
body { font-family: Arial; padding: 20px; }

form {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 10px;
    margin-bottom: 20px;
}

select, input, button {
    padding: 6px;
}

.tables {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th, td {
    border: 1px solid #ccc;
    padding: 6px;
}

th { background: #eee; }

tr:hover td {
    background: #f5f9ff;
}

.match-row td {
    background-color: #e6f7e6 !important;
}

.match-row:hover td {
    background-color: #d4f0d4 !important;
}
</style>
</head>

<body>

<h2>Comparação Ligamagic × Scryfall</h2>

<form method="get">
    <select name="ed_liga">
        <option value="">Edição Ligamagic</option>
        <?php foreach ($setsLiga as $s): ?>
        <option value="<?= $s['SIGLA'] ?>" <?= $edicaoLiga==$s['SIGLA']?'selected':'' ?>>
            <?= htmlspecialchars($s['NOME']) ?> (<?= $s['SIGLA'] ?>)
        </option>
        <?php endforeach; ?>
    </select>

    <select name="ed_scry">
        <option value="">Edição Scryfall</option>
        <?php foreach ($setsScry as $s): ?>
        <option value="<?= $s['code'] ?>" <?= $edicaoScry==$s['code']?'selected':'' ?>>
            <?= htmlspecialchars($s['name']) ?> (<?= $s['code'] ?>)
        </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="carta"
           placeholder="Nome da carta (inglês)"
           value="<?= htmlspecialchars($buscaCarta) ?>">

    <button type="submit">Buscar</button>
</form>

<?php if ($edicaoLiga && $edicaoScry && !$relacionamento): ?>
<div style="background:#fff3cd;padding:10px;margin-bottom:10px">
⚠️ As edições selecionadas não possuem relacionamento cadastrado.
A comparação por número foi desativada.
</div>
<?php endif; ?>

<div class="tables">

<!-- LIGAMAGIC -->
<div>
<h3>Ligamagic (<?= count($resultLiga) ?>)</h3>
<table>
<tr>
<th>Edição</th>
<th>#</th>
<th>Nome (PT)</th>
<th>Nome (EN)</th>
</tr>
<?php foreach ($resultLiga as $c): ?>
<tr class="<?= isset($numsMatch[(string)(int)$c['NumCarta']]) ? 'match-row' : '' ?>">
<td><?= $c['SiglaEdicao'] ?></td>
<td><?= $c['NumCarta'] ?></td>
<td><?= htmlspecialchars($c['NomeCartaPTBR']) ?></td>
<td><?= htmlspecialchars($c['NomeCartaIngles']) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<!-- SCRYFALL -->
<div>
<h3>Scryfall (<?= count($resultScry) ?>)</h3>
<table>
<tr>
<th>Edição</th> <!-- NOVA COLUNA -->
<th>Set</th>
<th>#</th>
<th>Nome</th>
<th>Lang</th>
<th>Foil</th>
</tr>
<?php foreach ($resultScry as $c): ?>
<tr class="<?=
    preg_match('/^[0-9]+$/', $c['collector_number']) &&
    isset($numsMatch[(string)(int)$c['collector_number']])
        ? 'match-row'
        : ''
?>">
<td><?= htmlspecialchars($c['set_name']) ?></td>
<td><?= $c['code'] ?></td>
<td><?= $c['collector_number'] ?></td>
<td><?= htmlspecialchars($c['name']) ?></td>
<td><?= $c['lang'] ?></td>
<td><?= $c['foil'] ? '✔' : '—' ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

</div>

</body>
</html>