<?php
class Kaya_Pods_Advanced_Search_Widget extends WP_Widget{
	public function __construct(){
		parent::__construct('pods-advanced-search',__('Pods advanced search filter','ppd'),
			array('description' => __('Use this widget to create simple and advanced search filters.','ppd'))
		);
	}
	public function widget( $args,$instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'enbale_selected_cpt_fields' => '',
			'cpt_post_type' => '',
			'tax_term' => '',
			'select_range_field_name' => '',
			'search_button_text' => 'Search',
			'change_select_talent_text' => 'Select One',
			'enable_ajax_search' => '',
			'ajax_search_class_name' => 'mid-content',
		));
		echo $args['before_widget'];
			if( class_exists('PodsField_Pick') ){
			     $pods_fields = new PodsField_Pick;
			     $pods_countries = $pods_fields->data_countries();
			     $pods_us_states = $pods_fields->data_us_states();
			}else{
			    $pods_countries ='';
			    $pods_us_states = '';
			}
			$enable_ajax_search = ( $instance['enable_ajax_search'] == 'on' ) ? 'on' : 'off';
			echo '<div id="'.$args['widget_id'].'" class="advanced_search_wrapper" data-ajax="'.$enable_ajax_search	.'" data-class="'.$instance['ajax_search_class_name'].'">';
			if( count($instance['cpt_post_type']) > 1){
				echo '<p>';
					echo '<label>'.$instance['change_select_talent_text'].'</label>';
					echo '<select id="pods_cpt_data">';
						foreach ($instance['cpt_post_type'] as $key => $post_type) {				
							if(!empty($post_type)){					
								echo '<option value="'.$post_type.'">'.str_replace('_', ' ', ucwords($post_type)).'</option>';
							}
						}
					echo '</select>';
					echo '<input type="hidden" value="" id="pods_cpt_name" />'; 
				echo '</p>';

			}else{
				echo '<input type="hidden" value="" id="pods_cpt_name" data-cpt_name="'.$instance['cpt_post_type'][0].'" value="'.$instance['cpt_post_type'][0].'"/>'; 
			}
			foreach ($instance['cpt_post_type'] as $key => $post_type) {

				$taxonomy_objects = get_object_taxonomies( $post_type, 'objects' );
				if(!empty($post_type)){
				$pods = pods( $post_type );
				$pods_fields_data_enable = (array) $instance['enbale_selected_cpt_fields']; 
				if( !empty($instance['enbale_selected_cpt_fields']) ){
					echo '<div class="advanced_search_forms pods_'.$post_type.'_fields">';
						echo '<form method="get" class="searchbox-wrapper pods-cpt-search-form pods_'.$post_type.'_fields_info s"  action="'.home_url().'">';
							echo '<input type="hidden" name="advance_search" value="advance_search">
								<input type="hidden"  name="s" placeholder="Name" />';
							echo '<input type="hidden"  name="post_type" value="'.$post_type.'" />';
							if( !empty($instance['select_range_field_name'][$post_type]) ){								
								foreach ($instance['select_range_field_name'][$post_type] as $key => $compare_data) {
									echo '<input type="hidden"  name="'.$compare_data.'_range" value="true">';
								}
							}
							
						//print_r($taxonomy_objects);
						if( !empty($instance['tax_term']) ){		
							foreach ($taxonomy_objects as $key => $taxonomy_data) {
								echo '<div class="checkbox_wrapper search_fields pods_cpt_categories">';
									$taxonomy = $taxonomy_data->name;
									$taxonomy_label = $taxonomy_data->label;								
									if( in_array($taxonomy, $instance['tax_term'])){
										$terms = get_terms( array(
											'taxonomy' => $taxonomy,
											'hide_empty' => false,
										) );
										echo '<label>'.$taxonomy_label.'</label>';
										foreach ($terms as $key => $terms_data) { ?>
											<label><input type="checkbox" value="<?php echo $terms_data->term_id; ?>" class="checkbox"  name="<?php echo 'pods_'.$taxonomy; ?>[]" /><?php echo trim( ucfirst( $terms_data->name) );
											echo '</label>';
										}
									}
								echo '</div>';	
							}
							
						}

						$i=0;
						if( !empty( $pods_fields_data_enable[$post_type]) ){
						foreach ($pods_fields_data_enable[$post_type] as $key => $cpt_fields) {
							$fields_info = $pods->fields($cpt_fields);
						$pods_cpt = !empty($fields_info['pod']) ? $fields_info['pod'] : '';
						if( $pods_cpt ==  $post_type ){
							if( $fields_info['type'] == 'text' ){
								echo '<div class="search_fields search_field_'.$fields_info['name'].' '.$post_type.' '.$fields_info['pod'].' search_field'.$i.'">';
									echo '<label>'.$fields_info['label'].'</label>';
									echo '<input type="text" name="'.$fields_info['name'].'" />';
								echo '</div>'; 

							}elseif(( $fields_info['type'] == 'number' ) || ( $fields_info['type'] == 'date' ) ){
								$slider_rand = rand(1,25000);
								if( ($fields_info['options']['number_format_type'] == 'slider' ) || ( $fields_info['name'] == 'age' )){
										if( $fields_info['name'] == 'age' ){
											$range_step = '1';
											$range_min = '1';
											$range_max = '80';
										}else{
											$range_step =  $fields_info['options']['number_step'];
											$range_min =  $fields_info['options']['number_min'];
											$range_max =  $fields_info['options']['number_max'];											
										}
								?>
									<div class="pods_ui_slider_range search_fields search_field_<?php echo $fields_info['name'] . ' ' .$post_type.' '.$fields_info['pod'].' search_field'.$i; ?>">
									 	<script>
											 jQuery( function() {
											    jQuery( "#slider-range-<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>_<?php echo $slider_rand; ?>" ).slider({
													range: true,
													min:<?php echo $range_min; ?>,
													max: <?php echo $range_max; ?>,
													values: [ <?php echo  $range_min; ?>, <?php echo  $range_max; ?> ],
													step:<?php echo  $range_step ?>,
													slide: function( event, ui ) {
														var attname = jQuery(this).data('name');
														jQuery( "#"+attname+"-min" ).val(ui.values[ 0 ]);
														jQuery( "#"+attname+"-max" ).val(ui.values[ 1 ]);
														jQuery( "span."+attname+"-min" ).text(ui.values[ 0 ]);
														jQuery( "span."+attname+"-max" ).text(ui.values[ 1 ]);
													}
											    });
											} );
										</script>
										  	<?php echo '<label>'.$fields_info['label'].'</label>'; ?>
											<input type="hidden" class="small-text" id="<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>-min" value="<?php echo $range_min; ?>"  name="<?php echo $fields_info['name']; ?>-min" value="">
											<input type="hidden" class="small-text" id="<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>-max" value="<?php echo $range_max; ?>" name="<?php echo $fields_info['name']; ?>-max" value="">
											<span class="<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>-min label_min"> <?php echo $range_min; ?> </span>
											<span class="<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>-max label_max"> <?php echo $range_max; ?> </span>												 
											<div id="slider-range-<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>_<?php echo $slider_rand; ?>" data-name="<?php echo $fields_info['pod'].'_'.$fields_info['name']; ?>"></div>
										</div>	
									<?php									
								}

							}elseif($fields_info['type'] == 'pick'){
								if( $fields_info['options']['pick_format_type'] == 'single' ){
									if($fields_info['pick_object'] == 'us_state'){
										if( !empty($instance['select_range_field_name'][$post_type]) ){
											foreach ($instance['select_range_field_name'][$post_type] as $key => $compare_data) {
											if( $compare_data == $fields_info['name'] ){									
												echo '<div class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.' ">';
													echo '<label>'.$fields_info['label'].'</label>';
													echo '<select class="column2" name="'.$fields_info['name'].'-from">';
														echo '<option value="">--'.__('Select', 'ppd').'--</option>';
														foreach ($pods_us_states as $key => $options) {
															if( !empty($options) ){
																echo '<option value="'.$key.'">'.$options.'</option>';
															}
														}
													echo '</select>';
													echo '<select class="column2" name="'.$fields_info['name'].'-top">';
														echo '<option value="">--'.__('Select', 'ppd').'--</option>';
														foreach ($pods_us_states as $key => $options) {
															if( !empty($options) ){
																echo '<option value="'.$key.'">'.$options.'</option>';
															}
														}
													echo '</select>';
												echo '</div>'; 
												}else{
													echo '<div class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
														echo '<label>'.$fields_info['label'].'</label>';
														echo '<select name="'.$fields_info['name'].'">';
															echo '<option value="">--'.__('Select', 'ppd').'--</option>';
															foreach ($pods_us_states as $key => $options) {
																if( !empty($options) ){
																	echo '<option value="'.$key.'">'.$options.'</option>';
																}
															}
														echo '</select>';
													echo '</div>';
												}
											}
										}else{
											echo '<div class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
												echo '<label>'.$fields_info['label'].'</label>';
												echo '<select name="'.$fields_info['name'].'">';
													echo '<option value="">--'.__('Select', 'ppd').'--</option>';
													foreach ($pods_us_states as $key => $options) {
														if( !empty($options) ){
															echo '<option value="'.$key.'">'.$options.'</option>';
														}
													}
												echo '</select>';
											echo '</div>';
										}										
									}else{
										$select_list_data = explode("\n", str_replace(array("\r\n","\n\r","\r"),"\n",$fields_info['options']['pick_custom']) );
										if( !empty($instance['select_range_field_name'][$post_type]) ){
											foreach ($instance['select_range_field_name'][$post_type] as $key => $compare_data) {
												if( $compare_data == $fields_info['name'] ){
													echo '<div  class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
														echo '<label>'.$fields_info['label'].'</label>';
														echo '<select class="column2" name="'.$fields_info['name'].'-from">';
															echo '<option value="">--'.__('Select', 'ppd').'--</option>';
															foreach ($select_list_data as $key => $options) {
																if( !empty($options) ){
																	echo '<option value="'.$options.'">'.$options.'</option>';
																}
															}
														echo '</select>';
														echo '<select class="column2" name="'.$fields_info['name'].'-to">';

															echo '<option value="">--'.__('Select', 'ppd').'--</option>';
															foreach ($select_list_data as $key => $options) {
																if( !empty($options) ){
																	echo '<option value="'.$options.'">'.$options.'</option>';
																}
															}
														echo '</select>';
													echo '</div>';
												}else{
													echo '<div  class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
												echo '<label>'.$fields_info['label'].'</label>';
												echo '<select name="'.$fields_info['name'].'">';
													echo '<option value="">--'.__('Select', 'ppd').'--</option>';
													foreach ($select_list_data as $key => $options) {
														if( !empty($options) ){
															echo '<option value="'.$options.'">'.$options.'</option>';
														}
													}
												echo '</select>';
											echo '</div>';	
												}
											}
										}else{
											echo '<div  class="search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
												echo '<label>'.$fields_info['label'].'</label>';
												echo '<select name="'.$fields_info['name'].'">';
													echo '<option value="">--'.__('Select', 'ppd').'--</option>';
													foreach ($select_list_data as $key => $options) {
														if( !empty($options) ){
															echo '<option value="'.$options.'">'.$options.'</option>';
														}
													}
												echo '</select>';
											echo '</div>';	
										}
									}
								} // Multi Select
								elseif( $fields_info['options']['pick_format_type'] == 'multi' ){
									$select_list_data = explode("\n", str_replace(array("\r\n","\n\r","\r"),"\n",$fields_info['options']['pick_custom']) );
									echo '<div class="checkbox_wrapper search_fields search_field_'.$fields_info['name'].' search_field'.$i.'">';
										echo '<label>'.$fields_info['label'].'</label>';
											foreach ($select_list_data as $key => $options) {
												if( !empty($options) ){
													echo ' <label><input type="checkbox" name="'.$fields_info['name'].'[]" value="'.$options.'" />'.$options.'</label>';
												}
											}
										echo '</select>';
									echo '</div>'; 
								}
							}else{

							}
						$i++;	
						}
					}
					}
						echo '<input type="submit" value="'.( !empty($instance['search_button_text']) ? $instance['search_button_text'] : __('Search', 'ppd') ).'" name="sumit_button" class="search_data_submit" />';
						echo '</form>';
					echo '</div>';	
				}
			}
		}
		echo '</div>';
		echo $args['after_widget'];
	}
	public function form($instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'enbale_selected_cpt_fields' => '',
			'cpt_post_type' => '',
			'tax_term' => '',
			'select_range_field_name' => '',
			'search_button_text' => 'Search',
			'change_select_talent_text' => __('Select Talent', 'cpt'),
			'enable_ajax_search' => '',
			'ajax_search_class_name' => 'mid-content',
		));
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
			$('.pods-cpt-post-wrapper').each(function(){
					var $this = $(this);
					$this.find('#<?php echo $this->get_field_id("cpt_post_type") ?>').change(function(){
						var post_type = $(this).is(':checked');
						if( post_type == false ){
							$this.find('.cpt_post_taxonomy').hide();
							$this.find('.cpt_options_fields').hide();
							$this.find('.compare_fields_data').hide();							
						}else{
							$this.find('.cpt_post_taxonomy').show();
							$this.find('.cpt_options_fields').show();
							$this.find('.compare_fields_data').show();

						}
					}).change();
				});
			});
		</script>
		<?php
		$cpt_types = kaya_get_pod_cpt_fields();
		
		// POD Taxonomies
		//echo '<p>';
			foreach ($cpt_types as $slug => $options) {
				if( $options['type'] == 'post_type' ){	
					echo '<div class="pods-cpt-post-wrapper adv-search-cpt-fields">';
					echo '<p>';					
						echo '<label>'.__('CPT Post Type', 'ppd').'</label><br />';
						echo '<label>';
						//echo '<option value="'.$tname->slug.'" '.$selected.'>'.trim( ucfirst( $tname->name) ).'</option>'; 
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
						$taxonomy_label = $cat->label;				
						$terms = get_terms( array(
						'taxonomy' => $taxonomy,
						'hide_empty' => false,
						) );				
						if( !empty($taxonomy ) ){
							echo '<div class="post_type_'.$options['name'].' '.$taxonomy.' cpt_post_taxonomy">';
								
								echo '<p>';
								echo '<label style="font-weight:bold; display:block;">'.__('CPT  Taxonomies','ppd').'</label>';
								if( !empty($instance['tax_term']) ){
									if( in_array($taxonomy, $instance['tax_term']) ){
										$checked = 'checked';
									}else{
										$checked = '';
									}
								}else{
									$checked = '';
								}
								 ?>
									<input type="checkbox" value="<?php echo $taxonomy; ?>" class="checkbox" <?php echo $checked; ?> id="<?php echo $this->get_field_id("tax_term"); ?>" name="<?php echo $this->get_field_name("tax_term"). '[]'; ?>" />
										<?php echo '<label>'.$taxonomy_label.'</label>';
								echo '</p>';
							echo '</div>';
						}
					}

					// Cpt meta options
					echo '<p>';
						$pods_fields_data_enable = (array) $instance['enbale_selected_cpt_fields']; 
				 	   	echo '<div class="cpt_options_fields post_type_'.$options['name'].'" id="cpt_'.$options['name'].'">';
				       		echo '<label>'.$options['label'].' '.__('CPT Fields', 'ppd').'</label><br />';
				          	foreach ($options['fields'] as $fields_key => $field_val) {		                
				                echo '<label>';                                                        
				                   if( !empty($instance['enbale_selected_cpt_fields']) ){
				                      $checked = in_array($fields_key,  $pods_fields_data_enable[$options['name']]) ? 'checked' : '';
				                   }else{
				                      $checked ='';
				                   }
				                   if( ($options['fields'][$fields_key]['type'] != 'file') && ($options['fields'][$fields_key]['type'] != 'wysiwyg')  && ($options['fields'][$fields_key]['type'] != 'email')  ){   ?>
				                      <input type="checkbox" class="" id="<?php echo $this->get_field_id('enbale_selected_cpt_fields'); ?>"  value="<?php echo trim($fields_key) ?>" name="<?php echo $this->get_field_name('enbale_selected_cpt_fields').'['.$options['name'].']'; ?>[]" <?php echo $checked; ?> />
				                      <?php echo $field_val['label']; 
				                   }        
				                echo '</label>';			             
				          	}                                                   
			       		echo '</div>';
			     	echo '</p>'; 
			   
			    echo '<p>';
				 	   	echo '<div class="compare_fields_data">';
				       		echo '<label>'.__('Enable Select Fields Compare Settings', 'ppd').'</label><br />';
				          	foreach ($options['fields'] as $fields_key => $field_val) {
				          		//print_r($options['fields'][$fields_key]['type']);	
				          		
				          		if($field_val['type'] == 'pick' ){
				          			if( $field_val['options']['pick_format_type'] == 'single' ){   
				          				  $select_range_field_name = (array) $instance['select_range_field_name'];
				          				 echo '<label>';                                                        
						                   if( !empty($select_range_field_name) ){
						                      $checked = in_array($fields_key,  $select_range_field_name[$options['name']]) ? 'checked' : '';
						                   }else{
						                      $checked ='';
						                   }  ?>
						                   <input type="checkbox" class="" id="<?php echo $this->get_field_id('select_range_field_name'); ?>"  value="<?php echo trim($fields_key) ?>" name="<?php echo $this->get_field_name('select_range_field_name').'['.$options['name'].']'; ?>[]" <?php echo $checked; ?> />
						                      <?php echo $field_val['label']; 
						                echo '</label>';
						            }
				              	}		             
				          	}                                                   
			       		echo '</div>';
			     	echo '</p>'; 
			     	 echo '</div>'; 
			}
			} ?>
			<p class="one_fourth">
			  <label for="<?php echo $this->get_field_id('enable_ajax_search') ?>">  <?php _e('Enable Ajax Search', 'ppd') ?>  </label>
			  <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("enable_ajax_search"); ?>" name="<?php echo $this->get_field_name("enable_ajax_search"); ?>"<?php checked( (bool) $instance["enable_ajax_search"], true ); ?> />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ajax_search_class_name'); ?>"><?php _e("Add class name for display ajax searh results data", 'ppd'); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id('ajax_search_class_name'); ?>"  value="<?php echo $instance['ajax_search_class_name'] ?>" name="<?php echo $this->get_field_name('ajax_search_class_name'); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('change_select_talent_text'); ?>"><?php _e("Change 'Select Talent' Text", 'ppd'); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id('change_select_talent_text'); ?>"  value="<?php echo $instance['change_select_talent_text'] ?>" name="<?php echo $this->get_field_name('change_select_talent_text'); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('search_button_text'); ?>"><?php _e('Search Button Text', 'ppd'); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id('search_button_text'); ?>"  value="<?php echo $instance['search_button_text'] ?>" name="<?php echo $this->get_field_name('search_button_text'); ?>" />
			</p>
		<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Kaya_Pods_Advanced_Search_Widget");'));
?>