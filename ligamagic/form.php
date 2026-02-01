<?php
require '../db.php';

$edicao = ['ANO'=>'', 'NOME'=>'', 'SIGLA'=>''];

if(isset($_GET['id'])){
    $stmt = $pdo->prepare("SELECT * FROM setsligamagic WHERE ID=?");
    $stmt->execute([$_GET['id']]);
    $edicao = $stmt->fetch();
}
?>
<form method="post" action="salvar.php">
<input type="hidden" name="id" value="<?= $_GET['id'] ?? '' ?>">

Ano: <input name="ano" value="<?= $edicao['ANO'] ?>"><br>
Nome: <input name="nome" value="<?= $edicao['NOME'] ?>"><br>
Sigla: <input name="sigla" value="<?= $edicao['SIGLA'] ?>"><br>

<button>Salvar</button>
</form>