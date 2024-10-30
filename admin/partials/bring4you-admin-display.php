<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://bring4you.com/
 * @since      1.0.0
 *
 * @package    Bring4you
 * @subpackage Bring4you/admin/partials
 */

	
	
?>

<div class="panel">
	<h3><i class="dashicons dashicons-admin-home"></i><?php _e('Module d\'envoi de Bring4You', 'b4y'); ?></h3>
	<img src="<?php echo plugins_url('/bring4you/views/img/carrier_image.jpg'); ?>" id="payment-logo" class="pull-right" title="" alt="<?php echo $server; ?>"/>
	<p>
		<strong><?php _e('Ceci est notre nouveau module !', 'b4y'); ?></strong><br />
		<?php _e('Vous pouvez le configurer en suivant le formulaire de configuration', 'b4y'); ?>
	</p>
	<br />
	<p>	
		<?php _e('Ce module va booster vos ventes !', 'b4y'); ?>
	</p>
</div>

<div class="panel">
	<h3><i class="dashicons dashicons-media-document"></i><?php _e('Documentation', 'b4y'); ?></h3>
	<p>
		&raquo; <?php _e('Vous pouvez récupérer la documentation pour configurer ce module :', 'b4y'); ?>
		<ul>
			<li><a href="#" target="_blank"><?php _e('Anglais', 'b4y'); ?></a></li>
			<li><a href="#" target="_blank"><?php _e('Français', 'b4y'); ?></a></li>
		</ul>
		<br/>
		<?php
			echo sprintf(__( 'Pour ajouter Bring4You à votre installation Woocommerce vous devez ajouter la méthode d\'expédition à votre zone d\'expedition %sici%s.', 'b4y' ),'<a href="'.admin_url( 'admin.php?page=wc-settings&tab=shipping&section' ).'">','</a>');
		?>
		<br/>
		<?php _e('Si vous n\'avez pas de clé merci d\'en commander une', 'b4y'); ?>
		<br/><br/>
		<?php
			echo sprintf(__( '%sCommandez votre clé d’API ici%s', 'b4y' ),'<a href="https://buy.stripe.com/28og1P5ZAeW3gKc5ks" class="button-primary">','</a>');
		?>
	</p>
</div>

<form id="module_form" class="defaultForm form-horizontal" action="options.php" method="post" novalidate="">
	<?php settings_fields( 'b4y_plugin_options' ); ?>
    <?php do_settings_sections( 'b4y_plugin_options' ); ?>
	<?php

	$prod = 0;
	$adresse = 'Paris 75000';
	$longitude = 0;
	$latitude = 0;
	$texte = 'Prix de transport';
	$aide = 'Ceci est une aide configurable';
	$commission = 0;
	$cats = array();
	$key = '';
	$name = '';
	$phone = '';
	
	if(get_option('b4y_plugin_setting_adresse') != '')
	{
		$prod = get_option('b4y_plugin_setting_prod');
		$adresse = get_option('b4y_plugin_setting_adresse');
		$longitude = get_option('b4y_plugin_setting_adresse_longitude');
		$latitude = get_option('b4y_plugin_setting_adresse_latitude');
		$texte = get_option('b4y_plugin_setting_texte');
		$aide = get_option('b4y_plugin_setting_aide');
		$commission = get_option('b4y_plugin_setting_comm');
		$key = get_option('b4y_plugin_setting_key');
		$cats = get_option('b4y_plugin_setting_cats');
		$name = get_option('b4y_plugin_setting_sender_name');
		$phone = get_option('b4y_plugin_setting_sender_phone');
	}
	?>
	<input type="hidden" name="submitBring4youModule" value="1">
		<div class="panel" id="fieldset_0">
			<div class="panel-heading">
				<h3><i class="dashicons dashicons-admin-generic"></i> <?php _e('Paramètres', 'b4y'); ?></h3>
			</div>
			
			<div class="form-wrapper">
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Clé API Bring4You', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<input type="text" name="b4y_plugin_setting_key" id="b4y_plugin_setting_key" value="<?php echo $key ?>" class="" style="width:50%">
						<p class="help-block"><?php _e('Saisir la clé API fournie par Bring4You', 'b4y'); ?></p>
					</div>
				</div>
			
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Mode Production', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<span class="switch b4y-switch fixed-width-lg">
							<input type="radio" name="b4y_plugin_setting_prod" id="BRING4YOU_LIVE_MODE_on" value="1" <?php if($prod == 1) { ?>checked="checked"<?php }?>>
								<label for="BRING4YOU_LIVE_MODE_on"><?php _e('Oui', 'b4y'); ?></label>
							<input type="radio" name="b4y_plugin_setting_prod" id="BRING4YOU_LIVE_MODE_off" value="0" <?php if($prod == 0) { ?>checked="checked"<?php }?>>
								<label for="BRING4YOU_LIVE_MODE_off"><?php _e('Non', 'b4y'); ?></label>
							<a class="slide-button btn"></a>
						</span>
																							
						<p class="help-block">
							<?php _e('Utiliser en production', 'b4y'); ?>
						</p>
					</div>
				</div>
						
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Adresse d’enlèvement', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<div class="input-group">
							<input type="text" name="b4y_plugin_setting_adresse" id="b4y_plugin_setting_adresse" value="<?php echo $adresse ?>" style="width:50%">	
							<input type="hidden" name="b4y_plugin_setting_adresse_longitude" id="b4y_plugin_setting_adresse_longitude" value="<?php echo $longitude ?>" >	
							<input type="hidden" name="b4y_plugin_setting_adresse_latitude" id="b4y_plugin_setting_adresse_latitude" value="<?php echo $latitude ?>">	
							<div id="select_box_wrapper">
								<div id="b4y_select_address"></div>
							</div>	
						</div>																																									
						<p class="help-block"><?php _e('Entrez l’adresse de votre entrepôt ou magasin, puis choisissez votre adresse dans la liste', 'b4y'); ?></p>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Nom de l\'expéditeur', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<div class="input-group">
							<input type="text" name="b4y_plugin_setting_sender_name" id="b4y_plugin_setting_sender_name" value="<?php echo $name ?>" class="" style="width:50%">	
						</div>
						<p class="help-block"><?php _e('Ce nom sera envoyé lors de la création de tâche chez Bring4You', 'b4y'); ?></p>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Téléphone de l\'expéditeur', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<div class="input-group">
							<input type="text" name="b4y_plugin_setting_sender_phone" id="b4y_plugin_setting_sender_phone" value="<?php echo $phone ?>" class="" style="width:50%">	
						</div>
						<p class="help-block"><?php _e('Ce téléphone sera envoyé lors de la création de tâche chez Bring4You', 'b4y'); ?></p>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Texte de l\'estimation', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<div class="input-group">
							<input type="text" name="b4y_plugin_setting_texte" id="b4y_plugin_setting_texte" value="<?php echo $texte ?>" class="" style="width:50%">	
						</div>
						<p class="help-block"><?php _e('Ce texte apparaît dans le résultat de l\'estimation', 'b4y'); ?></p>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Aide', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<div class="input-group">
							<input type="text" name="b4y_plugin_setting_aide" id="b4y_plugin_setting_aide" value="<?php echo $aide; ?>" class="" style="width:50%">	
						</div>
						<p class="help-block"><?php _e('Ce texte apparaît quand vous survolez l\'icone ?', 'b4y'); ?></p>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Votre commission sur le transport (%)', 'b4y'); ?></label>
					<div class="b4y-editzone">
						<input type="number" name="b4y_plugin_setting_comm" id="b4y_plugin_setting_comm" min="-30" max="30" value="<?php echo $commission ?>">
						<p class="help-block"><?php _e('Souhaitez-vous prendre une commission sur la livraison ?', 'b4y'); ?></p>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label col-lg-3"><?php _e('Liste des catégories autorisée', 'b4y'); ?></label>
					<div class="input-group">
						<div class="b4y-editzone">
								<?php 
									
									$all_categories = B4Y_allcat(); 
									
								?>
							<select name="b4y_plugin_setting_cats[]" id="b4y_plugin_setting_cats" multiple size="10" style="width: 50%;max-width:50%;">
								<?php
								
									foreach ($all_categories as $key => $cat) {
										$selected = '';
										
										$category_id = $key;   

										$current_level = count(get_ancestors($category_id,'product_cat'));
										
										//var_dump( get_ancestors($category_id, 'product_cat') );
										
										if(in_array($category_id,$cats) || $cats == '')
											$selected = 'selected';
											
										echo '<option value="'.$category_id.'" '.$selected.'>'.str_repeat("—&nbsp;", $current_level).$cat.'</option>';

									}
									
									
								?>
							</select>
							</div>
						</div>
						<p class="help-block"><?php _e('Gardez la touche CTRL ou CMD appuyé pour séléctionner plusieurs catégories', 'b4y'); ?></p>
					</div>
				</div>

			</div><!-- /.form-wrapper -->
		
			<div class="panel-footer">
				<button type="submit" value="1" id="module_form_submit_btn" name="submitBring4youModule" class="btn btn-default btn-b4y pull-right">
					<i class="dashicons dashicons-yes-alt"></i> <?php _e('Sauvegarder', 'b4y'); ?>
				</button>
			</div>	 
			
		</div>
		
		
	</form>
	<script>
		jQuery('#b4y_plugin_setting_adresse').keyup(function () {
			autocomplete_addess_admin();
		});
		
		function autocomplete_addess_admin()
		{
			var adresse = jQuery('#b4y_plugin_setting_adresse').val();
			jQuery.ajax({
				url: "https://pelias.bring4you.com/v1/autocomplete",
				method: "GET",
				dataType: "json",
				data: {
					"text": adresse,
				},
				success: function( data, status, jqxhr ){
					console.log( "Request received:", data );
					var selectHTML = '<select id="b4y_select_address_select" c size="10" style="width: 50%;max-width:50%;">';

					console.log( "Collected:", data.features );
					
					for (var key in data.features)
					{
						var b4y_class = 'b4y_select_address_selected';
						if(adresse == data.features[key].properties.label)
							b4y_class += ' b4y_select_address_selected_on';
						selectHTML +='<option value="'+data.features[key].properties.id+'" data-label="'+data.features[key].properties.label+'" data-long="'+data.features[key].geometry.coordinates[0]+'" data-lat="'+data.features[key].geometry.coordinates[1]+'" class="'+b4y_class+'">'+data.features[key].properties.label+'</option>';
					}
					
					selectHTML +='</select>';
					
					jQuery('#b4y_select_address').html(selectHTML);
				},
				error: function( jqxhr, status, error ){
					console.log(adresse);
				}
			});
		}
		
		jQuery('body').on('click','.b4y_select_address_selected', function () {
				var label = jQuery(this).attr('data-label');
				var longitude = jQuery(this).attr('data-long');
				var latitude = jQuery(this).attr('data-lat');
				
				jQuery('#b4y_plugin_setting_adresse').val(label);
				jQuery('#b4y_plugin_setting_adresse_longitude').val(longitude);
				jQuery('#b4y_plugin_setting_adresse_latitude').val(latitude);
				
				jQuery('#b4y_select_address').html('');
		});
	</script>