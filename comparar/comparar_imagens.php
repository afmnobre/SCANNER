<?php
require_once __DIR__ . '/../db.php';

$edLiga  = $_GET['liga'] ?? '';
$edScry = $_GET['scry'] ?? '';
$imgSize = $_GET['img'] ?? 'normal';

$allowedSizes = ['small','normal','large','png'];
if (!in_array($imgSize, $allowedSizes, true)) {
    $imgSize = 'normal';
}

/* =========================
   FUNÇÃO IMAGEM SCRYFALL
========================= */
function scryfall_image_url(string $id, string $size): string {
    $ext = ($size === 'png') ? 'png' : 'jpg';
    return "https://cards.scryfall.io/{$size}/front/"
         . substr($id, 0, 1) . "/"
         . substr($id, 1, 1) . "/"
         . $id . ".{$ext}";
}

/* =========================
   GRID DINÂMICO
========================= */
$gridMin = match ($imgSize) {
    'small'  => 130,
    'normal' => 180,
    'large'  => 250,
    'png'    => 320,
    default  => 180
};

/* =========================
   EDIÇÕES
========================= */
$setsLiga = $pdo->query("
    SELECT SIGLA, NOME FROM setsligamagic ORDER BY NOME
")->fetchAll();

$setsScry = $pdo->query("
    SELECT code, name FROM setsscryfall ORDER BY name
")->fetchAll();

/* =========================
   CARTAS
========================= */
$ligaCards = [];
$scryCards = [];

if ($edLiga && $edScry) {

    /* Ligamagic */
    $stmt = $pdo->prepare("
        SELECT NumCarta, NomeCartaIngles
        FROM cardsligamagic
        WHERE SiglaEdicao = ?
    ");
    $stmt->execute([$edLiga]);

    foreach ($stmt->fetchAll() as $c) {
        $ligaCards[$c['NumCarta']] = $c;
    }

    /* Scryfall */
    $stmt = $pdo->prepare("
        SELECT
            c.collector_number,
            c.name,
            c.scryfall_card_id
        FROM cardsscryfall c
        JOIN setsscryfall s ON s.scryfall_set_id = c.scryfall_set_id
        WHERE s.code = ?
    ");
    $stmt->execute([$edScry]);

    foreach ($stmt->fetchAll() as $c) {
        $scryCards[$c['collector_number']] = $c;
    }
}

/* =========================
   ORDENAÇÃO SEGURA
========================= */
function sortCards(array $cards): array {
    $numeric = [];
    $alpha   = [];

    foreach ($cards as $k => $v) {
        if (ctype_digit((string)$k)) {
            $numeric[(int)$k] = $v;
        } else {
            $alpha[$k] = $v;
        }
    }

    ksort($numeric, SORT_NUMERIC);
    ksort($alpha, SORT_NATURAL | SORT_FLAG_CASE);

    return $numeric + $alpha;
}

/* =========================
   COMPARAÇÃO
========================= */
$onlyLiga = [];
$both = [];
$onlyScry = [];

if ($edLiga && $edScry) {
    foreach ($ligaCards as $num => $c) {
        isset($scryCards[$num])
            ? $both[$num] = $scryCards[$num] + ['liganame'=>$c['NomeCartaIngles']]
            : $onlyLiga[$num] = $c;
    }

    foreach ($scryCards as $num => $c) {
        if (!isset($ligaCards[$num])) {
            $onlyScry[$num] = $c;
        }
    }

    $onlyLiga = sortCards($onlyLiga);
    $both     = sortCards($both);
    $onlyScry = sortCards($onlyScry);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Comparação Visual</title>

<style>
body { font-family: Arial, sans-serif; padding:20px; }
.top-bar { display:flex; justify-content:space-between; gap:15px; margin-bottom:25px; }

form.main { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; }

.control-panel {
    background:#2e2e2e; color:#fff;
    padding:10px 14px; border-radius:8px;
    font-size:13px; min-width:140px;
}

.control-panel label { display:block; cursor:pointer; margin:4px 0; }

.section { margin-bottom:50px; }
.grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(<?= $gridMin ?>px,1fr));
    gap:14px;
}

.card {
    border:2px solid #ccc;
    padding:6px;
    background:#fff;
    text-align:center;
    font-size:12px;
}

.card img {
    max-width:100%;
    height:auto;
    border-radius:6px;
}

.only-liga { border-color:#ff9800; }
.both { border-color:#4caf50; }
.only-scry { border-color:#e53935; }

.label { font-weight:bold; margin-top:6px; }
</style>
</head>

<body>

<h2>Comparação Visual de Cartas</h2>

<div class="top-bar">

<form method="get" class="main">
<select name="liga" required>
<option value="">Ligamagic</option>
<?php foreach ($setsLiga as $s): ?>
<option value="<?= $s['SIGLA'] ?>" <?= $edLiga==$s['SIGLA']?'selected':'' ?>>
<?= htmlspecialchars($s['NOME']) ?>
</option>
<?php endforeach; ?>
</select>

<select name="scry" required>
<option value="">Scryfall</option>
<?php foreach ($setsScry as $s): ?>
<option value="<?= $s['code'] ?>" <?= $edScry==$s['code']?'selected':'' ?>>
<?= htmlspecialchars($s['name']) ?>
</option>
<?php endforeach; ?>
</select>

<button type="submit">Comparar</button>
</form>

<form method="get" class="control-panel">
<input type="hidden" name="liga" value="<?= htmlspecialchars($edLiga) ?>">
<input type="hidden" name="scry" value="<?= htmlspecialchars($edScry) ?>">

<strong>Tamanho</strong>
<?php foreach ($allowedSizes as $s): ?>
<label>
<input type="radio" name="img" value="<?= $s ?>"
<?= $imgSize==$s?'checked':'' ?>
onchange="this.form.submit()">
<?= strtoupper($s) ?>
</label>
<?php endforeach; ?>
</form>

</div>

<?php if ($edLiga && $edScry): ?>

<div class="section">
<h3>🟧 Apenas Ligamagic (<?= count($onlyLiga) ?>)</h3>
<div class="grid">
<?php foreach ($onlyLiga as $num => $c): ?>
<div class="card only-liga">
<div class="label">#<?= htmlspecialchars($num) ?></div>
<div><?= htmlspecialchars($c['NomeCartaIngles'] ?? '—') ?></div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="section">
<h3>🟩 Em comum (<?= count($both) ?>)</h3>
<div class="grid">
<?php foreach ($both as $num => $c): ?>
<div class="card both">
<img src="<?= scryfall_image_url($c['scryfall_card_id'], $imgSize) ?>" loading="lazy">
<div class="label">#<?= htmlspecialchars($num) ?></div>
<div><?= htmlspecialchars($c['name']) ?></div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="section">
<h3>🟥 Apenas Scryfall (<?= count($onlyScry) ?>)</h3>
<div class="grid">
<?php foreach ($onlyScry as $num => $c): ?>
<div class="card only-scry">
<img src="<?= scryfall_image_url($c['scryfall_card_id'], $imgSize) ?>" loading="lazy">
<div class="label">#<?= htmlspecialchars($num) ?></div>
<div><?= htmlspecialchars($c['name']) ?></div>
</div>
<?php endforeach; ?>
</div>
</div>

<?php endif; ?>

</body>
</html>