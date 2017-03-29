<?php

require('../../../wp-blog-header.php');

global $woocommerce;

$ref = $_POST['ref'];
/*$mid = $_POST['mid'];
$cur = $_POST['cur'];
$amt = $_POST['amt'];
$shop = $_POST['shop'];
$buyer = $_POST['buyer'];
$tel = $_POST['tel'];
$email = $_POST['email'];
$product = $_POST['product'];
$lang = $_POST['lang'];
$param1 = $_POST['param1'];
$param2 = $_POST['param2'];
$param3 = $_POST['param3'];*/
	
$rescode = $_POST['rescode'];
$resmsg = $_POST['resmsg'];
/*$transid = $_POST['transid'];
$authcode = $_POST['authcode'];
$cardco = $_POST['cardco'];
$resdt = $_POST['resdt'];
$fgkey = $_POST['fgkey'];*/


if ( ! empty( $ref ) ) {
		
	$order_id_time = $ref;
	$order_id = explode('_', $ref);
	$order_id = (int)$order_id[0];
		
	if($order_id != ''){
		try
		{
			$order = new WC_Order( $order_id );

			$redirect_url = '';
			
			if ($rescode == '0000')
			{
				$redirect_url = $order->get_checkout_order_received_url();
			}else{
				//$woocommerce->add_error( __( $resmsg, 'woocommerce' ) );
				wc_add_notice( $resmsg, $notice_type = 'error' );
				$redirect_url =  $order->get_cancel_order_url();
			}
			
			echo '<script language="JavaScript">
					window.opener.location.href="'.$redirect_url.'";
					self.close();
				  </script>';

		}catch(Exception $e){
		}
	}
	
	exit;
}