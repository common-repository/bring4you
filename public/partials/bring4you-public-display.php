<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://bring4you.com/
 * @since      1.0.0
 *
 * @package    Bring4you
 * @subpackage Bring4you/public/partials
 */
 
global $product;   
if(isset($product))
{
	$weight = $product->get_weight();
	$width = $product->get_width();
	$height = $product->get_height();
	$depth = $product->get_length();
}
$prod = get_option('b4y_plugin_setting_prod');
$adresse = get_option('b4y_plugin_setting_adresse');
$texte = get_option('b4y_plugin_setting_texte');
$aide = get_option('b4y_plugin_setting_aide');
$commission = get_option('b4y_plugin_setting_comm');

$estimationok = true;

if(empty($weight) || empty($width) || empty($height) || empty($depth))
	$estimationok = false;
	
if($prod == 1 && $estimationok)
{
?>

<div class="bring4you_product_tab">
	<h3><?php _e('Estimez vos frais de port avec Bring4You','b4y'); ?> <a href="#" title="<?php echo $aide; ?>" onclick="return false;"><i class="dashicons dashicons-editor-help"></i></a></h3>
	<div id="bring4you_form_estimate">
		<div class="input-wrapper">
			<input type="text" name="bring4you_city" id="bring4you_city" value="" class="" placeholder="<?php echo $adresse; ?>" />
		</div>
		<input class="btn btn-primary float-xs-right hidden-xs-down" name="b4y_estimate" id="b4y_estimate" type="submit" value="Estimer" />
		<input type="hidden" id="bring4you_weight" value="<?php echo $weight; ?>" />
		<input type="hidden" id="bring4you_width" value="<?php echo $width; ?>" />
		<input type="hidden" id="bring4you_height" value="<?php echo $height; ?>" />
		<input type="hidden" id="bring4you_depth" value="<?php echo $depth; ?>" />
		<input type="hidden" id="bring4you_citydeparture" value="<?php echo $adresse; ?>" />
		<input type="hidden" id="bring4you_estimationtext" value="<?php echo $texte; ?>" />
		<input type="hidden" id="bring4you_percentage" value="<?php echo $commission; ?>" />
		<input type="hidden" id="bring4you_language" value="" />
		
	</div>
	<div id="bring4you_estimation"></div>
</div>

<?php
}
elseif($prod == 1 && !$estimationok)
{
	?>

	<h2><?php _e('Estimation Bring4You non disponible','b4y'); ?></h2>

	<?php
	if(empty($weight))
		echo '<p>'._e('Le poids n\'est pas renseigné','b4y').'</p>';
	if(empty($width))
		echo '<p>'._e('La largeur n\'est pas renseignée','b4y').'</p>';
	if(empty($height))
		echo '<p>'._e('La hauteur n\'est pas renseignée','b4y').'</p>';
	if(empty($depth))
		echo '<p>'._e('La longueur n\'est pas renseignée','b4y').'</p>';
}
?>