<?php
/**
 * ModelPaymentCobredireto
 *
 * Recupera os dados do produto adicionado no carrino de compras
 * @package cobredireto_opencart
 * @author ldmotta - ldmotta@gmail.com
 * @link motanet.com.br
 */
class ModelPaymentCobredireto extends Model
{
    /**
     * getMethod
     *
     * @access public
     * @return array Array contendo dados de configuração do módulo
     */
    public function getMethod()
    {
		$this->load->language('payment/cobredireto');
        $method_data = array( 
                'id'         => 'cobredireto',
                'title'      => $this->language->get('text_title'),
                'sort_order' => $this->config->get('cobredireto_sort_order')
                );
    	return $method_data;
    }

    /**
     * getCart
     *
     * Recupera os dados do carrinho contidos na session e seleciona
     * os dados dos produtos adicionados a este carrinho.
     * @access public
     * @return array Array com os dados dos produtos
     */
    public function getCart()
    {
		$this->load->model('catalog/product');
		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		foreach ($this->cart->getProducts() as $product) {
      		$option_data = array();

      		foreach ($product['option'] as $option) {
        		$option_data[] = array(
          			'name'   => $option['name'],
          			'value'  => $option['value'],
                    'prefix' => $option['prefix']
        		);
      		}
 
      		$product_data[] = array(
            'product_id' => $product['product_id'],
            'name'       => $product['name'],
            'model'      => $product['model'],
            'option'     => $option_data,
            'download'   => $product['download'],
            'quantity'   => $product['quantity'], 
            'price'      => $product['price'],
            'total'      => $product['total'],
            'tax'        => $this->tax->getRate($product['tax_class_id'])
      		); 
    	}

        return array($order, $product_data);
    }
}
