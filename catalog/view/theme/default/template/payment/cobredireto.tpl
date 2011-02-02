<?php
/**
 * Template de redirecionamento para o getway de pagamento
 *
 * Não visto pelo usuário, ao entrar nesta página, o formulário é submetido
 * automaticamente.
 * @package cobredireto_opencart
 * @author ldmotta - ldmotta@gmail.com
 * @link motanet.com.br
 */
?>
<div class="buttons">
  <table>
    <tr>
      <td align="left"><a onclick="location='<?php echo $back; ?>'" class="button"><span><?php echo $button_back; ?></span></a></td>
      <td align="right" id="checkout"><a href="<?php echo $confirm ?>" id='finalizar_compra' class="button"><span><?php echo $button_confirm; ?></span></a></td>
    </tr>
  </table>
</div>

<script type="text/javascript"><!--
/*
$('#finalizar_compra').click(function() {
  alert(1)
  $.ajax({ 
    type: 'GET',
    url: 'index.php?route=payment/cobredireto/confirm',
    success: function(t) {
      location = '<?php echo $continue; ?>'
    }   
  });
})
*/
//--></script>
