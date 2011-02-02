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
	protected function index() {
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

    public function cd_start_system ()
    {
        $caminho=array_slice(preg_split("/\//",$_SERVER['SCRIPT_FILENAME'],5),0,4);
        define('DIR_BASE', implode("\\",$caminho)."/");

         // Configuration
        require_once(DIR_BASE . 'config.php');

        // Startup
        require_once(DIR_SYSTEM . 'startup.php');

        // Load the application classes
        require_once(DIR_SYSTEM . 'library/customer.php');
        require_once(DIR_SYSTEM . 'library/currency.php');
        require_once(DIR_SYSTEM . 'library/tax.php');
        require_once(DIR_SYSTEM . 'library/weight.php');
        require_once(DIR_SYSTEM . 'library/length.php');
        require_once(DIR_SYSTEM . 'library/cart.php');

        // Registry
        $registry = new Registry();

        // Loader
        $loader = new Loader($registry);
        $registry->set('load', $loader);

        // Config
        $config = new Config();
        $registry->set('config', $config);

        // Database 
        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        $registry->set('db', $db);

        $this->cd_set_config();
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
	public function confirm() {
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

        // Adicionando dois produto
        $order->adicionar($produtos);

        //$this->model_checkout_order->confirm($this->session->data['order_id'], 2, $comment);

        $order->pagar();
	}

    public function retorno() {
        $this->cd_start_system();
        require_once ('cbd/pagamento.php');
        $order = new Retorno();
        $order->campainha();
        $this->probe();
    }

    public function recibo() {
        //$this->load->model('checkout/order');
        //$this->model_checkout_order->confirm($this->session->data['order_id'], 2);
        header("Location: ".HTTPS_SERVER.'index.php?route=checkout/success');
    }

    /**
      * Método para montar o Probe para o CobreDireto
      *
      * Monta a estrtura XML para o Probe e solicita o WebService
      * caso tenha a função capturar, já solicita a mesma
      *
      **/
    public function probe(){
      require_once ('cbd/pagamento.php');
      $order = new Retorno();
      $soapProbe =  $order->request->createElement('probe');
      
      $merch  = $order->request->createElement('merch_ref',$order->merch_ref);
      $id     = $order->request->createElement('id', $order->id);
      
      $soapProbe->appendChild($merch);
      $soapProbe->appendChild($id);
      
      $order->request->appendChild($soapProbe);
      parent::initCobreDireto('probe');
      if (function_exists('capturar')){
          $order->capturar(
          $order->merch_ref,(string) $order->xml->order_data->order->bpag_data->status, array(
          'url' => (string) $order->xml->order_data->order->bpag_data->url, 
          'cobredireto_id' => (string) $order->xml->order_data->order->bpag_data->id,
         ));
      }
    }

    function capturar($codpedido, $status){
        echo '<pre>'; print_r ($cobj->getMethods()); echo '</pre>'; die();
        $this->load->model('checkout/order');
        global $db;

        switch ($status) {
            case 0: // Pago – transação OK
                //$db->query('update '.DB_PREFIX.'order set order_status_id=5 where order_id='.$codpedido);
                $this->model_checkout_order->confirm($codigopedido, 5);
                break;
            case 1: // Não pago – transação cancelada ou inválida
                //$db->query('update '.DB_PREFIX.'order set order_status_id=7 where order_id='.$codpedido);
                $this->model_checkout_order->confirm($codigopedido, 7);
                break;
            case 2: // Pendente – transação em análise ou não capturada
                //$db->query('update '.DB_PREFIX.'order set order_status_id=1 where order_id='.$codpedido);
                $this->model_checkout_order->confirm($codigopedido, 1);
                break;
        }
    }

}

