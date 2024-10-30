<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sample instance based method.
 */
class WC_Shipping_B4Y extends WC_Shipping_Method {

	/**
	 * Constructor. The instance ID is passed to this.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'b4y_method';
		$this->instance_id 			     = absint( $instance_id );
		$this->method_title          = __( 'Bring4You' );
		$this->method_description    = __( 'Livraison d\'objets encombrants' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
		);
	    	$this->instance_form_fields = array(
        		'enabled' => array(
        			'title' 		=> __( 'Activer/Désactiver' ),
        			'type' 			=> 'checkbox',
        			'label' 		=> __( 'Activer cette méthode' ),
        			'default' 		=> 'yes',
        		),
        		'title' => array(
        			'title' 		=> __( 'Bring4You' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'Livraison d\'objets encombrants' ),
        			'default'		=> __( 'Bring4You' ),
        			'desc_tip'		=> true
        		)
		);
		$this->enabled		    = $this->get_option( 'enabled' );
		$this->title                = $this->get_option( 'title' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function is_authorize($products_ids = null) {		
		$cats = get_option('b4y_plugin_setting_cats');
		
		if($products_ids){
			foreach($products_ids as $product_id)
			{
				$terms = get_the_terms($product_id, 'product_cat' );
				
	 			if($terms){
					foreach ($terms  as $term ) {                    
						$product_cat_id = $term->term_id;     
						
						if(in_array($product_cat_id ,$cats))
							return true;
					}
				}
			}
		}
		
		return false;
	}

	/**
	 * calculate_shipping function.
	 * @param array $package (default: array())
	 */
	public function calculate_shipping( $package = array() ) {
		global $woocommerce,$order,$post;

		$products = $woocommerce->cart->get_cart();

		$products_ids = array();

		$totalweight = 0;
		$totalvolume = 0;
		
		/* Calculate the total weight and volume */
		
		foreach($products as $product)
		{   
			$product_parent=$product['data']->get_parent_id();
			
			if($product_parent==0)
				$products_ids[] = $product['data']->get_id();
			else
				$products_ids[] = $product_parent;
			
			$weight = $product['data']->get_weight();
			$width = $product['data']->get_width();
			$height = $product['data']->get_height();
			$depth = $product['data']->get_length(); 
			$quantity = $product['quantity']; 
			
			if(is_numeric($width) && is_numeric($height) && is_numeric($depth) && is_numeric($quantity))
				$totalvolume = $totalvolume + (($width  * $height * $depth) * $quantity);
			
			if(is_numeric($weight) && is_numeric($quantity))
				$totalweight = $totalweight + ($weight * $quantity);
		}
		
		/* Arrival address */

		// Get the user ID from WC_Order methods
		$user_id = get_current_user_id();
		
		$customer = new WC_Customer($user_id);
		
		if(WC()->checkout->get_value('shipping_city') != '')
		{
			$address = WC()->checkout->get_value('shipping_address_1').' ';
			$address .= WC()->checkout->get_value('shipping_address_2').' ';
			$address .= WC()->checkout->get_value('shipping_postcode').' ';
			$address .= WC()->checkout->get_value('shipping_city').' ';
			$address .= WC()->checkout->get_value('shipping_country').' ';
		}
		else
		{
			$address = WC()->checkout->get_value('billing_address_1').' ';
			$address .= WC()->checkout->get_value('billing_address_2').' ';
			$address .= WC()->checkout->get_value('billing_postcode').' ';
			$address .= WC()->checkout->get_value('billing_city').' ';
			$address .= WC()->checkout->get_value('billing_country').' ';
		}
		
		$arrival = urlencode($address);

		$departure = urlencode(get_option('b4y_plugin_setting_adresse'));
		
		$url = "https://bring4you.com/external-api/v1/priceEstimation?";
		
		if($departure != "")
		{
			$url = $url."origin_city=".$departure."&";
		}
		
		if($arrival != "")
		{
			$url = $url."destination_city=".$arrival."&";
		}
		
		if($totalweight != 0)
		{
			$url = $url."weight=".$totalweight."&";
			
		}
		
		$totalweight = round(wc_get_weight( $totalweight,'kg'));

		if($totalvolume != 0)
		{
			$url = $url."volume=".$totalvolume."&";
			
		}
		
		$key = get_option('b4y_plugin_setting_key');
		
		if($key == 'def28227-d98e-4800-b777-2725a24bcece')
		{
			$dev = true;
		}
		
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Content-Type: application/json",
				"X-Api-Key: ".$key
			],
		]);

		$response = curl_exec($curl);

		$err = curl_error($curl);

		curl_close($curl);
		
		// make request
		// make request
		//$response = wp_remote_get($url,array('sslverify' => FALSE));
		//$body     = wp_remote_retrieve_body($response); 

		// convert response
		$resultArray = json_decode($response,true);
		
		// If we manage error directly from wordpress
		
		if(isset($resultArray['name']) && $resultArray['name'] == "ValidationError")
		{
			// Error From Server
			return 0;
		}
		
		$price = $resultArray['value'];
		
		$percent = get_option('b4y_plugin_setting_comm');
			
		if($percent != "")
			$price = ($price * (100 + $percent))/100;
		
		$price = round($price);
		
		$auth = $this->is_authorize($products_ids);

		if($auth)
		{
			$this->add_rate( array(
				'id'    => $this->id . $this->instance_id,
				'label' => $this->title,
				'cost'  => $price,
			) );
		}
	}
}