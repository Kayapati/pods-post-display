<?php
/**
 * This loop data included "CPT Taxonomy & Shortlist " pages only
 * If you want to customize this loop data
 * Copy this file and add your theme root folder with same name(loop-content.php)
 */

global $kaya_options, $taxonomy_cpt_name, $kaya_shortlist_options;
$cpt_slug_name = kaya_get_post_type(); // cpt slug name
if( !is_author() && !is_tax() ){
	$columns = isset($kaya_shortlist_options['shortlist_display_columns']) ? $kaya_shortlist_options['shortlist_display_columns'] : '4'; 
}elseif(is_tax()){
	$columns =  !empty($kaya_options->taxonomy_columns) ? $kaya_options->taxonomy_columns : '4'; // this columns working on cpt taxonomy page only
}else{
	$columns = '3';
}
// Featured Image Sizes
$image_cropping_type = !empty($kaya_options->choose_image_sizes) ? $kaya_options->choose_image_sizes : 'wp_image_sizes';
if( $image_cropping_type == 'wp_image_sizes' ){
	$image_sizes = !empty($kaya_options->choose_image_sizes) ? $kaya_options->choose_image_sizes : 'full';
}else{
	$image_size_width = !empty($kaya_options->taxonomy_gallery_width) ? $kaya_options->taxonomy_gallery_width : '380';
	$image_size_height = !empty($kaya_options->taxonomy_gallery_height) ? $kaya_options->taxonomy_gallery_height : '600';
	$image_sizes = array( $image_size_width, $image_size_height );
}		
// Session for shortlist data
if(isset($_SESSION['shortlist'])) {
	if ( in_array(get_the_ID(), $_SESSION['shortlist']) ) {
		$selected = 'item_selected';
	}
}else{
	$selected = '';
}
$img_url = wp_get_attachment_url(get_post_thumbnail_id()); // Featured Image URL
echo '<li class="column'.$columns.' '.$selected.' item" id="'.get_the_ID().'">';
	
	// check this function to enabled shortlist icons or not
	if( !empty($kaya_shortlist_options['enable_cpt_shortlist']) ){
		if( in_array($cpt_slug_name, $kaya_shortlist_options['enable_cpt_shortlist']) ){ 
			do_action('kaya_cpt_post_shortlist_icons'); // Shortlist Icons
		}
	}
	
	// post featured image with permalink
	echo '<a href="'.get_the_permalink().'">';
		echo kaya_pod_featured_img( $image_sizes, $image_cropping_type ); // Featured Image
	echo '</a>';
	// End

	// Cpt Meta fields information wrapper
	echo '<div class="cpt_post_meta_info">'; 
		echo '<h4>'.get_the_title().'</h4>'; // post title section
		if( function_exists('kaya_general_info_section') ){
			kaya_general_info_section($cpt_slug_name);  // Cpt post meta data information
		}
	echo '</div>';
	// End Cpt Meta fields information wrapper

echo '</li>';
?>