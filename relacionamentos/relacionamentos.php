<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';

/* =========================
   CACHE DE ÍCONES SCRYFALL
========================= */
function getSetIcon($sigla) {
    $sigla = strtolower(trim($sigla));
    $dir = __DIR__ . "/cache/sets";
    $file = "$dir/$sigla.svg";

    // garante diretório
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // se já existe → retorna IMEDIATO
    if (file_exists($file)) {
        return "cache/sets/$sigla.svg";
    }

    // NÃO BAIXA aqui — evita travar página
    return "cache/sets/_missing.svg";
}


/* =========================
   DADOS
========================= */
$ligamagic = $pdo->query("
    SELECT id, NOME, SIGLA
    FROM setsligamagic
    ORDER BY NOME
")->fetchAll();

$scryfall = $pdo->query("
    SELECT
        e.Sigla,
        e.NomeSet,
        e.TipoEdicao,
        e.NumeroCardInicial
    FROM EdicoesScryfall e
    LEFT JOIN RelacionamentosEdicoes r
        ON  r.scryfall_sigla  = e.Sigla
        AND r.scryfall_tipo   = e.TipoEdicao
        AND r.scryfall_inicio = e.NumeroCardInicial
    WHERE r.scryfall_sigla IS NULL
    ORDER BY e.NomeSet, e.TipoEdicao
")->fetchAll();


$relacionamentos = $pdo->query("
    SELECT
        r.ligamagic_id,
        r.ligamagic_sigla,
        r.scryfall_sigla,
        r.scryfall_tipo,
        r.scryfall_inicio,
        e.NOME AS ligamagic_nome
    FROM RelacionamentosEdicoes r
    JOIN setsligamagic e ON e.id = r.ligamagic_id
    ORDER BY e.NOME, r.scryfall_sigla, r.scryfall_tipo
")->fetchAll();

/* =========================
   SALVAR
========================= */
if (isset($_POST['salvar'])) {
    $stmt = $pdo->prepare("
        INSERT INTO RelacionamentosEdicoes
        (ligamagic_id, ligamagic_sigla, scryfall_sigla, scryfall_tipo, scryfall_inicio, criado_em)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_POST['ligamagic_id'],
        $_POST['ligamagic_sigla'],
        $_POST['scryfall_sigla'],
        $_POST['scryfall_tipo'],
        $_POST['scryfall_inicio']
    ]);
    header("Location: relacionamentos.php");
    exit;
}

/* =========================
   EXCLUIR
========================= */
if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("
        DELETE FROM RelacionamentosEdicoes
        WHERE ligamagic_id = ?
          AND scryfall_sigla = ?
          AND scryfall_tipo = ?
    ");
    $stmt->execute([
        $_GET['ligamagic_id'],
        $_GET['sigla'],
        $_GET['tipo']
    ]);
    header("Location: relacionamentos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Relacionar Edições</title>

<style>
body { font-family: Arial; padding: 20px; }

.layout {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: 30px;
}

select, input, button {
    width: 100%;
    padding: 6px;
    margin-bottom: 8px;
}

.actions {
    display: flex;
    gap: 6px;
    margin-bottom: 8px;
}

.table-wrapper {
    max-height: 70vh;
    overflow-y: auto;
    border: 1px solid #ccc;
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

.col-liga, .col-scry {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    text-decoration: none;
    color: #fff;
    display: inline-block;
}

.btn-liga { background: #ff9800; }
.btn-scry { background: #2196f3; }
.btn-del  { background: #e53935; }

/* SMART SELECT SCRYFALL */
.custom-select {
    position: relative;
    border: 1px solid #ccc;
    padding: 6px;
    background: #fff;
    cursor: pointer;
}

.custom-select .selected {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.custom-select .options {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ccc;
    max-height: 300px;
    overflow-y: auto;
    z-index: 999;
}

.custom-select.open .options {
    display: block;
}

.custom-select .search {
    width: 100%;
    padding: 6px;
    border: none;
    border-bottom: 1px solid #ccc;
    outline: none;
}

.custom-select .option {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px;
    font-size: 12px;
}

.custom-select .option:hover {
    background: #f5f5f5;
}

.custom-select img {
    height: 16px;
    flex-shrink: 0;
}

/* ===== TABELA - LIMITES E HOVER ===== */
.table-wrapper table {
    table-layout: fixed;
}

.col-liga,
.col-scry {
    max-width: 50ch;          /* limite visual ~50 caracteres */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Hover da linha inteira */
.table-wrapper table tr:hover td {
    background-color: #f2f7ff;
    transition: background-color 0.15s ease-in-out;
}

</style>
</head>

<body>

<h2>Relacionamento Ligamagic × Scryfall</h2>

<div class="layout">

<!-- FORMULÁRIO -->
<div>
<form method="post">

<label>Filtrar Ligamagic</label>
<input type="text" id="filtroLigamagic" placeholder="Digite nome, sigla ou ID…">

<label>Ligamagic</label>
<select name="ligamagic_id" id="ligamagic" required>
<option value="">Selecione…</option>

<?php $contar = 1 ?>
<?php foreach ($ligamagic as $l): ?>
<option value="<?= $l['id'] ?>" data-sigla="<?= strtoupper($l['SIGLA']) ?>">
<?php echo $contar.") "; $contar++; ?><?= htmlspecialchars($l['NOME']) ?>  | <?= $l['SIGLA'] ?> | <?= $l['id'] ?>
</option>
<?php endforeach; ?>
</select>

<input type="hidden" name="ligamagic_sigla" id="ligamagic_sigla">

<div class="actions">
<a id="linkLiga" target="_blank" class="btn btn-liga">Ligamagic</a>
</div>

<label>Scryfall</label>

<div id="scryfallCustom" class="custom-select">
    <div class="selected">Selecione…</div>
    <div class="options">
        <input type="text" class="search" placeholder="Buscar edição…">
        <div id="scryfallOptions"></div>
    </div>
</div>

<select id="scryfall" style="display:none">
<option value="">Selecione…</option>
<?php $contar = 1; ?>
<?php foreach ($scryfall as $s): ?>
<option
 data-sigla="<?= $s['Sigla'] ?>"
 data-tipo="<?= htmlspecialchars($s['TipoEdicao']) ?>"
 data-inicio="<?= $s['NumeroCardInicial'] ?>"
>
<?php echo $contar.") "; $contar++;?><?= htmlspecialchars($s['NomeSet']) ?> – <?= htmlspecialchars($s['TipoEdicao']) ?>
 | <?= $s['Sigla'] ?> | <?= $s['NumeroCardInicial'] ?>
</option>
<?php endforeach; ?>
</select>

<input type="hidden" name="scryfall_sigla" id="scryfall_sigla">
<input type="hidden" name="scryfall_tipo" id="scryfall_tipo">
<input type="hidden" name="scryfall_inicio" id="scryfall_inicio">

<div class="actions">
<a id="linkScry" target="_blank" class="btn btn-scry">Scryfall</a>
</div>

<button type="submit" name="salvar">Salvar Relacionamento</button>

</form>
</div>

<!-- TABELA -->
<div>
<h3>Relacionamentos Salvos (<?= count($relacionamentos) ?>)</h3>

<div class="table-wrapper">
<table>
<tr>
<th>Ligamagic</th>
<th>Scryfall</th>
<th>Ações</th>
</tr>

<?php foreach ($relacionamentos as $r): ?>
<tr>
<td class="col-liga" title="<?= htmlspecialchars($r['ligamagic_nome']) ?>">
<?= htmlspecialchars($r['ligamagic_nome']) ?> (<?= $r['ligamagic_sigla'] ?>)
</td>

<td class="col-scry"
title="<?= $r['scryfall_sigla'] ?> – <?= htmlspecialchars($r['scryfall_tipo']) ?>"><?= $r['scryfall_inicio']?> -
<img src="<?= getSetIcon($r['scryfall_sigla']) ?>"
     style="height:16px;margin-right:6px;vertical-align:middle"
     onerror="this.style.display='none'">
<?= $r['scryfall_sigla'] ?> – <?= htmlspecialchars($r['scryfall_tipo']) ?>
</td>

<td>
<a class="btn btn-liga" target="_blank"
 href="https://www.ligamagic.com.br/?view=cards/search&card=edid=<?= $r['ligamagic_id'] ?>&ed=<?= $r['ligamagic_sigla'] ?>">Liga</a>

<a class="btn btn-scry" target="_blank"
 href="https://scryfall.com/sets/<?= $r['scryfall_sigla'] ?>">Scry</a>

<a class="btn btn-del"
 href="?excluir=1&ligamagic_id=<?= $r['ligamagic_id'] ?>&sigla=<?= $r['scryfall_sigla'] ?>&tipo=<?= urlencode($r['scryfall_tipo']) ?>">X</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>

</div>

<script>
const ligamagic = document.getElementById('ligamagic');
const filtroLiga = document.getElementById('filtroLigamagic');
const linkLiga = document.getElementById('linkLiga');
const linkScry = document.getElementById('linkScry');

/* FILTRO LIGAMAGIC */
filtroLiga.addEventListener('input', () => {
    const termo = filtroLiga.value.toLowerCase();
    for (let opt of ligamagic.options) {
        if (!opt.value) continue;
        opt.style.display =
            opt.textContent.toLowerCase().includes(termo) ? '' : 'none';
    }
});

/* LIGAMAGIC CHANGE */
ligamagic.onchange = () => {
    const opt = ligamagic.selectedOptions[0];
    if (!opt) return;

    ligamagic_sigla.value = opt.dataset.sigla;
    linkLiga.href =
        "https://www.ligamagic.com.br/?view=cards/search&card=edid=" +
        opt.value + "&ed=" + opt.dataset.sigla;
};

/* SMART SELECT SCRYFALL */
const SET_ICON_BASE = "cache/sets/";
const custom = document.getElementById('scryfallCustom');
const selected = custom.querySelector('.selected');
const search = custom.querySelector('.search');
const optionsBox = document.getElementById('scryfallOptions');
const scryfall = document.getElementById('scryfall');

for (let opt of scryfall.options) {
    if (!opt.dataset.sigla) continue;

    const div = document.createElement('div');
    div.className = 'option';

    const img = document.createElement('img');
    img.src = SET_ICON_BASE + opt.dataset.sigla.toLowerCase() + ".svg";
    img.onerror = () => img.style.display = 'none';

    div.appendChild(img);
    div.appendChild(document.createTextNode(opt.textContent));

    div.onclick = () => {
        opt.selected = true;
        selected.innerHTML = div.innerHTML;
        custom.classList.remove('open');

        scryfall_sigla.value = opt.dataset.sigla;
        scryfall_tipo.value = opt.dataset.tipo;
        scryfall_inicio.value = opt.dataset.inicio;

        linkScry.href = "https://scryfall.com/sets/" + opt.dataset.sigla;
    };

    optionsBox.appendChild(div);
}

custom.onclick = e => {
    if (!e.target.classList.contains('search')) {
        custom.classList.toggle('open');
        search.focus();
    }
};

search.oninput = () => {
    const t = search.value.toLowerCase();
    optionsBox.querySelectorAll('.option').forEach(o => {
        o.style.display = o.textContent.toLowerCase().includes(t) ? '' : 'none';
    });
};

document.addEventListener('click', e => {
    if (!custom.contains(e.target)) custom.classList.remove('open');
});
</script>

</body>
</html>