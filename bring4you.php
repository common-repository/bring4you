<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://bring4you.com/
 * @since             1.0.0
 * @package           Bring4you
 *
 * @wordpress-plugin
 * Plugin Name:       Bring4You
 * Plugin URI:        https://gitlab.com/bring4you/b4u-wordpress-plugin/-/archive/master/b4u-wordpress-plugin-master.zip
 * Description:       Module permettant d'ajouter le transporteur Bring4You à votre installation Woocommerce
 * Version:           1.0.9
 * Author:            Bring4You
 * Author URI:        http://bring4you.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bring4you
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'B4Y_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
 
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BRING4YOU_VERSION', '1.0.10' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bring4you-activator.php
 */
function B4Y_activate_bring4you() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bring4you-activator.php';
	Bring4you_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bring4you-deactivator.php
 */
function B4Y_deactivate_bring4you() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bring4you-deactivator.php';
	Bring4you_Deactivator::deactivate();
}

add_action('plugins_loaded', 'B4Y_load_plugins', 0);
function B4Y_load_plugins() {
    require_once plugin_dir_path( __FILE__ ) . 'class/B4Y-shipping.php';
}

register_activation_hook( __FILE__, 'B4Y_activate_bring4you' );
register_deactivation_hook( __FILE__, 'B4Y_deactivate_bring4you' );
add_action('admin_menu', 'B4Y_load_menu' );
add_shortcode('b4y_estimation', 'B4Y_estimation');
add_action( 'admin_init', 'B4Y_register_settings' );
add_action( 'wp_ajax_nopriv_b4yestimation', 'B4Y_estimation_result' );
add_action( 'wp_ajax_b4yestimation', 'B4Y_estimation_result' );
add_filter( 'woocommerce_shipping_methods', 'B4Y_register_b4y_method' );
add_filter( 'woocommerce_package_rates', 'B4Y_shipping_rate_cost_calculation', 10, 2 );

// Add B4Y field to Orders

add_filter( 'manage_edit-shop_order_columns', 'set_b4y_edit_shop_order_columns' );
function set_b4y_edit_shop_order_columns($columns) {
    $columns['b4y_send'] = __( 'Bring4You', 'b4y' );
    return $columns;
}

add_action( 'manage_shop_order_posts_custom_column' , 'b4y_shop_order_column', 10, 2 );
function b4y_shop_order_column( $column, $post_id ) {
    switch ( $column ) {
        case 'b4y_send' :
					$id = $post_id;
					
					$value = get_post_meta( $id, 'b4y_send', true );
					
					if($value == false)
					{
						echo '<a href="'.admin_url().'post.php?post='.$id.'&action=edit#b4y_sent_it_'.$id.'" class="b4y_sent_it button"><img src="'.plugin_dir_url( __FILE__ ).'views/img/b4y.png" class="b4y-car" alt=""> '.__( 'Envoyer avec Bring4You', 'b4y' ).'</a>';
					}
					else
					{
						echo __( 'Tâche créée chez Bring4You', 'b4y' );
					}
          break;
    }
}

add_action( 'add_meta_boxes', 'add_shop_order_meta_box' );
function add_shop_order_meta_box() {
    add_meta_box(
        'b4y_send',
        __( 'Bring4You', 'b4y' ),
		'shop_order_display_callback',
		'shop_order'
    );
}

function shop_order_display_callback( $post ) {
		$id = $post->ID;

		$current_order = wc_get_order($id);

    $value = get_post_meta( $id, 'b4y_send', true );
		
		if($current_order->shipping_city != '')
		{
			$address = $current_order->shipping_address_1.' ';
			$address .= $current_order->shipping_address_2.' ';
			$address .= $current_order->shipping_postcode.' ';
			$address .= $current_order->shipping_city.' ';
			$address .= $current_order->shipping_country.' ';
		}
		else
		{
			$address = $current_order->billing_address_1.' ';
			$address .= $current_order->billing_address_2.' ';
			$address .= $current_order->billing_postcode.' ';
			$address .= $current_order->billing_city.' ';
			$address .= $current_order->billing_country.' ';
		}
		
		$name = '';
		
		if($current_order->shipping_first_name != '' && $current_order->shipping_last_name != '')
		{
			$name = $current_order->shipping_first_name.' ';
			$name .= $current_order->shipping_last_name;
		}
		else
		{
			$name = $current_order->billing_first_name.' ';
			$name .= $current_order->billing_last_name;
		}
		
		$phone = '';
		
		if($current_order->shipping_phone != '')
		{
			$phone = $current_order->shipping_phone;
		}
		else
		{
			$phone = $current_order->billing_phone;
		}
		
		$from_name = get_option('b4y_plugin_setting_sender_name');
		$from_phone = get_option('b4y_plugin_setting_sender_phone');
		$from_address = get_option('b4y_plugin_setting_adresse');
		$from_long = get_option('b4y_plugin_setting_adresse_longitude');
		$from_lat = get_option('b4y_plugin_setting_adresse_latitude');
		
		echo '<div id="popupChoose_'.$id.'" class="b4y-modal">
						<div class="b4y-modal-content">
							<span class="b4y-close">&times;</span>
							<div>
								<h3>'.__( 'Envoyer votre colis avec Bring4You').'</h3>
								<p class="important-hightlight">&#x26A0; '.__( 'Merci de valider l\'adresse de votre destinataire en sélectionnant l\'adresse correcte dans la liste, puis cliquez sur valider pour procéder à l\'envoi de la demande').'</p>
								<h4>Expéditeur</h4>
								<input type="text" autocomplete="no" name="b4y_from_name" id="b4y_from_name" value="'.$from_name.'">	
								<input type="text" autocomplete="no" name="b4y_from_phone" id="b4y_from_phone" value="'.$from_phone.'">	
								<input type="text" autocomplete="no" name="b4y_from_adresse" id="b4y_from_adresse" value="'.$from_address.'">
								<input type="button" class="button button-primary valid" name="validate_from" id="validate_from" value="'.__( 'Valider adresse').'" />
								<input type="hidden" name="b4y_from_adresse_longitude" id="b4y_from_adresse_longitude" value="'.$from_long.'" >	
								<input type="hidden" name="b4y_from_adresse_latitude" id="b4y_from_adresse_latitude" value="'.$from_lat.'">	
								<div id="select_from_box_wrapper">
									<div id="b4y_select_from_address"></div>
								</div>
								<h4>Destinataire</h4>
								<input type="text" autocomplete="no" name="b4y_to_name" id="b4y_to_name" value="'.$name.'">	
								<input type="text" autocomplete="no" name="b4y_to_phone" id="b4y_to_phone" value="'.$phone.'">	
								<input type="text" autocomplete="no" name="b4y_to_adresse" id="b4y_to_adresse" value="'.$address.'">	
								<input type="button" class="button button-primary" name="validate_to" id="validate_to" value="'.__( 'Valider adresse').'" />
								<input type="hidden" name="b4y_to_adresse_longitude" id="b4y_to_adresse_longitude" value="" >	
								<input type="hidden" name="b4y_to_adresse_latitude" id="b4y_to_adresse_latitude" value="">	
								<div id="select_box_wrapper">
									<div id="b4y_select_address"></div>
								</div>
								<textarea name="b4y_description" id="b4y_description" placeholder="'.__( 'Spécification de votre envoi.').'"></textarea>
								<br/>
								<input type="hidden" name="b4y_estimation" id="b4y_estimation" value="">									
								<div id="b4y_result_estimation"></div>
								<br/>
								<a href="#" id="b4y_sent_it_now_'.$id.'" data-post-id="'.$id.'" data-address="" data-latitude="" data-longitude="" class="button hideonfirst b4y_sent_it_now">'.__( 'Envoyer', 'b4y' ).'</a>
							</div>
						</div>
					</div>';
		echo '<div id="b4y_result"></div>';
		if(!$value)
		{
			echo '<a href="#" id="b4y_sent_it_'.$id.'" data-post-id="'.$id.'" class="b4y_sent_it button"><img src="'.plugin_dir_url( __FILE__ ).'views/img/b4y.png" class="b4y-car" alt=""> '.__( 'Envoyer avec Bring4You', 'b4y' ).'</a>';
		}
		else
		{
			echo __( 'Tâche créée chez Bring4You', 'b4y' );
		}
}

add_action( 'wp_ajax_b4y_send_it', 'b4y_send_it' );
add_action( 'wp_ajax_nopriv_b4y_send_it', 'b4y_send_it' );
add_action( 'wp_ajax_b4y_estimationadmin', 'b4y_estimationadmin' );
add_action( 'wp_ajax_nopriv_b4y_estimationadmin', 'b4y_estimationadmin' );

function b4y_send_it() {
	$post_id = intval( $_POST['post_id'] );
	
	$to = array();
	$to['address'] = $_POST['address'];
	$to['latitude'] = $_POST['latitude'];
	$to['longitude'] = $_POST['longitude'];
	$to['name'] = $_POST['name'];
	$to['phone'] = $_POST['phone'];
	
	$from = array();
	$from['address'] = $_POST['from_address'];
	$from['latitude'] = $_POST['from_latitude'];
	$from['longitude'] = $_POST['from_longitude'];
	$from['name'] = $_POST['from_name'];
	$from['phone'] = $_POST['from_phone'];
	
	
	$description = $_POST['description'];
	
	$price = $_POST['price'];
	
	$response = B4Y_task_creation($post_id,$to,$from,$price,$description);
	
	$responseArray = json_decode($response,true);
	
	if(isset($responseArray['statusCode']) && $responseArray['statusCode'] == 400)
	{
		$details = $responseArray['details'][0];
			
		$message = array();
		$message[] = 'error';
		$message[] = $details;
			
		echo json_encode($message);
		wp_die();
	}
	else
	{
		update_post_meta($post_id, 'b4y_send', 'true' );
		
		$message = array();
		$message[] = 'ok';
		$message[] = $response;
		
		echo json_encode($message);
		
		wp_die();
	}
}

function b4y_estimationadmin() { 
	$post_id = intval( $_POST['post_id'] );

	$current_order = wc_get_order($post_id);
	
	$to = array();
	$to['address'] = $_POST['address'];
	
	$from = array();
	$from['address'] = $_POST['address_from'];
	
	$totalvolume = 0;
	$totalweight = 0;
	
	$items = array();
	
	foreach ( $current_order->get_items() as $item_id => $item ) {
		$product = $item->get_product();
		
		$weight = $product->get_weight();
		$width = $product->get_width();
		$height = $product->get_height();
		$length = $product->get_length(); 
		$quantity = $item->get_quantity(); 
		
		$volume = $quantity * ($width*$height*$length);
		$totalvolume = $totalvolume + $volume;
		
		$weight = $quantity * $weight;
		$totalweight = $totalweight + $weight;
	}
	
	$arrival = urlencode(sanitize_text_field($to['address']));
	$departure = urlencode(sanitize_text_field($from['address']));
	
	$url = "https://bring4you.com/external-api/v1/priceEstimation?";
	
	$weight = round(wc_get_weight( $totalweight,'kg'));

	$url = $url."weight=".$totalweight."&";

	$url = $url."volume=".$totalvolume."&";

	$url = $url."origin_city=".$departure."&";

	$url = $url."destination_city=".$arrival."&";

		
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

	//$response = wp_remote_get($url,array('sslverify' => FALSE));
	//$body     = wp_remote_retrieve_body($response); 

	// convert response
	$resultArray = json_decode($response,true);
	
	$price = $resultArray['value'];
	echo round($price);
	wp_die();
} 

add_action( 'admin_footer', 'b4y_sent_it_script' );
function b4y_sent_it_script() {
    ?>
    <script>
        jQuery(document).ready(function ($) {
					
					// Get the popup
					var modal = jQuery(".b4y-modal");
					
					var finalsend = jQuery(".hideonfirst");
					
					// Get the <span> element that closes the modal
					var span = jQuery(".b4y-close")[0];

					// When the user clicks on <span> (x), close the modal
					span.onclick = function() {
						modal.hide();
					}

					// When the user clicks anywhere outside of the modal, close it
					window.onclick = function(event) {
						if (event.target == modal) {
							modal.hide();
						}
					}
					
					jQuery('.b4y_sent_it').click(function () {
						var post_id = jQuery(this).attr('data-post-id');
									
						finalsend.hide();
						jQuery("#popupChoose_"+post_id).show();
					});
					
					jQuery('.b4y_sent_it_now').click(function () {
						var post_id = jQuery(this).attr('data-post-id');
						var address = jQuery(this).attr('data-address');
						var latitude = jQuery(this).attr('data-latitude');
						var longitude = jQuery(this).attr('data-longitude');
						var name = jQuery("#b4y_to_name").val();
						var phone = jQuery("#b4y_to_phone").val();
						
						var from_address = jQuery("#b4y_from_adresse").val();
						var from_longitude = jQuery("#b4y_from_adresse_longitude").val();
						var from_latitude = jQuery("#b4y_from_adresse_latitude").val();
						var from_name = jQuery("#b4y_from_name").val();
						var from_phone = jQuery("#b4y_from_phone").val();
						
						var description = jQuery("#b4y_description").val();
						var price = jQuery("#b4y_estimation").val();
						
						send_it(post_id, address, latitude, longitude,price,description,name,phone,from_address,from_longitude,from_latitude,from_name,from_phone);
					});
		});
				
				function send_it(post_id, address, latitude, longitude,price,description,name,phone,from_address,from_longitude,from_latitude,from_name,from_phone)
				{
					var data = {
							'action': 'b4y_send_it',
							'post_id': post_id,
							'address': address,
							'latitude': latitude,
							'longitude': longitude,
							'description':description,
							'price':price,
							'name':name,
							'phone':phone,
							'from_address':from_address,
							'from_longitude':from_longitude,
							'from_latitude':from_latitude,
							'from_name':from_name,
							'from_phone':from_phone,
							
					};

					jQuery.post(ajaxurl, data, function (response) {
						console.log( "Result:", response );
						var result = JSON.parse(response);
						var firstElement = result.shift();
						
						if(firstElement == "error")
						{
							var message = result.shift();
							alert('Erreur lors de l\'enregistrement sur Bring4You : ' + JSON.stringify(message));
						}
						else {
							jQuery("#b4y_result").html('Votre demande d\'envoi a été correctement envoyé');
							alert('Votre demande d\'envoi a été correctement envoyé');
							jQuery(".b4y-modal").hide();
							jQuery("#b4y_sent_it_" + post_id).hide();
						}
						
						
					});
				}
				
				jQuery('#validate_to').click(function () {
					autocomplete_addess();
				});
				
				jQuery('#validate_from').click(function () {
					autocomplete_addess_from();
				});
				
				jQuery('#b4y_to_adresse').keyup(function () {
					autocomplete_addess();
				});
				
				jQuery('#b4y_from_adresse').keyup(function () {
					autocomplete_addess_from();
				});
				
				function autocomplete_addess()
				{
					jQuery.ajax({
						url: "https://pelias.bring4you.com/v1/autocomplete",
						method: "GET",
						dataType: "json",
						data: {
							"text": jQuery('#b4y_to_adresse').val(),
						},
						success: function( data, status, jqxhr ){
							//console.log( "Request received:", data );
							var selectHTML = '<select id="b4y_select_address_select" c size="10" style="width: 50%;max-width:50%;">';

							//console.log( "Collected:", data.features );
							
							for (var key in data.features)
							{
								var b4y_class = 'b4y_select_address_selected';
								if(jQuery('#b4y_to_adresse').val() == data.features[key].properties.label)
									b4y_class += ' b4y_select_address_selected_on';
								selectHTML +='<option value="'+data.features[key].properties.id+'" data-label="'+data.features[key].properties.label+'" data-long="'+data.features[key].geometry.coordinates[0]+'" data-lat="'+data.features[key].geometry.coordinates[1]+'" class="'+b4y_class+'">'+data.features[key].properties.label+'</option>';
							}
							
							selectHTML +='</select>';
							
							jQuery('#b4y_select_address').html(selectHTML);
						},
						error: function( jqxhr, status, error ){
							console.log( "Something went wrong!" );
						}
					});
				}
				
				function autocomplete_addess_from()
				{
					jQuery.ajax({
						url: "https://pelias.bring4you.com/v1/autocomplete",
						method: "GET",
						dataType: "json",
						data: {
							"text": jQuery('#b4y_from_adresse').val(),
						},
						success: function( data, status, jqxhr ){
							//console.log( "Request received:", data );
							var selectHTML = '<select id="b4y_select_from_address_select" c size="10" style="width: 50%;max-width:50%;">';

							//console.log( "Collected:", data.features );
							
							for (var key in data.features)
							{
								var b4y_class = 'b4y_select_from_address_selected';
								if(jQuery('#b4y_from_adresse').val() == data.features[key].properties.label)
									b4y_class += ' b4y_select_from_address_selected_on';
								selectHTML +='<option value="'+data.features[key].properties.id+'" data-label="'+data.features[key].properties.label+'" data-long="'+data.features[key].geometry.coordinates[0]+'" data-lat="'+data.features[key].geometry.coordinates[1]+'" class="'+b4y_class+'">'+data.features[key].properties.label+'</option>';
							}
							
							selectHTML +='</select>';
							
							jQuery('#b4y_select_from_address').html(selectHTML);
						},
						error: function( jqxhr, status, error ){
							console.log( "Something went wrong!" );
						}
					});
				}
				
				jQuery('body').on('click','.b4y_select_from_address_selected', function () {
						var label = jQuery(this).attr('data-label');
						var longitude = jQuery(this).attr('data-long');
						var latitude = jQuery(this).attr('data-lat');
						var finalsend = jQuery(".hideonfirst");
						var post_id = jQuery('.b4y_sent_it_now').attr('data-post-id');
						
						jQuery('#b4y_from_adresse').val(label);
						jQuery('#b4y_from_adresse_latitude').val(latitude);
						jQuery('#b4y_from_adresse_longitude').val(longitude);
						
						jQuery('#b4y_select_from_address').html('');

						var data = {
							'action': 'b4y_estimationadmin',
							'post_id': post_id,
							'address': jQuery('#b4y_to_adresse').val(),
							'address_from': jQuery('#b4y_from_adresse').val()
						};
						
						jQuery.post(ajaxurl, data, function (response) {
							console.log( "Estimate B4Y:", response );
							jQuery('#b4y_estimation').val(response);
							jQuery('#b4y_result_estimation').html("Estimation du tarif : " + response + " €");
						});
				});
				
				jQuery('body').on('click','.b4y_select_address_selected', function () {
						var label = jQuery(this).attr('data-label');
						var longitude = jQuery(this).attr('data-long');
						var latitude = jQuery(this).attr('data-lat');
						var finalsend = jQuery(".hideonfirst");
						var post_id = jQuery('.b4y_sent_it_now').attr('data-post-id');
						
						jQuery('#b4y_to_adresse').val(label);
						jQuery('#b4y_to_adresse_latitude').val(latitude);
						jQuery('#b4y_to_adresse_longitude').val(longitude);
						
						jQuery('#b4y_select_address').html('');
						
						jQuery("#b4y_sent_it_now_" + post_id).attr('data-address',label);
						jQuery("#b4y_sent_it_now_" + post_id).attr('data-latitude',latitude);
						jQuery("#b4y_sent_it_now_" + post_id).attr('data-longitude',longitude);
						
						var data = {
							'action': 'b4y_estimationadmin',
							'post_id': post_id,
							'address': jQuery('#b4y_to_adresse').val(),
							'address_from': jQuery('#b4y_from_adresse').val()
						};
						
						jQuery.post(ajaxurl, data, function (response) {
							console.log( "Estimate B4Y:", response );
							jQuery('#b4y_estimation').val(response);
							jQuery('#b4y_result_estimation').html("Estimation du tarif : " + response + " €");
						});
						
						jQuery('#validate_to').addClass('valid');
						
						finalsend.show();
				});
    </script>

    <?php
}

// END - Add B4Y field to Orders

function B4Y_task_creation($post_id,$to,$from,$price,$description) {
	$curl = curl_init();

	$current_order = wc_get_order($post_id);

	$key = get_option('b4y_plugin_setting_key');

	$dev = false;
	
	$items = array();
	
	foreach ( $current_order->get_items() as $item_id => $item ) {
		$product = $item->get_product();
		
		$weight = $product->get_weight();
		$width = $product->get_width();
		$height = $product->get_height();
		$length = $product->get_length(); 
		$quantity = $item->get_quantity(); 
		$name = $product->get_name(); 
		
		$items[] = array(
			'name' => $name,
			'quantity' => (int)$quantity,
			'weight' => (float)$weight,
			'dimension' => array('width' => (float)$width, 'length' => (float)$length, 'height' => (float)$height)
		);
	}
	
	/*$from = array();
	$from['address'] = get_option('b4y_plugin_setting_adresse');
	$from['latitude'] = get_option('b4y_plugin_setting_adresse_latitude');
	$from['longitude'] = get_option('b4y_plugin_setting_adresse_longitude');
	$from['name'] = get_option('b4y_plugin_setting_sender_name');
	$from['phone'] = get_option('b4y_plugin_setting_sender_phone');*/
	
	$from = array('Address' => $from['address'],'lat' => $from['latitude'],'lng' => $from['longitude'],'name' => $from['name'],'phone' => $from['phone']);
	$to = array('Address' => $to['address'],'lat' => $to['latitude'],'lng' => $to['longitude'],'name' => $to['name'],'phone' => $to['phone']);
	
	$price = round($price/1.18);
	
	$bonus = array(
		'value' => $price,
		'currency' => "EUR",
	);
	
	$externalOrderId = $current_order->get_id();

	if($key == 'def28227-d98e-4800-b777-2725a24bcece')
	{
		$dev = true;
	}
	
	if($description != "")
		$json = '{"dev":'.['false', 'true'][$dev].',"items":'.json_encode($items).',"fromAddress":"'.$from['Address'].'","fromPlace":{"location":{"lat":'.$from['lat'].',"lng":'.$from['lng'].'}},"description":"'.$description.'","toAddress":"'.$to['Address'].'","toPlace":{"location":{"lat":'.$to['lat'].',"lng":'.$to['lng'].'}},"bonus":{"value":"'.$bonus['value'].'","currency":"'.$bonus['currency'].'"},"removal":{"name":"'.$from['name'].'","phone":"'.$from['phone'].'"},"recipient":{"name":"'.$to['name'].'","phone":"'.$to['phone'].'"},"externalOrderId": "'.$externalOrderId.'"}';
	else
		$json = '{"dev":'.['false', 'true'][$dev].',"items":'.json_encode($items).',"fromAddress":"'.$from['Address'].'","fromPlace":{"location":{"lat":'.$from['lat'].',"lng":'.$from['lng'].'}},"toAddress":"'.$to['Address'].'","toPlace":{"location":{"lat":'.$to['lat'].',"lng":'.$to['lng'].'}},"bonus":{"value":"'.$bonus['value'].'","currency":"'.$bonus['currency'].'"},"removal":{"name":"'.$from['name'].'","phone":"'.$from['phone'].'"},"recipient":{"name":"'.$to['name'].'","phone":"'.$to['phone'].'"},"externalOrderId": "'.$externalOrderId.'"}';
		
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://bring4you.com/external-api/v1/tasks",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $json,
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"X-Api-Key: ".$key
		],
	]);

	$response = curl_exec($curl);

	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return $json;
	} else {
		return $response;
	}
}

function B4Y_shipping_rate_cost_calculation( $rates, $package ) {
    foreach( $rates as $rate_key => $rate ) {
        /*$rates[$rate_key]->cost = round(10, 2);*/
    }

    return $rates;
}

function B4Y_register_b4y_method( $methods ) {
	$methods[ 'b4y_method' ] = 'WC_Shipping_B4Y';
	return $methods;
}

function B4Y_estimation_result() { 
	$weight = sanitize_text_field($_POST['weight']);
	$width = sanitize_text_field($_POST['width']);
	$height = sanitize_text_field($_POST['height']);
	$depth = sanitize_text_field($_POST['depth']);
	
	$percent = sanitize_text_field($_POST['percent']);
	
	$volume = $width*$height*$depth;
	
	$estimationText = sanitize_text_field($_POST['estimationtext']);
	
	$arrival = urlencode(sanitize_text_field($_POST['arrival']));
	$departure = urlencode(sanitize_text_field($_POST['departure']));
	
	$language = sanitize_text_field($_POST['lang']);
	
	$url = "https://bring4you.com/external-api/v1/priceEstimation?";
	
	$error = array();
	
	$weight = round(wc_get_weight( $weight,'kg'));

	if($weight != 0)
	{
		$url = $url."weight=".$weight."&";
		
	}
	else
	{
		$error[1] = true; 
	}
	
	if($volume != 0)
	{
		$url = $url."volume=".$volume."&";
		
	}
	else
	{
		$error[2] = true; 
		
		if($width == 0)
		{
			$error[3] = true; 
		}
		if($height == 0)
		{
			$error[4] = true; 
		}
		if($depth == 0)
		{
			$error[5] = true; 
		}
	}
	
	if($departure != "")
	{
		$url = $url."origin_city=".$departure."&";
	}
	else
	{
		$error[6] = true; 
	}
	
	if($arrival != "")
	{
		$url = $url."destination_city=".$arrival."&";
	}
	else
	{
		$error[7] = true; 
	}
	
	//file_put_contents(plugin_dir_path( __FILE__ ).'/log.txt', $url."\r\n", FILE_APPEND);
	
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
	if(!empty($error))
	{
		/*
		*
		* Error 1 : Weight is not set
		* Error 2 : Volume is not set
		* Error 3 : Width is not set
		* Error 4 : Height is not set
		* Error 5 : Depth is not set
		* Error 6 : Departure is not set
		* Error 7 : Arrival is not set
		*
		*/
		
		$errort = __('Erreur détectée, impossible de faire une estimation');
		$errors = __('Erreurs détectées, impossible de faire une estimation');
		$error1 = __('Poids non défini');
		$error2 = __('Le volume ne peut pas être calculé');
		$error3 = __('Largeur non définie');
		$error4 = __('Hauteur non définie');
		$error5 = __('Profondeur non définie');
		$error6 = __('Adresse de départ non définie, nous vous suggerons de contacter le propriétaire du site');
		$error7 = __('Adresse d\'arrivée non définie, merci de remplir le champ correctement');
		$eweight = __('Poids (en kg)');
		$eheight = __('Hauteur (en cm)');
		$ewidth = __('Largeur (en cm)');
		$edepth = __('Profondeur (en cm)');
		$esubmit = __('Envoyer');
		$earrival = __('Ville d\'arrivée avec son code postal. Par exemple : Paris 75000');
		
		$errorform = '<div class="bring4you_errorform">';
		$errormessage = '<div class="bring4you_error">';
		
		if(count($error) > 1)
			$errormessage .= $errort.' :<br/>';
		else
			$errormessage .= $errors.' :<br/>';
		
		$submit = false;
		
		if($error[1])
		{
			$errormessage .= '- '.$error1.'<br/>';
			$errorform .= '<input type="text" class="input-group form-control" id="bring4you_errorform_weight" placeholder="'.$eweight.'">';
			$submit = true;
		}
		else
		{
			$errorform .= '<input type="hidden" class="input-group form-control" id="bring4you_errorform_weight" value="'.$weight.'">';
		}
		
		if($error[2])
		{
			$errormessage .= '- '.$error2.'<br/>';
		}
		if($error[3])
		{
			$errormessage .= '- '.$error3.'<br/>';
			$errorform .= '<input type="text" class="input-group form-control" id="bring4you_errorform_width" placeholder="'.$ewidth.'">';
			$submit = true;
		}
		else
		{
			$errorform .= '<input type="hidden" class="input-group form-control" id="bring4you_errorform_width" value="'.$weight.'">';
		}
		
		if($error[4])
		{
			$errormessage .= '- '.$error4.'<br/>';
			$errorform .= '<input type="text" class="input-group form-control" id="bring4you_errorform_height" placeholder="'.$eheight.'">';
			$submit = true;
		}
		else
		{
			$errorform .= '<input type="hidden" class="input-group form-control" id="bring4you_errorform_height" value="'.$weight.'">';
		}
		
		if($error[5])
		{
			$errormessage .= '- '.$error5.'<br/>';
			$errorform .= '<input type="text" class="input-group form-control" id="bring4you_errorform_depth" placeholder="'.$edepth.'">';
			$submit = true;
		}
		else
		{
			$errorform .= '<input type="hidden" class="input-group form-control" id="bring4you_errorform_depth" value="'.$weight.'">';
		}
		
		if($error[6])
		{
			$errormessage .= '- '.$error6 .'<br/>';
		}
		
		if($error[7])
		{
			$errormessage .= '- '.$error7.'<br/>';
			$errorform .= '<input type="text" class="input-group form-control" id="bring4you_errorform_arrival" placeholder="'.$earrival.'">';
			$submit = true;
		}
		else
		{
			$errorform .= '<input type="hidden" class="input-group form-control" id="bring4you_errorform_arrival" value="'.$arrival.'">';
		}
		
		if($submit)
			$errorform .= '<input class="btn btn-primary" name="b4y__stimate_after_error" id="b4y_estimate_after_error" type="submit" value="'.$esubmit .'"><br/>';
		
		
		$errormessage .= '</div>';
		$errorform .= '</div>';

		echo $errormessage; 
		echo $errorform; 
	}	
	else
	{
		$price = $resultArray['value'];
		
		if($percent != "")
			$price = ($price * (100 + $percent))/100;
		
		if(isset($resultArray['value']) && $price != 0)
			echo $estimationText." : ".round($price).' '.$resultArray['currency'];
		
		if($response == "Unauthorized")
			echo "Il faut saisir une clé API valide dans l'administration";
		
	}
} 


function B4Y_load_menu()
{
	$page_title = 'Bring4You';
	$menu_title = 'Bring4You';
	$capability= 'manage_options'; //An administrator only capability
	$menu_slug = 'bring4you';
	$callback = 'B4Y_load_view';
	$menu_icon = B4Y_PLUGIN_URL . '/views/img/b4y_menu.png';
	$menu_position = 81; //After settings

	add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback, $menu_icon , $menu_position); 
}

function B4Y_allcat ($parent=0) {
  $taxonomy     = 'product_cat';
  $orderby      = 'name';  
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 1;      // 1 for yes, 0 for no  
  $title        = '';  
  $empty        = 0;
  
  $all_categories = array();
  
	if($parent == 0) {
		$args = array(
			 'taxonomy'     => $taxonomy,
			 'orderby'      => $orderby,
			 'show_count'   => $show_count,
			 'pad_counts'   => $pad_counts,
			 'hierarchical' => $hierarchical,
			 'title_li'     => $title,
			 'hide_empty'   => $empty
		);
	}
	else {
		$args = array(
			'taxonomy'     => $taxonomy,
			'child_of'     => 0,
			'parent'       => $parent,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty
		);
	}
  
	$categories = get_categories( $args );
	
	if(count($categories) > 0){
		foreach ($categories as $category) {
			$category_id = $category->cat_ID; 
			$category_name = $category->name;
			
			$subcategories = B4Y_allcat ($category_id);
			
			$all_categories[$category_id] = $category_name;
		}
	}
  
  return $all_categories;
 
}

function B4Y_register_settings() {
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_prod');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_adresse');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_adresse_longitude');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_adresse_latitude');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_texte');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_aide');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_cats');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_comm');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_key');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_sender_name');
	register_setting( 'b4y_plugin_options', 'b4y_plugin_setting_sender_phone');
}


function B4Y_load_view()
{
	if ( !current_user_can( 'activate_plugins' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	include( plugin_dir_path( __FILE__ ) . 'admin/partials/bring4you-admin-display.php');
}

function B4Y_estimation() { 
 
	// Things that you want to do. 
	include_once( plugin_dir_path( __FILE__ ) . 'public/partials/bring4you-public-display.php');
	
	// Output needs to be return
	//return $message;
} 

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bring4you.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function B4Y_run_bring4you() {

	$plugin = new Bring4you();
	$plugin->run();

}
B4Y_run_bring4you();
