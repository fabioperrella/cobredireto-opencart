<?php
/**
 * ControllerPaymentCobredireto
 *
 * Classe controle da administração do módulo de pagamento
 * @package cobredireto_opencart
 * @author ldmotta - ldmotta@gmail.com
 * @link motanet.com.br
 */
class ControllerPaymentCobredireto extends Controller {
    private $error;
    /**
     * index
     * Executado na página de edição do módulo na administração, implementa
     * os botões de salvar e cancelar
     */
    function index() {

        $this->load->language('payment/cobredireto');

        $this->document->title = $this->language->get('heading_title');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('cobredireto', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
        }

        $this->document->breadcrumbs = array(
                array(
                        'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
                        'text'      => $this->language->get('text_home'),
                        'separator' => FALSE
                ),
                array(
                        'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
                        'text'      => $this->language->get('text_payment'),
                        'separator' => ' :: '
                ),
                array(
                        'href'      => HTTPS_SERVER . 'index.php?route=payment/cobredireto&token=' . $this->session->data['token'],
                        'text'      => $this->language->get('heading_title'),
                        'separator' => ' :: '
                )
        );
        $langs = array(
                'heading_title', 'text_payment', 'text_success',
                'text_enabled', 'text_disabled', 'button_cancel',
                'text_teste', 'text_producao', 'text_pac', 'text_sedex',
                'button_save', 'lb_codloja', 'lb_usuario', 'lb_senha',
                'lb_ambiente', 'instructions_title', 'instructions_info','lb_status'
        );
        
        foreach ($langs as $item) {
            $this->data[$item] = $this->language->get($item);
        }

        $campos_submetidos=array(
            'codloja', 'usuario', 'senha', 
            'ambiente', 'status'
        );
        foreach ($campos_submetidos as $item) {
            if (isset($this->request->post['cobredireto_'.$item])) {
                $this->data["cobredireto_$item"] = $this->request->post["cobredireto_$item"];
            } else {
                $this->data["cobredireto_$item"] = $this->config->get("cobredireto_$item");
            }
        }

        $this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/cobredireto&token=' . $this->session->data['token'];

        $this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

        $this->data['error_warning'] = @$this->error['warning'];
        $this->data['error_cobredireto_codloja'] = @$this->error['cobredireto_codloja'];

        $this->id       = 'content';
        $this->template = 'payment/cobredireto.tpl';
        $this->children = array(
                'common/header',
                'common/footer'
        );

        $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    }

    /**
     * validate - Valida os dados submetidos na página de edição do módulo
     * @access public
     * @return boolean True ou False dependendo do retorno da validação
     */
    public function validate() {
        if (!$this->user->hasPermission('modify', 'payment/cobredireto')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * formatText - Utilizado para formatar o texto da documentação do módulo
     * @access static
     * @param string Texto a ser formatado
     * @return string Retorna o texto formatado com html
     */
    static function formatText($text) {
        $text = trim($text);
        // Trocando os titulos
        $text = preg_replace('/== (.+) ==/', '<h3>\1</h3>', $text);
        // Trocando os paragrados
        $text = preg_replace('@[\n\r]{3,}@', "</p>\n\n<p>", $text);
        // Trocando os negritos
        $text = preg_replace("@\*([^\*]+)\*@", '<strong>\1</strong>', $text);
        // Troca as imagens
        $text = preg_replace('@\[img:([^\]]+)\]@', '<img src="view/image/payment/\1" />', $text);
        // Trocando as urls
        $text = preg_replace('@\[([^ ]+) ([^\]]+)\]@', '<a href="\1" title="\2" target="_blank">\2</a>', $text);
        // Aplicando o primeiro e ultimo paragrafos
        $text = "\n\n<p>$text</p>\n\n";
        // Removendo as duplicatas
        $text = preg_replace('@<p>(<h\d>.+</h\d>)</p>@', '\1', $text);
        return $text;
    }
}
