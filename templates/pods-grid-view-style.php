<?php
/**
 * This loop data included "CPT Post Grid View & CPT Post Slider " widgets only
 * If you want to customize this loop data
 * Copy this file and add your theme root folder with same name(widget-loop.php)
 */
global $kaya_shortlist_options;
$current_item_type = get_post_type( $post->ID );
// post featured images with link
echo '<a href="'.get_the_permalink().'">';
	$img_url = get_the_post_thumbnail_url();
	kaya_pod_featured_img($image_sizes, $instance['thumbnail_sizes']);
echo '</a>';

// check this function to enabled shortlist icons or not
if( !empty($kaya_shortlist_options['enable_cpt_shortlist']) ){
	if( in_array($instance['post_type'], $kaya_shortlist_options['enable_cpt_shortlist']) ){
		do_action('kaya_cpt_post_shortlist_icons'); // Shortlis Icons
	}
}

// start Post content wrapper
if( $current_item_type == $cpt_name ){
	echo '<div class="description">';
		$option_fields = kaya_get_cpt_fields($instance['post_type']);
		//if( $instance['disable_post_title'] != 'on' ){
			echo '<h4><a href="'.get_the_permalink().'">'.get_the_title().'</a></h4>';
		//}
		// Post meta information
		if( !empty($instance['enbale_selected_cpt_fields'][$current_item_type]) ){
			echo '<div class="post-meta-info-wrapper">';
				echo '<ul>';
				foreach ($instance['enbale_selected_cpt_fields'][$current_item_type] as $key => $fields_data) {
					$meta_data = get_post_meta(get_the_ID(), $fields_data, true);
					if( !empty($meta_data) ){
						if( $option_fields[$fields_data]['name'] ==  'age' ){
							if( kaya_age_calculate($meta_data) == '0' ){
								$age = '<1';
							}else{
								$age = kaya_age_calculate($meta_data);
							}
							echo '<li><strong>'.$option_fields[$fields_data]['label'].':</strong> '.$age.'</li>';
						}else{
							echo '<li><strong>'.$option_fields[$fields_data]['label'].':</strong> '.$meta_data.'</li>';
						}
					}
				}
				echo '</ul>';
			echo '</div>';
		}
	} // End

	// post description limit words
	if( $instance['disable_post_content'] != 'on' ){
		echo '<p>'.wp_trim_words( get_the_content(), $instance['post_content_limit'], null ).'</p>';
	}

echo '</div>'; // End Post content wrapper
?>