<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: WooCommerce Eximbay Payment Gateway
Plugin URI: http://www.eximbay.com
Description: Eximbay Payment gateway for woocommerce
Version: 3.0.0
Author: KRPartners Co.,Ltd
Author URI: http://www.eximbay.com
*/

add_action('plugins_loaded', 'woocommerce_eximbay_init', 0);

function woocommerce_eximbay_init(){
  if(!class_exists('WC_Payment_Gateway')) return;
class WC_Gateway_Eximbay extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
	public function __construct() {
		global $woocommerce;

		$plugin_dir = plugin_dir_url(__FILE__);

        $this->id           = 'eximbay';
		$this->icon = apply_filters('woocommerce_eximbay_icon', ''.$plugin_dir.'logo-pay_128.png');
        $this->has_fields   = false;
        //$this->liveurl      = 'https://www.eximbay.com/web/payment2.0/payment_real.do';
		//$this->testurl      = 'https://www.test.eximbay.com/web/payment2.0/payment_real.do';
        $this->method_title = __( 'Eximbay', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title 			= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->merchant			= $this->get_option( 'merchant' );
		$this->mid				= $this->get_option( 'mid' );
		$this->secretKey		= $this->get_option( 'secretKey' );
		$this->ver				= $this->get_option( 'ver' );
		$this->testmode			= $this->get_option( 'testmode' );
		$this->debug			= $this->get_option( 'debug' );
		//$this->status_page_id	= $this->get_option( 'status_page_id' );
		
		// Logs
		if ( 'yes' == $this->debug ) {
			$this->log = new WC_Logger();
		}
		
		// Actions
		add_action( 'valid-eximbay-request', array( $this, 'successful_request' ) );
		add_action( 'woocommerce_receipt_eximbay', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		//Payment listener/API hook
		add_action( 'woocommerce_api_wc_gateway_eximbay', array( $this, 'check_response' ) );

		if ( !$this->is_valid_for_use() ) $this->enabled = false;
    }


    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @access public
     * @return bool
     */
    function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_eximbay_supported_currencies', array( 'AED', 'AUD', 'BRL', 'CAD', 'CNY', 'EUR', 'GBP', 'HKD', 'JPY', 'KRW', 'KZT', 'MNT', 'MOP', 'MYR', 'NOK', 'NZD', 'PHP', 'RUB', 'SAR', 'SGD', 'THB', 'TRY', 'TWD', 'VND', 'USD' ) ) ) ) return false;
         
        return true;
    }

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

		?>
		<h3><?php _e( 'Eximbay Gateway', 'woocommerce' ); ?></h3>
		<p><?php _e( 'Exibmay Gateway works by sending the user to Eximbay to enter their payment information.', 'woocommerce' ); ?></p>

    	<?php if ( $this->is_valid_for_use() ) : ?>

			<table class="form-table">
			<?php
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
			?>
			</table><!--/.form-table-->

		<?php else : ?>
            <div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'Eximbay does not support your store currency.', 'woocommerce' ); ?></p></div>
		<?php
			endif;
	}


    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
							'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
							'type' 			=> 'checkbox',
							'label' 		=> __( 'Enable Eximbay Payment Gateway', 'woocommerce' ),
							'default'		=> 'yes'
						),
			'title' => array(
							'title' 		=> __( 'Title', 'woocommerce' ),
							'type' 			=> 'text',
							'description'	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
							'default' 		=> __( 'Eximbay', 'woocommerce' ),
							'desc_tip'      => true,
						),
			'description' => array(
							'title' 		=> __( 'Description', 'woocommerce' ),
							'type' 			=> 'textarea',
							'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
							'default' 		=> __( 'Pay via Eximbay; you can pay with your credit card if you don\'t have a Eximbay account', 'woocommerce' )
						),
			'merchant' => array(
							'title' 		=> __('Merchant Name', 'woocommerce'),
							'type' 			=> 'text',
							'description' 	=> __('Enter your merchant name.')
						),
			'mid' => array(
							'title' 		=> __('Merchant ID', 'woocommerce'),
							'type' 			=> 'text',
							'description' 	=> __('Merchant ID at Eximbay.')
						),
            'secretKey' => array(
							'title' 		=> __('Secret Key', 'woocommerce'),
							'type' 			=> 'text',
							'description' 	=>  __('Given to Merchant by Eximbay', 'eximbay')
						),
    		'ver' => array(
    						  'title'       => __( 'API Version', 'woocommerce' ),
    						  'type'        => 'select',
    						  'description' => __( 'Choose API Version.', 'woocommerce' ),
    					      'default'     => '170',
    					      'desc_tip'    => true,
    						  'options'     => array(
    						  		'170'   	=> __( '170', 'woocommerce' ),
    						  		'180'   	=> __( '180', 'woocommerce' ),
    								'200' 		=> __( '200', 'woocommerce' )
    										)
    					),
			'testing' => array(
							'title' 		=> __( 'Gateway Testing', 'woocommerce' ),
							'type' 			=> 'title',
							'description'	=> '',
						),
			'testmode' => array(
							'title' 		=> __( 'Eximbay Test Mode', 'woocommerce' ),
							'type' 			=> 'checkbox',
							'label' 		=> __( 'Enable Test Mode', 'woocommerce' ),
							'default' 		=> 'yes',
							'description' 	=> __( 'Eximbay Test URL can be used to test payments.', 'woocommerce' )
						),
    		'debug' => array(
    						'title'       	=> __( 'Debug Log', 'woocommerce' ),
    						'type'        	=> 'checkbox',
    						'label'      	=> __( 'Enable logging', 'woocommerce' ),
    						'default'    	=> 'no',
    						'description' 	=> sprintf( __( 'Log Eximbay events, such as IPN requests, inside <code>%s</code>', 'woocommerce' ), wc_get_log_file_path( 'eximbay_'.date("ymd") ) )
    					)
    			/*,
			 'status_page_id' => array(
							'title' => __('Status Page'),
							'type' => 'select',
							'options' => $this -> get_pages('Select Page'),
							'description' => "URL of status page"
						)*/
			);

    }


	/**
	 * Get Eximbay Args for passing Payment Processing
	 *
	 * @access public
	 * @param mixed $order
	 * @return array
	 */
	function get_eximbay_args( $order ) {
		global $woocommerce;

		$order_id = $order->id;

		$ref = $order_id.'_'.date("ymdHis");
		
		$cur = get_woocommerce_currency();
		$amt = $order->get_total();   		//$order->get_order_total();  

		$linkBuf = $this->secretKey. "?mid=" . $this->mid ."&ref=" . $ref ."&cur=" .$cur ."&amt=" .$amt;
		$fgkey = hash("sha256", $linkBuf);
		
		/*$item_name = '';
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				$product = $order->get_product_from_item( $item );
				$item_name 	.= $item['name'] . ' ';
			}
		}*/

		//$return_url = str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_Eximbay', home_url( '/' ) ) );
		$return_url = plugins_url( 'return.php', __FILE__ );
		//$status_url = ($this -> status_page_id=="" || $this -> status_page_id==0)?get_site_url() . "/":get_permalink($this -> status_page_id);
		$status_url = WC()->api_request_url( 'WC_Gateway_Eximbay' );
		
		$txntype = 'SALE';
		$ostype = '';
		if($this->ver == '200'){
			$txntype = 'PAYMENT';
			$ostype = 'P';
			if(wp_is_mobile()){
				$ostype = 'M';
			}
		}
		
		// Eximbay Args
		$eximbay_args = array(
				'ver'      				=> $this->ver,
				'mid' 					=> $this->mid,
				'txntype'      			=> $txntype,		
				'ref' 					=> $ref,
				'fgkey' 				=> $fgkey,
				'cur'			 		=> $cur,
				'amt'					=> $amt,
				'product'				=> '',
				'param1'				=> '',
				'param2'				=> '',
				'param3'				=> '',
				'buyer'					=> $order->billing_first_name.' '.$order->billing_last_name,
				'email'					=> $order->billing_email,
				'tel'					=> $order->billing_phone,
				'charset' 				=> 'UTF-8',
				'shop'					=> $this->merchant,
				'lang' 					=> 'EN',
				'returnurl' 			=> $return_url,
				'statusurl'				=> $status_url,
				'autoclose'				=> 'Y',
				'ostype'				=> $ostype,
				'directToReturn'		=> 'N',
				'displaytype'      		=> 'P',
				'dm_billTo_firstName'			=> $order->billing_first_name,
				'dm_billTo_lastName'			=> $order->billing_last_name,
				'dm_billTo_phoneNumber'			=> $order->billing_phone,
				'dm_billTo_city'				=> $order->billing_city,
				'dm_billTo_country'				=> $order->billing_country,
				'dm_billTo_postalCode'			=> $order->billing_postcode,
				'dm_billTo_state'				=> $this->get_eximbay_state( $order->billing_country, $order->billing_state ),
				'dm_billTo_street1'				=> $order->billing_address_1,
				'dm_billTo_street2'				=> $order->billing_address_2,
				'dm_shipTo_firstName'			=> $order->shipping_first_name,
				'dm_shipTo_lastName'			=> $order->shipping_last_name,
				'dm_shipTo_phoneNumber'			=> $order->shipping_phone,
				'dm_shipTo_city'				=> $order->shipping_city,
				'dm_shipTo_country'				=> $order->shipping_country,
				'dm_shipTo_postalCode'			=> $order->shipping_postcode,
				'dm_shipTo_state'				=> $this->get_eximbay_state( $order->shipping_country, $order->shipping_state ),
				'dm_shipTo_street1'				=> $order->shipping_address_1,
				'dm_shipTo_street2'				=> $order->shipping_address_2
		);
		
		if ( $line_items = $this->get_line_items( $order ) ) {
			$eximbay_args = array_merge( $eximbay_args, $line_items );
		}
		
		$eximbay_args = apply_filters( 'woocommerce_eximbay_args', $eximbay_args );

		return $eximbay_args;
	}


    /**
	 * Generate the eximbay button link
     *
     * @access public
     * @param mixed $order_id
     * @return string
     */
    function generate_eximbay_form( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );

		/*if ( $this->testmode == 'yes' ):
			$eximbay_adr = $this->testurl;
		else :
			$eximbay_adr = $this->liveurl;
		endif;*/
		
		$eximbay_adr = $this->get_eximbay_url();

		$eximbay_args = $this->get_eximbay_args( $order );
		
		if ( 'yes' == $this->debug ) {
			$this->log->add( 'eximbay_'.date("ymd"), 'Checking IPN request is valid via ' . $eximbay_adr . '...');
			$this->log->add( 'eximbay_'.date("ymd"), 'IPN Request parameters: ' . print_r( $eximbay_args, true ) );
		}
		
		$eximbay_args_array = array();

		foreach ($eximbay_args as $key => $value) {
			$eximbay_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
		}

		$width = '800';
		$height = '470';
		if('200' == $this->ver){
			$width = '419';
			$height = '712';
		}
		
		return '<form action="'.esc_url( $eximbay_adr ).'" method="post" id="eximbay_payment_form">
				' . implode( '', $eximbay_args_array) . '
				<div id="submit_div"><input type="submit" class="button-alt" id="submit_eximbay_payment_form" value="'.__( 'Pay via Eximbay', 'woocommerce' ).'" /> <a class="button cancel" id="clink" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__( 'Cancel order &amp; restore cart', 'woocommerce' ).'</a></div>
				
				<noscript>'.__( 'JavaScript must be enabled in order for you to use Eximbay in standard view. However, it seems JavaScript is either disabled or not supported by your browser. To use standard view, enable JavaScript by changing your browser options, then ', 'woocommerce' ).'<a href="'.add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' )))).'">'.__( 'try again', 'woocommerce' ).'</a>.
					<style>#submit_div {display:none;}</style>		
				</noscript>
				
				<script type="text/javascript">
					jQuery(function(){
						jQuery("#eximbay_payment_form").submit(function(){

							window.open("", "payment2", "scrollbars=yes,status=no,toolbar=no,resizable=yes,location=no,menu=no,width='.$width.',height='.$height.',top=200,left=300");
							this.target = "payment2";
				
							jQuery("#clink").removeAttr("href");
							jQuery("#submit_eximbay_payment_form").click(function(e) { e.preventDefault(); });
						});
					});
				</script>

				</form>';

	}


    /**
     * Process the payment and return the result
     *
     * @access public
     * @param int $order_id
     * @return array
     */
	function process_payment( $order_id ) {

		$order = new WC_Order( $order_id );
		
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
		);
	}


    /**
     * Output for the order received page.
     *
     * @access public
     * @return void
     */
	function receipt_page( $order ) {

		echo '<p>'.__( 'Thank you for your order, please click the button below to pay with Exibmay.', 'woocommerce' ).'</p>';

		echo $this->generate_eximbay_form( $order );

	}
	
	
	/**
	 * Check for Eximbay Response
	 *
	 * @access public
	 * @return void
	 */
	function check_response() {

		@ob_clean();
		
    	if ( ! empty( $_POST ) ) {
    		$_POST = stripslashes_deep( $_POST );
        	do_action( "valid-eximbay-request", $_POST );

		} else {

			wp_die( "Eximbay IPN Request Failure" );

   		}

	}


	/**
	 * Successful Payment!
	 *
	 * @access public
	 * @param array $posted
	 * @return void
	 */
	function successful_request($posted) {
		global $woocommerce;
		
		if ( 'yes' == $this->debug ) {
			$this->log->add( 'eximbay_'.date("ymd"), 'IPN Response parameters: ' . print_r( $posted, true ) );
		}
		
	    if ( ! empty( $posted['ref'] ) ) {
			
			$order_id_time = $posted['ref'];
			$order_id = explode('_', $posted['ref']);
			$order_id = (int)$order_id[0];
			
			if($order_id != ''){
				try
				{
					$order = new WC_Order( $order_id );
					
					if ( 'yes' == $this->debug ) {
						$this->log->add( 'eximbay_'.date("ymd"), 'Found order #' . $order->id );
					}
					
					$mid = $posted['mid'];
					$ref = $posted['ref'];
					$cur = $posted['cur'];
					$amt = $posted['amt'];
					$shop = $posted['shop'];
					$buyer = $posted['buyer'];
					$tel = $posted['tel'];
					$email = $posted['email'];
					$product = $posted['product'];
					$lang = $posted['lang'];
					$param1 = $posted['param1'];
					$param2 = $posted['param2'];
					$param3 = $posted['param3'];
					
					
					$transid = $posted['transid'];
					$rescode = $posted['rescode'];
					$resmsg = $posted['resmsg'];
					$authcode = $posted['authcode'];
					$cardco = $posted['cardco'];
					$resdt = $posted['resdt'];
					$fgkey = $posted['fgkey'];
					
											
					if ($rescode == '0000')
					{	

							$linkBuf = $this->secretKey. "?mid=" . $mid ."&ref=" . $ref ."&cur=" .$cur ."&amt=" .$amt ."&rescode=" .$rescode ."&transid=" .$transid;
							//$newFgkey = md5($linkBuf);
							$newFgkey = hash("sha256", $linkBuf);
							
							if ( 'yes' == $this->debug ) {
								$this->log->add( 'eximbay_'.date("ymd"), 'FGKEY CHECK:' . $linkBuf . '=>' . $newFgkey . ' / ' . strtolower($fgkey));
							}
							
							if(strtolower($fgkey) != $newFgkey){
								 $order -> update_status('failed');
								 $order -> add_order_note('Security Error. Illegal access detected');
							}else{                           
								$order -> payment_complete();
								//$order->update_status('processing', __( 'Payment received.', 'woocommerce' ));
								// Reduce stock
								//$order->reduce_order_stock();
                                $order -> add_order_note('Eximbay payment successful.<br/>Unique Id from Eximbay: '.$posted['transid']);
                                $woocommerce -> cart -> empty_cart();
								//$redirect_url = $this->get_return_url( $order );
								//echo $this->redirect($order, $redirect_url);
							}
					}else{
                            $order -> add_order_note('Transaction Declined: '.$posted['rescode'].'-'.$posted['resmsg']);
							//$woocommerce->add_error( __( 'Sorry, the transaction was declined.', 'woocommerce' ) );
							//$redirect_url =  $order->get_cancel_order_url();
							//echo $this->redirect($order, $redirect_url);
					}
								
				}catch(Exception $e){
                }

	        }
				exit;
	    }

	}
	
	/**
	 * Get the Eximbay URL.
	 *
	 * @return string
	 */
	public function get_eximbay_url() {
	
		if ( 'yes' == $this->testmode ) {
			if ( '170' == $this->ver || '180' == $this->ver ) {
				if ( wp_is_mobile() ) {
					return 'https://www.test.eximbay.com/web/mpayment/payment_real.do';
				}else
					return 'https://www.test.eximbay.com/web/payment2.0/payment_real.do';
			}
			
			return 'https://secureapi.test.eximbay.com/Gateway/BasicProcessor.krp';
			
		} else {
			if ( '170' == $this->ver || '180' == $this->ver ) {
				if ( wp_is_mobile() ) {
					return 'https://www.eximbay.com/web/mpayment/payment_real.do';
				}else
					return 'https://www.eximbay.com/web/payment2.0/payment_real.do';
			}
			
			return 'https://secureapi.eximbay.com/Gateway/BasicProcessor.krp';
		}	
	}
	
	//Redirect to return page
	function redirect($order, $redirect_url) {
		return '<script language="JavaScript">
					window.opener.location.href="'.$redirect_url.'";	
					self.close();
				</script>';
	}
	
	
	/**
	 * Get line items to send to Eximbay
	 *
	 * @param  WC_Order $order
	 * @return array on success, or false when it is not possible to send line items
	 */
	private function get_line_items( $order ) {

		$item_loop        = 0;
		$args             = array();

		// Products
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( ! $item['qty'] ) {
					continue;
				}
				
				$product   = $order->get_product_from_item( $item );
				$item_name = $item['name'];
				
				if($item_loop == 0)
					$args['product'] = $this->eximbay_item_name($item_name);
				
				$args['dm_item_'.$item_loop.'_product'] = $this->eximbay_item_name( $item_name );
				$args['dm_item_'.$item_loop.'_quantity']  = $item['qty'];
				$args['dm_item_'.$item_loop.'_unitPrice']    = $order->get_item_subtotal( $item, false );
	
				if ( $args['dm_item_'.$item_loop.'_unitPrice'] < 0 ) {
					return false; // Abort - negative line
				}
				
				$item_loop ++;
			}
			
			if($item_loop > 1){
				$item_cnt = $item_loop - 1;
				$args['product'] .= " and " . $item_cnt . " more ...";
			}
		}
			
		return $args;
	}
	
	/**
	 * Limit the length of item names
	 * @param  string $item_name
	 * @return string
	 */
	public function eximbay_item_name( $item_name ) {
		if ( strlen( $item_name ) > 127 ) {
			$item_name = substr( $item_name, 0, 124 ) . '...';
		}
		return html_entity_decode( $item_name, ENT_NOQUOTES, 'UTF-8' );
	}
	
	
	/**
	 * Get the state to send to Eximbay
	 * @param  string $cc
	 * @param  string $state
	 * @return string
	 */
	public function get_eximbay_state( $cc, $state ) {
		if ( 'US' === $cc ) {
			return $state;
		}
	
		$states = WC()->countries->get_states( $cc );
	
		if ( isset( $states[ $state ] ) ) {
			return $states[ $state ];
		}
	
		return $state;
	}
	
	 // get all pages
    function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';
            // show indented child pages?
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            // add to page list array array
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }

}

/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_eximbay_gateway($methods) {
     $methods[] = 'WC_Gateway_Eximbay';
     return $methods;
}
 
add_filter('woocommerce_payment_gateways', 'woocommerce_add_eximbay_gateway' );

}
