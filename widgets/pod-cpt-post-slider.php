<?php
class Kaya_Post_Slider_Widget extends WP_Widget{
	public function __construct(){
		parent::__construct('cpt-slider-post',__('Pods - Post Slider','ppd'),
			array('description' => __('Use this widget to add  cpt posts as a slider view','ppd'))
		);
	}
	public function widget( $args,$instance){
		$instance = wp_parse_args($instance,array(
			'cpt_post_type' => '',
			'grid_postes_order' => '',
			'display_no_of_postes' => '',
			'postes_order_by' => '',
			'custom_image_size_w' => '',
			'custom_image_size_h' => '',
			'thumbnail_sizes' => '',
			'tax_term' => '',
			'disable_post_thumbnail' => '',
			'disable_post_content' => '',
			'post_content_limit' => '20',
			'post_img_info' => '',
			'enbale_selected_cpt_fields' => '',
			'columns' => '',
			'gray_scale_mode' => '',
			'pagination' => '',
			'nav_buttons' => '',
		));
		echo $args['before_widget'];
			echo '<div class="cpt-post-content-wrapper">';
				echo '<div class="owl-carousel" data-columns="'.$instance['columns'].'" data-nav="'.$instance['nav_buttons'].'">';
				global $wp_query, $paged, $post;
				$taxonomy_objects = get_object_taxonomies( $instance['cpt_post_type'] );
				$tax_data = !empty($taxonomy_objects[0]) ? $taxonomy_objects[0] : '';
				$array_val = ( !empty( $instance['tax_term'])) ? $instance['tax_term'] : '';

				if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
				elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
				else { $paged = 1; }


				foreach ($taxonomy_objects as $tax_data) {
					if( $tax_data != 'post_tag' ){
						if( isset($instance['tax_term'][$tax_data]) ){	
						    $the_taxes[] = array (  
						        'taxonomy' => $tax_data,
						        'field'    => 'term_id',
						        'terms'    => $instance['tax_term'][$tax_data],
						    );
						}
					}
				}
				$the_taxes['relation'] = 'OR';
				if( $array_val ) {
					$args1 = array( 'paged' => $paged, 'post_type' => $instance['cpt_post_type'], 'orderby' => $instance['postes_order_by'], 'posts_per_page' =>-1,'order' => $instance['grid_postes_order'],  'tax_query' => $the_taxes );
				}else{
					$args1 = array( 'paged' => $paged, 'taxonomy' => $tax_data, 'post_type' => $instance['cpt_post_type'], 'orderby' => $instance['postes_order_by'], 'posts_per_page' => -1,'order' => $instance['grid_postes_order'] );
				}				
				query_posts($args1);
				if( $instance['gray_scale_mode'] == 'on'){
					$class="gray_scale_mode";
				}else{
					$class="";
				}
				if( have_posts() ) :
					while( have_posts() ) : the_post();
						if(isset($_SESSION['shortlist'])) {
							if ( in_array(get_the_ID(), $_SESSION['shortlist']) ) {
								$selected = 'item_selected';
							}else{
								$selected = '';
							}
						}else{
							$selected = '';
						}
						echo '<div class="item '.$class.' '.$selected.'" id="'.get_the_ID().'">';
							if( $instance['thumbnail_sizes'] == 'custom_thumb_sizes' ){
								$image_width = !empty($instance['custom_image_size_w']) ? $instance['custom_image_size_w'] : '420';
								$image_height = !empty($instance['custom_image_size_h']) ? $instance['custom_image_size_h'] : '580';
								$image_sizes = array($image_width, $image_height);
							}else{
								$image_sizes = $instance['thumbnail_sizes'];
							}
							$template_file = locate_template( 'pods-grid-view-style.php' );
							if( $template_file ){
								include $template_file;
							}else{
								include KAYA_PCV_PLUGIN_PATH.'templates/pods-grid-view-style.php';
							}

						echo '</div>';
						endwhile;
						endif;
						echo '</div>';
					echo '</div>';
					if( $instance['pagination'] == 'on' ){
						if(function_exists('kaya_pagination')){
						    echo kaya_pagination();
						}
					}
					wp_reset_postdata();
					wp_reset_query();
				//echo '</div>';
		echo $args['after_widget'];
	}
	public function form($instance){
		$instance = wp_parse_args($instance,array(
			'cpt_post_type' => '',
			'grid_postes_order' => '',
			'display_no_of_postes' => '',
			'postes_order_by' => '',
			'custom_image_size_w' => '',
			'custom_image_size_h' => '',
			'thumbnail_sizes' => '',
			'tax_term' => '',
			'disable_post_thumbnail' => '',
			'disable_post_title' => '',
			'disable_post_title' => '',
			'disable_post_content' => '',
			'post_content_limit' => '20',
			'post_img_info' =>'',
			'enbale_selected_cpt_fields' => '',
			'columns' => '4',
			'gray_scale_mode' => '',
			'pagination' => '',
			'nav_buttons' => '',
		));
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("#<?php echo $this->get_field_id('thumbnail_sizes'); ?>").change(function(){
					var $thumbnails_type = $(this).find('option:selected').val();
					$(".<?php echo $this->get_field_id('custom_image_size_w') ?>").hide();
					if( $thumbnails_type == 'custom_thumb_sizes' ){
						$(".<?php echo $this->get_field_id('custom_image_size_w') ?>").show();
					}
					
				}).change();

			});
		</script>
		<?php
		$cpt_types = kaya_get_pod_cpt_fields();
		
		// Getting CPT's Names
		foreach ($cpt_types as $slug => $options) {
				if( $options['type'] == 'post_type' ){	
					echo '<div class="pods-cpt-post-wrapper">';
					echo '<p>';					
						echo '<label>'.__('CPT Post Type', 'ppd').'</label><br />';	
						echo '<label>';
						if( !empty( $instance['cpt_post_type']) ){
							$checked = (in_array($options['name'], $instance['cpt_post_type']) ) ? 'checked' : ''; 
						}else{
							$checked = '';
						} ?>
						<input type="checkbox" value="<?php echo  $options['name']; ?>" class="checkbox" id="<?php echo $this->get_field_id("cpt_post_type"); ?>" name="<?php echo $this->get_field_name("cpt_post_type"). '[]'; ?>"<?php echo $checked; ?> /><?php echo  $options['label']; ?>
						<?php echo '</label>';
					echo '</p>';

					$get_taxonomy = get_object_taxonomies($options['name'],  'objects' );

					
					foreach ($get_taxonomy as $key => $cat) {
						$taxonomy = $cat->name;
						$taxonomy_label =$cat->label;				
						$terms = get_terms( array(
							'taxonomy' => $taxonomy,
							'hide_empty' => false,
						) );		
						if( !empty($taxonomy ) ){
							if( $taxonomy != 'post_tag' ){
								echo '<div class="post_type_'.$options['name'].' '.$taxonomy.' cpt_post_taxonomy">';
									echo '<p>';					
										echo '<label>'.$taxonomy_label.'</label><br />';	
										//echo '<select class="widefat" name="'.$this->get_field_name( 'tax_term' ) . '[' . $taxonomy . ']'.'" id="'.$this->get_field_id( 'tax_term-' . $taxonomy ).'">';
										foreach($terms as $key => $tname){
										echo '<label>';
										//echo '<option value="'.$tname->slug.'" '.$selected.'>'.trim( ucfirst( $tname->name) ).'</option>'; 
										if( !empty( $instance['tax_term'][$tname->taxonomy]) ){
											$checked = (in_array($tname->term_id, $instance['tax_term'][$tname->taxonomy]) ) ? 'checked' : ''; 
										}else{
											$checked = '';
										}
										?>
										<input type="checkbox" value="<?php echo $tname->term_id; ?>" class="checkbox" id="<?php echo $this->get_field_id("tax_term-".$taxonomy); ?>" name="<?php echo $this->get_field_name("tax_term"). '[' . $taxonomy . '][]'; ?>"<?php echo $checked; ?> /><?php echo trim( ucfirst( $tname->name) ); ?>
										<?php echo '</label>';
										}
									echo '</p>';
								echo '</div>';
							}
						}
					}

					// Cpt meta options
					echo '<p>';
				 	   	echo '<div class="cpt_options_fields post_type_'.$options['name'].'" id="cpt_'.$options['name'].'">';
				       		echo '<label>'.$options['label'].' '.__('CPT Fields', 'ppd').'</label><br />';
				          	foreach ($options['fields'] as $fields_key => $field_val) {		                
				                echo '<label>';                                                        
				                   if( !empty($instance['enbale_selected_cpt_fields'][$options['name']]) ){
				                      $checked = in_array($fields_key,  $instance['enbale_selected_cpt_fields'][$options['name']]) ? 'checked' : '';
				                   }else{
				                      $checked ='';
				                   }
				                   if( $options['fields'][$fields_key]['type'] != 'file' ){   ?>
				                      <input type="checkbox" class="" id="<?php echo $this->get_field_id('enbale_selected_cpt_fields'); ?>"  value="<?php echo trim($fields_key) ?>" name="<?php echo $this->get_field_name('enbale_selected_cpt_fields'). '[' . $options['name'] . ']'; ?>[]" <?php echo $checked; ?> />
				                      <?php echo $field_val['label']; 
				                   }        
				                echo '</label>';			             
				          	}                                                   
			       		echo '</div>';
			     	echo '</p>'; 
			    echo '</div>'; 	
				}
			}	

		?>
		<p>
			<label for="<?php echo $this->get_field_id('columns') ?>"> <?php _e('Columns','ppd') ?> </label>
			<select class="widefat" id="<?php echo $this->get_field_id('columns') ?>" name="<?php echo $this->get_field_name('columns') ?>">
				<option value="8" <?php selected('8', $instance['columns']) ?>>8</option>
				<option value="7" <?php selected('7', $instance['columns']) ?>>7</option>
				<option value="6" <?php selected('6', $instance['columns']) ?>>6</option>
				<option value="5" <?php selected('5', $instance['columns']) ?>>5</option>
				<option value="4" <?php selected('4', $instance['columns']) ?>>4</option>
				<option value="3" <?php selected('3', $instance['columns']) ?>>3</option>
				<option value="2" <?php selected('2', $instance['columns']) ?>>2</option>
				<option value="1" <?php selected('1', $instance['columns']) ?>>1</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('postes_order_by') ?>"> <?php _e('Order By','ppd') ?> </label>
			<select class="widefat" id="<?php echo $this->get_field_id('postes_order_by') ?>" name="<?php echo $this->get_field_name('postes_order_by') ?>">
				<option value="date" <?php selected('date', $instance['postes_order_by']) ?>>   <?php esc_html_e('Date','ppd') ?>  </option>
				<option value="title" <?php selected('title', $instance['postes_order_by']) ?>>   <?php esc_html_e('Title', 'ppd') ?>  </option>
				<option value="rand" <?php selected('rand', $instance['postes_order_by']) ?>>   <?php esc_html_e('Random', 'ppd') ?>  </option>
				<option value="name" <?php selected('name', $instance['postes_order_by']) ?>>   <?php esc_html_e('Name', 'ppd') ?>  </option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('grid_postes_order') ?>"> <?php _e('Order','ppd') ?> </label>
			<select  class="widefat" id="<?php echo $this->get_field_id('grid_postes_order') ?>" name="<?php echo $this->get_field_name('grid_postes_order') ?>">
				<option value="desc" <?php selected('desc', $instance['grid_postes_order']) ?>>   <?php esc_html_e('Descending Order', 'ppd') ?>  </option>
				<option value="asc" <?php selected('asc', $instance['grid_postes_order']) ?>>   <?php esc_html_e('Ascending Order', 'ppd') ?>  </option>
			</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('thumbnail_sizes') ?>"> <?php _e('Wordpress Post Thumbnails  Default / Custom Sizes','ppd') ?> </label>
		<?php
		$wp_defaults = array( 'thumbnail', 'medium', 'medium_large', 'large' );
		echo '<select class="widefat" id="'.$this->get_field_id('thumbnail_sizes') .'" name="'.$this->get_field_name('thumbnail_sizes').'">';
		foreach ($wp_defaults as $key => $default_size) {
			echo '<option value="'.$default_size.'" '.selected($default_size, $instance['thumbnail_sizes']).' >'.ucwords(str_replace('_', ' ', $default_size)).'</option>';
		}
			echo '<option value="custom_thumb_sizes" '.selected('custom_thumb_sizes', $instance['thumbnail_sizes']).' >'.__('Custom Thumb Sizes', 'ppd').'</option>';
		echo '</select>';
		?>
		</p>
		<div class="<?php echo $this->get_field_id('custom_image_size_w') ?>">
			<label for="<?php echo $this->get_field_id('custom_image_size_w') ?>">  <?php _e('Width (px)','ppd') ?>  </label>
			<input type="text"  class="widefat" id="<?php echo $this->get_field_id('custom_image_size_w') ?>" value="<?php echo esc_attr($instance['custom_image_size_w']) ?>" name="<?php echo $this->get_field_name('custom_image_size_w') ?>" />

			<label for="<?php echo $this->get_field_id('custom_image_size_h') ?>">  <?php _e('Height (px)','ppd') ?>  </label>
			<input type="text"  class="widefat" id="<?php echo $this->get_field_id('custom_image_size_h') ?>" value="<?php echo esc_attr($instance['custom_image_size_h']) ?>" name="<?php echo $this->get_field_name('custom_image_size_h') ?>" />
		</div>
		<p>
		    <label for="<?php echo $this->get_field_id('post_content_limit') ?>">  <?php _e('Post Content Limit','ppd')?>  </label>
		    <input type="text" name="<?php echo $this->get_field_name('post_content_limit') ?>" id="<?php echo $this->get_field_id('post_content_limit') ?>" class="widefat" value="<?php echo $instance['post_content_limit'] ?>" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('disable_post_thumbnail') ?>">  <?php _e('Disable post Thumbnail','ppd') ?>  </label>
		      <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("disable_post_thumbnail"); ?>" name="<?php echo $this->get_field_name("disable_post_thumbnail"); ?>"<?php checked( (bool) $instance["disable_post_thumbnail"], true ); ?> />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('disable_post_title') ?>">  <?php _e('Disable post Title','ppd') ?>  </label>
		      <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("disable_post_title"); ?>" name="<?php echo $this->get_field_name("disable_post_title"); ?>"<?php checked( (bool) $instance["disable_post_title"], true ); ?> />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id('disable_post_content') ?>">  <?php _e('Disable post Content','ppd') ?>  </label>
		      <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("disable_post_content"); ?>" name="<?php echo $this->get_field_name("disable_post_content"); ?>"<?php checked( (bool) $instance["disable_post_content"], true ); ?> />
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id('gray_scale_mode') ?>">  <?php _e('Enable Gray Scale Images','pvc')?>  </label>
		  <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("gray_scale_mode"); ?>" name="<?php echo $this->get_field_name("gray_scale_mode"); ?>"<?php checked( (bool) $instance["gray_scale_mode"], true ); ?> />
		</p>
		<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Kaya_Post_Slider_Widget");'));
?>