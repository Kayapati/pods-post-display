<?php
class Kaya_Post_Grid_View_Widget extends WP_Widget{
	public function __construct(){
		parent::__construct('cpt-grid-post',__('Pods - Post Grid View','ppd'),
			array('description' => __('Use this widget to add  cpt posts as a grid view','ppd'))
		);
	}
	public function child_info($term_info, $bg_color, $color){
		echo '<li  class="cat-'.$term_info->term_id .' parent-'.$term_info->parent.'-'.$term_info->term_id.'" data-id="' . $term_info->term_id . '" data-type="'.$instance['post_type'].'" data-taxonomy="'.$term_info->taxonomy.'">';
		if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){
			$active = ( $_REQUEST['cat_id'] == $term_info->term_id ) ? 'active' : '';
		}
		else{
			$active = '';
		}
		echo '<a class="'.$active.'" href="'.get_permalink().'?post_type='.$term_info->taxonomy.'&cat_id='.$term_info->term_id.'" style="background-color:'.$bg_color.'; color:'.$color.';" data-filter=".cat-' . $term_info->term_id . '">' . ucwords(get_cat_name( $term_info )) . ' </a>';
	}
	public function widget( $args,$instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
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
			'filter_tabs' => 'false',
			'cpt_post_type' => '',
			'filter_tab_all_button_text' => __('ALL', 'talentagency'),
	        'filter_tab_bg_color' => '#e5e5e5',
			'filter_tab_text_color' => '#333333',
			'filter_tab_active_bg_color' => '#ff3333',
			'filter_tab_active_text_color' => '#ffffff',
		));
		echo $args['before_widget'];
			$filter_rand = rand(1,500);
			$css ='.cpt-post-content-wrapper .filter'.$filter_rand.' > ul > li >  a.active { background:'.$instance['filter_tab_active_bg_color'].'!important;  color:'.$instance['filter_tab_active_text_color'].'!important; }';
			    $css .='.cpt-post-content-wrapper .filter'.$filter_rand.' > ul > li > a:hover { background:'.$instance['filter_tab_active_bg_color'].'!important;  color:'.$instance['filter_tab_active_text_color'].'!important; }';

			    $all_tab_text = !empty($instance['filter_tab_all_button_text']) ? $instance['filter_tab_all_button_text'] : '';
			    $css = preg_replace( '/\s+/', ' ', $css ); 
				echo "<style type=\"text/css\">\n" .trim( $css ). "\n</style>";
			$template_file = locate_template( 'pods-grid-view-style.php' );
			$taxonomy_objects = get_object_taxonomies( $instance['cpt_post_type'] );
			echo '<div class="cpt-post-content-wrapper">';
				$array_val = array();
				if( !empty($taxonomy_objects) ){
					foreach ($taxonomy_objects as $key => $taxonomies) {
						$array_val[] = ( !empty( $instance['tax_term'][$taxonomy_objects[$key]] )) ? $instance['tax_term'][$taxonomy_objects[$key]] : '';
					}
				}
				
				$taxonomy_ids = array();
				if( !empty($array_val[0]) ){
					foreach ($array_val as $key => $taxonomy_array_ids) {
						if(  !empty( $taxonomy_array_ids) ){
							$taxonomy_ids = array_merge($taxonomy_ids, array_values($taxonomy_array_ids));
						}
					}
				}else{
						$taxonomy_ids = '';
					}
				//print_r($array_val);
				if( $instance['filter_tabs'] == 'true' ){			
					echo '<div class="filter_tabs">';
						echo '<div class="filter filter'.$filter_rand.'" id="filter" >';
							if( count($instance['cpt_post_type']) > 1 ){
							echo '<ul>';
								if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){ }else{
									$active = 'active';
								}
								if( !empty($all_tab_text) ){
									echo '<li class="all" ><a class="'.$active.'" href="'.get_permalink().'" style="background-color:'.$instance['filter_tab_bg_color'].'; color:'.$instance['filter_tab_text_color'].';"  data-filter="*">'.$all_tab_text.'</a></li>';
								}
								$category = $array_val;
								if( !empty($array_val) ){
									$talent_categories = $array_val;
									foreach ($talent_categories as $key => $cats) {
										for($i=0;$i<count($cats);$i++){
											$terms[] = get_term_by('id', $cats[$i], $taxonomy_objects[$key]);
										} 
									}
								} else {
									foreach ($talent_categories as $key => $cats) {
										$terms[] = get_terms($taxonomy_objects[$key]);
									}
								}
								if( !empty($instance['cpt_post_type']) ){
									$cpt=0;
									foreach ($instance['cpt_post_type'] as $key => $cpt_name) {

										echo '<li class="" ><a class="" href="#" style="background-color:'.$instance['filter_tab_bg_color'].'; color:'.$instance['filter_tab_text_color'].';"  data-filter="*">'.ucwords($cpt_name).'</a>';
											$taxonomy_data = get_object_taxonomies( $cpt_name );
											if( !empty($taxonomy_data) ){
												echo '<ul class="filter_submenu">';
													$i=1;
													$tax_data = array();
													foreach ($taxonomy_data as $key => $taxonomy) {
														$tax_data[] = ( !empty( $instance['tax_term'][$taxonomy_data[$key]] )) ? $instance['tax_term'][$taxonomy_data[$key]] : '';
													}
													$talent_categories1 = $tax_data;
													$terms = array();
													foreach ($talent_categories1 as $key => $cats) {
														for($i=0;$i<count($cats);$i++){
															$terms[] = get_term_by('id', $cats[$i], $taxonomy_data[$key]);
														} 
													}
													foreach($terms as $term) {	
														$term_child = get_term_children($term->term_id, $term->taxonomy);
														//echo $term->parent;
														if ( $term->parent == '0' ) {
														echo '<li  class="cat-'.$term->term_id .'" data-id="' . $term->term_id . '" data-type="'.$instance['post_type'].'" data-taxonomy="'.$term->taxonomy.'">';
															if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){
																$active = ( $_REQUEST['cat_id'] == $term->term_id ) ? 'active' : '';
															}
															else{
																$active = '';
															}
															echo '<a class="'.$active.'" href="'.get_permalink().'?post_type='.$term->taxonomy.'&cat_id='.$term->term_id.'"  data-filter=".cat-' . $term->term_id . '">' . ucwords($term->name) . ' </a>';
															
															//print_r($term_child);
															if( !empty($term_child) ){
																echo '<ul class="filter_submenu">';
																	$cc=0;							
													
																	foreach ($term_child as $key => $child_id) {
																		//echo $term[$cc]->$term_id;
																		//echo $child_id;
																		if( in_array($child_id, $taxonomy_ids)){

																		$term_child_id = get_term_children($child_id, $term->taxonomy, 'OBJECT');
																		$parent_term_info = get_term($child_id, $term->taxonomy, 'OBJECT');
																		//print_r($parent_term_info);
																		//echo $parent_term_info->term_id .'--'.$term->term_id;
																		if( $parent_term_info->parent == $term->term_id  ){
																		echo '<li  class="cat-'.$parent_term_info->term_id .' parent-'.$parent_term_info->parent.'-'.$parent_term_info->term_id.'" data-id="' . $parent_term_info->term_id . '" data-type="'.$instance['post_type'].'" data-taxonomy="'.$parent_term_info->taxonomy.'">';
																		if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){
																			$active = ( $_REQUEST['cat_id'] == $parent_term_info->term_id ) ? 'active' : '';
																		}
																		else{
																			$active = '';
																		}
																		echo '<a class="'.$active.'" href="'.get_permalink().'?post_type='.$parent_term_info->taxonomy.'&cat_id='.$parent_term_info->term_id.'" data-filter=".cat-' . $parent_term_info->term_id . '">' . ucwords(get_cat_name( $child_id )) . ' </a>';

																		$term_child_id = get_term_children($child_id, $term->taxonomy);
																		//print_r($term_child_id);
																		if(!empty($term_child_id)){
																			echo '<ul class="filter_submenu">';
																			foreach ($term_child_id as $key => $terms_sub_child) {
																				if( in_array($terms_sub_child, $taxonomy_ids) ){
																				$this->child_info($terms_sub_child, $instance['filter_tab_bg_color'], $instance['filter_tab_text_color']);
																			}
																			}
																			echo '</ul>';
																		}
																		echo '</li>';
																		}
																		}
																		$cc++;
																	}																	
																echo '</ul>';
															}

														echo '</li>';
														}
													} 
												echo '</ul>';
											}
										echo '</li>';
										$cpt++;
									}
								}
								/*foreach($terms as $term) {
									echo '<li  class="cat-'.$term->term_id .'" data-id="' . $term->term_id . '" data-type="'.$instance['post_type'].'" data-taxonomy="'.$term->taxonomy.'">';
										if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){
											$active = ( $_REQUEST['cat_id'] == $term->term_id ) ? 'active' : '';
										}
										else{
											$active = '';
										}
										echo '<a class="'.$active.'" href="'.get_permalink().'?post_type='.$term->taxonomy.'&cat_id='.$term->term_id.'" style="background-color:'.$instance['filter_tab_bg_color'].'; color:'.$instance['filter_tab_text_color'].';" data-filter=".cat-' . $term->term_id . '">' . $term->name . ' </a>';
									echo '</li>';
								} */
							echo '</ul>';
						}else{
							echo '<ul>';
								if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){ }else{
									$active = 'active';
								}
								if( !empty($all_tab_text) ){
									echo '<li class="all" ><a class="'.$active.'" href="'.get_permalink().'" style="background-color:'.$instance['filter_tab_bg_color'].'; color:'.$instance['filter_tab_text_color'].';"  data-filter="*">'.$all_tab_text.'</a></li>';
								}
								$category = $array_val;
								if( !empty($array_val) ){
									$talent_categories = $array_val;
									foreach ($talent_categories as $key => $cats) {
										for($i=0;$i<count($cats);$i++){
											$terms[] = get_term_by('id', $cats[$i], $taxonomy_objects[$key]);
										} 
									}
								} else {
									if(!empty($talent_categories)){
										foreach ($talent_categories as $key => $cats) {
											$terms[] = get_terms($taxonomy_objects[$key]);
										}
									}else{
										$terms = '';
									}
								}
								if( !empty($terms) ){
								foreach($terms as $term) {
									if( !empty($term) ){
										echo '<li  class="cat-'.$term->term_id .'" data-id="' . $term->term_id . '" data-type="'.$instance['post_type'].'" data-taxonomy="'.$term->taxonomy.'">';
											if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id'])  ){
												$active = ( $_REQUEST['cat_id'] == $term->term_id ) ? 'active' : '';
											}
											else{
												$active = '';
											}
											echo '<a class="'.$active.'" href="'.get_permalink().'?post_type='.$term->taxonomy.'&cat_id='.$term->term_id.'" style="background-color:'.$instance['filter_tab_bg_color'].'; color:'.$instance['filter_tab_text_color'].';" data-filter=".cat-' . $term->term_id . '">' . $term->name . ' </a>';
										echo '</li>';
									}
								}
							}
							echo '</ul>';
						}
						echo '</div>';
			    	echo '</div>';
			    } 
				echo '<ul class="column-extra">';
				global $wp_query, $paged, $post;				
				
				if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
				elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
				else { $paged = 1; }
				if( isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id']) && !empty($_REQUEST['post_type']) && !empty($_REQUEST['cat_id']) && ( $instance['filter_tabs'] == 'true' )  ){
					$the_taxes[] = array (  
					    'taxonomy' => $_REQUEST['post_type'],
					    'field'    => 'term_id',
					    'terms'    => array($_REQUEST['cat_id']),
				  );
				}else{
					foreach ($taxonomy_objects as $tax_data) {
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
					$args1 = array( 'paged' => $paged, 'post_type' => $instance['cpt_post_type'], 'orderby' => $instance['postes_order_by'], 'posts_per_page' =>$instance['display_no_of_postes'],'order' => $instance['grid_postes_order'],  'tax_query' => $the_taxes);
				}else{
					$args1 = array('paged' => $paged, 'taxonomy' => '', 'post_type' => $instance['cpt_post_type'], 'orderby' => $instance['postes_order_by'], 'posts_per_page' => $instance['display_no_of_postes'],'order' => $instance['grid_postes_order'] );
				}
				if( $instance['gray_scale_mode'] == 'on'){
					$class="gray_scale_mode";
				}else{
					$class="";
				}
				query_posts( $args1 );
				if( have_posts() ) :
					while( have_posts() ) : the_post();
						global $terms_id;
						// Seesion Data, storing cpt post short list IDS only
						if(isset($_SESSION['shortlist'])) {
							if ( in_array(get_the_ID(), $_SESSION['shortlist']) ) {
								$selected = 'item_selected';
							}else{
								$selected = '';
							}
						}else{
							$selected = '';
						}
						if(!empty($taxonomy_objects)){
							foreach ($taxonomy_objects as $key => $taxonomy_object) {
								$talent_categories[] = get_terms($taxonomy_objects[$key]);
							}
							
							foreach ($talent_categories as $key => $talent_cats) {							
								$terms_id = array();
								if( is_array($talent_cats) ){
									foreach ($talent_cats as $talent_cat) {
										$terms_id[] = 'cat-'.$talent_cat->term_id;
									}
								}
							}
						}else{
							$terms_id = '';
						}

						echo '<li class="'.$selected.' all '.(!empty($terms_id) ? implode(' ', $terms_id) : '' ).' item column'.$instance['columns'].' '.$class.'" id="'.get_the_ID().'">';
							if( $instance['thumbnail_sizes'] == 'custom_thumb_sizes' ){
								$image_width = !empty($instance['custom_image_size_w']) ? $instance['custom_image_size_w'] : '420';
								$image_height = !empty($instance['custom_image_size_h']) ? $instance['custom_image_size_h'] : '580';
								$image_sizes = array($image_width, $image_height);
							}else{
								$image_sizes = $instance['thumbnail_sizes'];
							}
							
							if( $template_file ){
								include $template_file;
							}else{
								include KAYA_PCV_PLUGIN_PATH.'templates/pods-grid-view-style.php';
							}
						echo '</li>';
						endwhile;
						endif;
						echo '</ul>';
					if( $instance['pagination'] != 'on' ){
						if(function_exists('kaya_pagination')){
						    echo kaya_pagination();
						}
					}
					wp_reset_postdata();
					wp_reset_query();
				echo '</div>';
		echo $args['after_widget'];
	}
	public function form($instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
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
			'post_img_info' =>'',
			'enbale_selected_cpt_fields' => '',
			'columns' => '4',
			'gray_scale_mode' => '',
			'pagination' => '',
			'filter_tabs' => 'false',
			'cpt_post_type' => '',
			'filter_tab_all_button_text' => __('ALL', 'talentagency'),
	        'filter_tab_bg_color' => '#e5e5e5',
			'filter_tab_text_color' => '#333333',
			'filter_tab_active_bg_color' => '#ff3333',
			'filter_tab_active_text_color' => '#ffffff',
		));
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {

				$("#<?php echo $this->get_field_id('filter_tabs') ?>").change(function () {
					$(".<?php echo $this->get_field_id('filter_tab_bg_color'); ?>").hide();
					var selectlayout = $("#<?php echo $this->get_field_id('filter_tabs') ?> option:selected").val(); 
					switch(selectlayout)
					{
					case 'true':
						$(".<?php echo $this->get_field_id('filter_tab_bg_color'); ?>").show();
					break;      
					}
				}).change();


				$("#<?php echo $this->get_field_id('thumbnail_sizes'); ?>").change(function(){
					var $thumbnails_type = $(this).find('option:selected').val();
					$(".<?php echo $this->get_field_id('custom_image_size_w') ?>").hide();
					if( $thumbnails_type == 'custom_thumb_sizes' ){
						$(".<?php echo $this->get_field_id('custom_image_size_w') ?>").show();
					}					
				}).change();

				$('.cpt_post_fields_color_pickr').each(function(){ // Color pickr
					$(this).wpColorPicker();
				});

				$('.pods-cpt-post-wrapper').each(function(){
					var $this = $(this);
					$this.find('#<?php echo $this->get_field_id("cpt_post_type") ?>').change(function(){
						var post_type = $(this).is(':checked');
						if( post_type == false ){
							$this.find('.cpt_post_taxonomy').hide();
							$this.find('.cpt_options_fields').hide();
						}else{
							$this.find('.cpt_post_taxonomy').show();
							$this.find('.cpt_options_fields').show();
						}
					}).change();
				})

			});
		</script>
		<?php
		$cpt_types = kaya_get_pod_cpt_fields();
		
		// POD Taxonomies
		//echo '<p>';
			foreach ($cpt_types as $slug => $options) {
				if(  $options['type'] == 'post_type' ){	
					echo '<div class="pods-cpt-post-wrapper">';
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
							if( $taxonomy != 'post_tag' ){
								echo '<div class="post_type_'.$options['name'].' '.$taxonomy.' cpt_post_taxonomy">';
									echo '<p>';					
										echo '<label>'.$taxonomy_label.'</label><br />';	
										//echo '<select class="widefat" name="'.$this->get_field_name( 'tax_term' ) . '[' . $taxonomy . ']'.'" id="'.$this->get_field_id( 'tax_term-' . $taxonomy ).'">';
										foreach ($terms as $key => $tname) {
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
		//echo '</p>'; 
		?>
		<p>
			<label for="<?php echo $this->get_field_id('filter_tabs') ?>"> <?php _e('Filter Tabs','ppd') ?> </label>
			<select  class="widefat" id="<?php echo $this->get_field_id('filter_tabs') ?>" name="<?php echo $this->get_field_name('filter_tabs') ?>">
				<option value="false" <?php selected('false', $instance['filter_tabs']) ?>> <?php esc_html_e('False', 'ppd') ?>  </option>
				<option value="true" <?php selected('true', $instance['filter_tabs']) ?>>   <?php esc_html_e('True', 'ppd') ?>  </option>
			</select>
		</p> 
		<div class="<?php  echo $this->get_field_id('filter_tab_bg_color'); ?>">
		 
		<p>
			<label for="<?php echo $this->get_field_id('filter_tab_all_button_text') ?>">  <?php _e('Filter Tab ALL Button Text Change','ppd')?>  </label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('filter_tab_all_button_text') ?>" value="<?php echo esc_attr($instance['filter_tab_all_button_text']) ?>" name="<?php echo $this->get_field_name('filter_tab_all_button_text') ?>" />
		</p>
		<p  id="<?php echo $this->get_field_id('filter_tab_bg_color'); ?>">
		  <label for="<?php echo $this->get_field_id('filter_tab_bg_color'); ?>"><?php _e('Filter Tab BG Color', 'ppd') ?></label>
		  <input type="text" name="<?php echo $this->get_field_name('filter_tab_bg_color') ?>" id="<?php echo $this->get_field_id('filter_tab_bg_color') ?>" class="widefat cpt_post_fields_color_pickr" value="<?php echo $instance['filter_tab_bg_color'] ?>" />
		</p>
		<p  class="" id="<?php echo $this->get_field_id('filter_tab_text_color'); ?>">
		  <label for="<?php echo $this->get_field_id('filter_tab_text_color'); ?>"><?php _e('Filter Tab Text Color','ppd') ?></label>
		  <input type="text" name="<?php echo $this->get_field_name('filter_tab_text_color') ?>" id="<?php echo $this->get_field_id('filter_tab_text_color') ?>" class="cpt_post_fields_color_pickr" value="<?php echo $instance['filter_tab_text_color'] ?>" />
		</p>				
		<p class="" id="<?php echo $this->get_field_id('filter_tab_active_bg_color'); ?>" style="clear:both;">
		  <label for="<?php echo $this->get_field_id('filter_tab_active_bg_color'); ?>"><?php _e('Filter Tab Acive BG Color','ppd') ?></label>
		  <input type="text" name="<?php echo $this->get_field_name('filter_tab_active_bg_color') ?>" id="<?php echo $this->get_field_id('filter_tab_active_bg_color') ?>" class="cpt_post_fields_color_pickr" value="<?php echo $instance['filter_tab_active_bg_color'] ?>" />
		</p>
		<p class="" id="<?php echo $this->get_field_id('filter_tab_active_text_color'); ?>" >
		  <label for="<?php echo $this->get_field_id('filter_tab_active_text_color'); ?>"><?php _e('Filter Tab Active Text Color','ppd') ?></label>
		  <input type="text" name="<?php echo $this->get_field_name('filter_tab_active_text_color') ?>" id="<?php echo $this->get_field_id('filter_tab_active_text_color') ?>" class="cpt_post_fields_color_pickr" value="<?php echo $instance['filter_tab_active_text_color'] ?>" />
		</p>
		</div>
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
		    <label for="<?php echo $this->get_field_id('disable_post_content') ?>">  <?php _e('Disable post Content','ppd') ?>  </label>
		      <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("disable_post_content"); ?>" name="<?php echo $this->get_field_name("disable_post_content"); ?>"<?php checked( (bool) $instance["disable_post_content"], true ); ?> />
		</p>
		<p>
		  <label for="<?php echo $this->get_field_id('gray_scale_mode') ?>">  <?php _e('Enable Gray Scale Images','ppd')?>  </label>
		  <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("gray_scale_mode"); ?>" name="<?php echo $this->get_field_name("gray_scale_mode"); ?>"<?php checked( (bool) $instance["gray_scale_mode"], true ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('display_no_of_postes') ?>">  <?php _e('Display Number Of Postes','ppd') ?>  </label>
			<input type="text"  class="widefat" id="<?php echo $this->get_field_id('display_no_of_postes') ?>" value="<?php echo esc_attr($instance['display_no_of_postes']) ?>" name="<?php echo $this->get_field_name('display_no_of_postes') ?>" />
		</p>
		<p class="one_fourth">
			  <label for="<?php echo $this->get_field_id('pagination') ?>">  <?php _e('Disable Pagination', 'ppd') ?>  </label>
			  <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("pagination"); ?>" name="<?php echo $this->get_field_name("pagination"); ?>"<?php checked( (bool) $instance["pagination"], true ); ?> />
			</p>
		<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Kaya_Post_Grid_View_Widget");'));
?>