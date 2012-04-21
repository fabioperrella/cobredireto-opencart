<?php
/**
 * Template de administração do módulo
 *
 * Exibe o formulário para edição do módulo
 * @package cobredireto_opencart
 * @author ldmotta - ldmotta@gmail.com
 * @link motanet.com.br
 */
?>
<?php echo $header; ?>

<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<div class="box">
    <div class="left"></div>
    <div class="right"></div>
    <div class="heading">
        <h1 style="background-image: url('view/image/payment.png');"><?php echo $heading_title; ?></h1>
        <div class="buttons">
            <a onclick="$('#form').submit();" class="button">
                <span><?php echo $button_save; ?></span></a>
            <a onclick="location = '<?php echo $cancel; ?>';" class="button">
                <span><?php echo $button_cancel; ?></span></a>
        </div>
    </div>

    <div class="content">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="form">
                <tr>
                    <td><?php echo $lb_ambiente; ?></td>
                    <td>
                        <select name="cobredireto_ambiente">
                            <option value="teste" selected="selected"><?php echo $text_teste; ?></option>
                            <option value="producao"><?php echo $text_producao; ?></option>
                        </select>
                    </td>
                </tr>
				<tr>
                    <td width="25%">
                        <span class="required">*</span>
                        <?php echo $lb_codloja; ?>
                    </td>
                    <td>
                        <input type="text" name="cobredireto_codloja" value="<?php echo $cobredireto_codloja; ?>" />
                        <br />
                        <?php if ($error_cobredireto_codloja): ?>
                        <span class="error"><?php echo $error_cobredireto_codloja; ?></span>
                        <?php endif ?>
                    </td>
                </tr>
                <tr>
                    <td width="25%">
                        <?php echo $lb_usuario; ?>
                    </td>
                    <td>
                        <input type="text" name="cobredireto_usuario" value="<?php echo $cobredireto_usuario; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="25%">
                        <?php echo $lb_senha; ?>
                    </td>
                    <td>
                        <input type="text" name="cobredireto_senha" value="<?php echo $cobredireto_senha; ?>" />
                    </td>
                </tr>
                <tr>
                    <td><?php echo $lb_status; ?></td>
                    <td>
                        <select name="cobredireto_status">
                            <?php if ($cobredireto_status) { ?>
                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                            <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                            <option value="1"><?php echo $text_enabled; ?></option>
                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                        </select>
                    </td>
                </tr>

            </table>
        </form>
        <div>
            <h2><?php echo $instructions_title; ?></h2>

            <?php echo ControllerPaymentCobredireto::formatText($instructions_info); ?>

        </div>
    </div><!-- content -->

    <?php echo $footer; ?>
