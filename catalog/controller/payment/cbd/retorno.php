<?php
  require_once('pagamento.php');
  $pag = new Retorno;
  $pag->campainha();
  $pag->probe();
?>
