<?php
/**
 * ControllerPaymentCobredireto
 *
 * Classe que controla o comportamento do módulo no lado do cliente
 * Responsável por capturar os dados do carrinho de compras, exibir o formulário
 * na página de checkout e enviar estes dados para o getway de pagamento.
 * @package cobredireto_opencart
 * <code>
 * \@include pgs/pgs.php
 * \@include pgs/tratadados.php
 * </code>
 * @author ldmotta - ldmotta@gmail.com
 * @link motanet.com.br
 */

require_once ('cbd/tratadados.php');

class ControllerPaymentCobredireto extends Controller
{

    /**
     * index - Incluido à ultima tela do processo de compra
     * 
     * @access protected
     * @return void
     */
    protected function index()
    {
        $this->language->load('payment/cobredireto');
        $this->load->model('payment/cobredireto');

        $this->data['button_confirm']   = $this->language->get('button_confirm');
        $this->data['button_back']      = $this->language->get('button_back');

        $this->data['continue']         = HTTPS_SERVER . 'index.php?route=checkout/success&token=' . $this->session->data['token'];
        $this->data['back']             = HTTPS_SERVER . 'index.php?route=checkout/payment&token=' . $this->session->data['token'];
        $this->data['confirm']          = HTTPS_SERVER . 'index.php?route=payment/cobredireto/confirm&token=' . $this->session->data['token'];


        $this->id           = 'payment';
        $this->template     = $this->config->get('config_template') . '/template/payment/cobredireto.tpl';
        $this->render(); 
    }


    function cd_set_config ()
    {
        define('CD_AMBIENTE'    , $this->config->get('cobredireto_ambiente'));
        define('CD_CODLOJA'     , $this->config->get('cobredireto_codloja')); 
        define('CD_USUARIO'     , $this->config->get('cobredireto_usuario'));
        define('CD_SENHA'       , $this->config->get('cobredireto_senha'));
        define('CD_URL_RETORNO' , HTTPS_SERVER . 'index.php?route=payment/cobredireto/retorno');
        define('CD_URL_RECIBO'  , HTTPS_SERVER . 'index.php?route=payment/cobredireto/recibo');
        define('CD_URL_ERRO'    , HTTPS_SERVER . 'index.php?route=payment/cobredireto/erro');
    }

    /**
     * confirm - é executado quando se clica no botão de confirmação de compra
     * 
     * @access public
     * @return void
     */
    public function confirm()
    {
        $this->cd_set_config();
        $this->language->load('payment/cobredireto');
        $this->load->model('payment/cobredireto');

        $this->load->model('checkout/order');

        $comment  = $this->language->get('text_payable') . "\n";
        $comment .= $this->config->get('cobredireto_payable') . "\n\n";
        $comment .= $this->language->get('text_address') . "\n";
        $comment .= $this->config->get('config_address') . "\n\n";
        $comment .= $this->language->get('text_payment') . "\n";

        /* Aplicando a biblioteca Cobredireto */

        require_once ('cbd/pagamento.php');

        list($order_info, $cart) = $this->model_payment_cobredireto->getCart();
        $order = new Pg($order_info['order_id']);

        $frete = intval(( $this->session->data['shipping_method']['cost']*100));
        $order->frete($frete);
        //$frete = number_format($this->currency->format($this->session->data['shipping_method']['cost'], '' , FALSE, FALSE), 2, '', '');

        list($ddd, $telefone) = trataTelefone($order_info['telephone']);
        list($endereco, $numero, $complemento) = trataEndereco("{$order_info['payment_address_1']} {$order_info['payment_address_2']}");
        $data = array (
                'primeiro_nome' => $order_info['shipping_firstname'],
                'meio_nome'     => '',
                'ultimo_nome'   => $order_info['shipping_lastname'],
                'email'         => $order_info['email'],
                'documento'     => '',
                'tel_casa'      => array (
                    'area'          => $ddd,
                    'numero'        => $telefone,
                    ),
                'cep' => $order_info['shipping_postcode'],
                );
        $order->endereco($data,'ENTREGA');

        $data = array (
                'primeiro_nome' => $order_info['payment_firstname'],
                'meio_nome'     => '',
                'ultimo_nome'   => $order_info['payment_lastname'],
                'email'         => $order_info['email'],
                'documento'     => '',
                'tel_casa'      => array (
                    'area'          => $ddd,
                    'numero'        => $telefone,
                    ),
                'cep' => $order_info['payment_postcode'],
                );
        $order->endereco($data,'COBRANCA');
        $order->endereco($data,'CONSUMIDOR');

        $produtos = array();
        foreach ($cart as $item) {
            $produtos[] = array(
                    'id'         => $item['product_id'],
                    'descricao'  => $item['name'],
                    'quantidade' => $item['quantity'],
                    'valor'      => $item['price']
                    );
        }

        $order->adicionar($produtos);

        $order->pagar();
    }

    public function retorno()
    {
        self::cd_set_config();
        require_once ('cbd/pagamento.php');
        $this->order = new Retorno();
        $this->order->campainha();
        self::probe();
    }

    public function recibo()
    {
        header("Location: ".HTTPS_SERVER.'index.php?route=checkout/success');
    }

    function capturar($codpedido, $status)
    {
        $this->load->model('checkout/order');
        switch ($status) {
            case 0: // Pago – transação OK
                $this->model_checkout_order->confirm($codigopedido, 5);
                break;
            case 1: // Não pago – transação cancelada ou inválida
                $this->model_checkout_order->confirm($codigopedido, 7);
                break;
            case 2: // Pendente – transação em análise ou não capturada
                $this->model_checkout_order->confirm($codigopedido, 1);
                break;
        }
    }
    /**
      * Reescrevendo o método de Probe da Biblioteca do CobreDireto
      *
      * A biblioteca não tem como acessar o método 'capturar' que esta
      * nesta mesma classe, então a solução é reescreve-la dentro
      * dessa classe e utilizar os métodos da biblioteca do CobreDireto
      **/
    public function probe()
    {
        $request = new DomDocument('1.0','utf8');

        $soapProbe =  $request->createElement('probe');

        $merch  = $request->createElement('merch_ref',$this->order->merch_ref);
        $id     = $request->createElement('id', $this->order->id);

        $soapProbe->appendChild($merch);
        $soapProbe->appendChild($id);

        $request->appendChild($soapProbe);

        $this->order->request = clone $request;

        $this->order->initCobreDireto('probe');

        self::capturar($merch,(string) $this->order->xml->order_data->order->bpag_data->status, array(
                    'url' => (string) $this->order->xml->order_data->order->bpag_data->url, 
                    'cobredireto_id' => (string) $this->order->xml->order_data->order->bpag_data->id,
                    ));
    }
}
