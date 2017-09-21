<?php
add_action( 'pre_get_posts', 'search_query',1000);
function search_query($query){
	 		if($query->is_search()){
	 			global $kaya_options;
				$limit = !empty($kaya_options->post_limit) ? $kaya_options->post_limit : '-1';
	 			$advance_search =  isset( $_GET['advance_search']  ) ? $_GET['advance_search'] : '';
	 			$post_type =  isset( $_GET['post_type']  ) ? $_GET['post_type'] : '';
	 			$post_type = str_replace('pods_', '', $post_type);
	 			$cpt_meta_fields = kaya_get_cpt_fields($post_type);
	 			$taxonomy_objects = get_object_taxonomies( $post_type );
	 			if( !empty($taxonomy_objects) ){
		 			foreach ($taxonomy_objects as $key => $pods_taxonomy) {
		 				//echo $_GET[$taxonomy];
		 				$taxonomy = 'pods_'.$pods_taxonomy;
		 				if( !empty($_GET[$taxonomy]) ){
			 				$tax_query[] = array(
								'taxonomy' => $pods_taxonomy,
								'field' => 'id',
								'terms' =>  $_GET[$taxonomy],
							);
		 				}
		 			}
	 			}
	 			
	 			$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => $limit,
					'tax_query' => $tax_query,
				);
				foreach ($cpt_meta_fields as $key => $field_id) {	

						if( $field_id['type'] == 'pick' ){
							//print_r( $field_id['name']);
							if( $field_id['options']['pick_format_type'] == 'single' ){								
								if( !empty( $_GET[$field_id['name'].'_range'] ) &&  ( $_GET[$field_id['name'].'_range'] == 'true' ) ){
									if( !empty($_GET[$field_id['name'].'-from']) ){
										$args['meta_query'][] = array(
											'key' => $field_id['name'],
											'value' => array( $_GET[$field_id['name'].'-from'], $_GET[$field_id['name'].'-to'] ),
											'compare' => 'BETWEEN',
											'type' => 'numeric',
										);	
									}									
								}else{
									if( !empty($_GET[$field_id['name']]) ){
											$args['meta_query'][] = array(
												'key' => $field_id['name'],
												'value' => $_GET[$field_id['name']],
											);
										}
								}
							}elseif( $field_id['options']['pick_format_type'] == 'multi' ){
									if( !empty($_GET[$field_id['name']]) ){
											$args['meta_query'][] = array(
												'key' => $field_id['name'],
												'value' => $_GET[$field_id['name']],
												'compare' => 'IN',
											);
										}

							}else{
								if(!empty($_GET[$field_id['name']])){
									$args['meta_query'][] = array(
										'key' => $field_id['name'],
										'value' => $_GET[$field_id['name']],
										'compare' => 'LIKE',
									);
								}
							}
						}elseif( $field_id['options']['pick_format_type'] == 'multi' ){
								//echo  strip_tags( $field_id['name']);
								if( !empty($_GET[$field_id['name']]) ){
									$args['meta_query'][] = array(
										'key' => $field_id['name'],
										'value' => $_GET[$field_id['name']],
										'compare' => 'IN',
									);
								}

						}elseif( $field_id['type'] == 'text' ){
							if( !empty($_GET[$field_id['name']] ) ){
								$args['meta_query'][] = array(
									'key' => $field_id['name'],
									'value' => $_GET[$field_id['name']],
									'compare' => 'LIKE'	
								);	
							}
						}elseif( $field_id['type'] == 'number' ){
							if( !empty($_GET[$field_id['name'].'-min']) ){
								$args['meta_query'][] = array(
									'key' => $field_id['name'],
									'value' => array( $_GET[$field_id['name'].'-min'], $_GET[$field_id['name'].'-max'] ),
									'type' => 'numeric',
									'compare' => 'BETWEEN'	
								);	
							}
						}elseif( $field_id['type'] == 'date' ){
							if( $field_id['name'] == 'age' ){
								if( !empty($_GET[$field_id['name'].'-min']) ){
									$args['meta_query'][] = array(
										'key' =>'age_filter',
										'value' => array( $_GET[$field_id['name'].'-min'], $_GET[$field_id['name'].'-max'] ),
										'type' => 'numeric',
										'compare' => 'BETWEEN'	
									);	
								}	
							}else{
								
							}
						}else{
							if( !empty($_GET[$field_id['name']]) ){
									$args['meta_query'][] = array(
										'key' => $field_id['name'],
										'value' => $_GET[$field_id['name']],
										'compare' => 'LIKE',
									);
								}
						}
					//}
				}
				foreach($args as $k => $v){
		         		$query->set( $k, $v );
				}
				return $query; 
	 		}
	 	}

function ajx_search_query(){
	global $paged, $wp_query, $kaya_options;
	$limit = !empty($kaya_options->post_limit) ? $kaya_options->post_limit : '-1';
	$search_data = $_POST['search_data'];
	parse_str($search_data);
	$taxonomy_objects = get_object_taxonomies( $post_type );
	if( !empty($taxonomy_objects) ){
		foreach ($taxonomy_objects as $key => $pods_taxonomy) {
			//echo $_GET[$taxonomy];
			$taxonomy = 'pods_'.$pods_taxonomy;
			if( !empty(${$taxonomy}) ){
				$tax_query[] = array(
				'taxonomy' => $pods_taxonomy,
				'field' => 'id',
				'terms' =>  ${$taxonomy},
			);
			}
		}
	}
	if (get_query_var('paged'))
{
	$paged = get_query_var('paged');
}
elseif (get_query_var('page'))
{
	$paged = get_query_var('page');
}
else
{
	$paged = 1;
}
	$args = array(
		'paged' => !empty($_POST['paged']) ? $_POST['paged'] : '1',
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => $limit, 
		'tax_query' => $tax_query,
	);
	$cpt_meta_fields = kaya_get_cpt_fields($post_type);
	foreach ($cpt_meta_fields as $key => $field_id) {
		if(  $field_id['type'] == 'text'){
			if( !empty(${$field_id['name']}) ){
				$args['meta_query'][] = array(
					'key' => $field_id['name'],
					'value' => ${$field_id['name']},
					'compare' => 'LIKE'	
				);
			}
		}elseif( $field_id['type'] == 'date' ){
			if( $field_id['name'] == 'age' ){
				if( !empty( ${$field_id['name'].'-min'} ) ){
					$args['meta_query'][] = array(
						'key' =>'age_filter',
						'value' => array( ${$field_id['name'].'-min'} ,${$field_id['name'].'-max'} ),
						'type' => 'numeric',
						'compare' => 'BETWEEN'	
					);	
				}	
			}else{
				
			}
		}
		elseif( $field_id['type'] == 'number' ){
			if( !empty( ${$field_id['name'].'-min'} ) ){
				$args['meta_query'][] = array(
					'key' => $field_id['name'],
					'value' => array( ${$field_id['name'].'-min'} ,${$field_id['name'].'-max'} ),
					'type' => 'numeric',
					'compare' => 'BETWEEN'	
				);	
			}
		}elseif( $field_id['type'] == 'pick' ){
			if( $field_id['options']['pick_format_type'] == 'single' ){						
				if( !empty( ${$field_id['name'].'_range'} ) &&  ( ${$field_id['name'].'_range'} == 'true' )  ){
					if( !empty(${$field_id['name'].'-from'}) ){
						$args['meta_query'][] = array(
							'key' => $field_id['name'],
							'value' => array( ${$field_id['name'].'-from'}, ${$field_id['name'].'-to'} ),
							'compare' => 'BETWEEN',
							'type' => 'numeric',
						);	
					}
				}else{
					if( !empty(${$field_id['name']}) ){
							$args['meta_query'][] = array(
								'key' => $field_id['name'],
								'value' => ${$field_id['name']},
							);
						}
				}
			}elseif( $field_id['options']['pick_format_type'] == 'multi' ){
				if( !empty(${$field_id['name']}) ){
					$args['meta_query'][] = array(
						'key' => $field_id['name'],
						'value' => ${$field_id['name']},
						'compare' => 'IN',
					);
				}
			}
		}				//}
	}

	query_posts($args);
	 echo '<h1>'.__('Search Results :', '' ).'</h1>';
	  echo '<div class="search-content-wrapper kaya-post-content-wrapper" data-div_id="'.$_POST['div_id'].'">';
			echo '<ul class="column-extra">';
	if ( have_posts() ) {
	   while ( have_posts() ) {
	   		the_post();
	   		global $post;
	   		kaya_get_template_part( 'pods-taxonomy-view-style' );
	   }
	}else{
		echo '<h4>'.__('Nothing Found', 'ppd').'</h4>';
	}
	echo '</ul>';
	echo kaya_pagination();
	wp_reset_query();
		wp_reset_postdata(); 
		echo '</div>';
	die();
	
}
add_action('wp_ajax_ajx_search_query', 'ajx_search_query');
add_action('wp_ajax_nopriv_ajx_search_query', 'ajx_search_query');

	?>