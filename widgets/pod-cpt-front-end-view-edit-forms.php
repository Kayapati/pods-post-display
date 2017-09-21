<?php
class Kaya_View_Edit_Cpt_Form_Widget extends WP_Widget{
	public function __construct(){
		parent::__construct('cpt-front-end-view-edit-forms',__('Pods - Frontend posts list view','ppd'),
			array('description' => __('Use this widget to add  cpt posts in Front End as a list view.','ppd'))
		);
	}
	public function pods_data_save($pods_id, $field_id, $post_id, $related_pod_id, $related_field_id, $related_id, $related_weight){
		global $wpdb;
		$wpdb->query( $wpdb->prepare(				 "
			INSERT INTO `wp_podsrel`
			    (
			        `pod_id`,
			        `field_id`,
			        `item_id`,
			        `related_pod_id`,
			        `related_field_id`,
			        `related_item_id`,
			        `weight`
			    )
			VALUES ( %d, %d, %d, %d, %d, %d, %d )
			", array(
			$pods_id,
			$field_id,
			$post_id,
			$related_pod_id,
			$related_field_id,
			$related_id,
			$related_weight
		)
		) );
	}
	public function get_pods_data($pod_id, $field_id, $item_id){
		global $wpdb, $related_item_ids;
		$related_data_ids = $wpdb->get_results("SELECT related_item_id FROM wp_podsrel WHERE pod_id= $pod_id AND field_id=$field_id AND item_id = $item_id ");
		foreach ($related_data_ids as $related_data_id) { 
		    $related_item_ids[$related_data_id->related_item_id] = $related_data_id->related_item_id;
		}
		return $related_item_ids;
	}
	public function widget( $args,$instance){
	global $pods_success_msg;

		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'disable_pod_cpt_fields' => '',
			'thank_you' =>'',
			'submit_button_text' => __('Save Changes', 'ppd'),
			'post_delete_msg' => __('Your post has been deleted', 'ppd'),
			'form_update_message' => __( 'Form Updated successfully', 'ppd' ),
			'post_limit_exceeded_msg' => __("You Don't Have Permissions to create post", 'ppd'),
		));
		echo $args['before_widget'];
		if( !function_exists('pods_api') ){
				return false;
			}
			
		$page_slug = get_post($instance['thank_you']);
		if ( !is_user_logged_in() ){ // when user not logged in return false
			echo '<p class="kaya-error-message">'.(!empty($kaya_settings['non_logged_users_msg']) ? stripslashes($kaya_settings['non_logged_users_msg']) : __('Please Login', 'ppd')).'</p>';
			return;
		}
		wp_enqueue_style( 'pods-form' );
		
		if( isset($_REQUEST['action']) && ( $_REQUEST['action'] == 'edit' ) ){ // Post Edit absed on post ID
			$post_id = $_REQUEST['id'];
			$cpt_types = kaya_get_cpt_fields($instance['post_type']);
			foreach ($cpt_types as $key => $options) {
					$cpt_meta_opts[$key] = $key;
			}
			$pods_success_msg = !empty($instance['form_update_message']) ? $instance['form_update_message'] : __( 'Form Updated successfully', 'ppd' );
			$pod = pods($instance['post_type'], $post_id);
			$page_slug = get_post($instance['thank_you']);
			$post_meta_opts = !empty($instance['disable_pod_cpt_fields'][$instance['post_type']]) ? array_diff($cpt_meta_opts, $instance['disable_pod_cpt_fields'][$instance['post_type']]) : $cpt_meta_opts;
			$fields_data = !empty($post_meta_opts) ? (', '.implode(', ',$post_meta_opts)) : '';
			$thank_you = !empty($instance['thank_you']) ? "thank_you='".$page_slug->post_name."'" : '';
			$pods_options = kaya_get_cpt_fields($instance['post_type']);
			$pods_cpt_taxonomies = get_object_taxonomies($instance['post_type']);
			$pods_taxonomy_info = get_object_taxonomies($instance['post_type'],  'objects' );
			echo '<div class="pods_cpt_form_wrapper">';				// Submitting the post data
					if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['post_action'] ) && !empty( $_POST['post_action'] ) && $_POST['post_action'] == 'user_edit_pods_cpt_profile' ){
						$pods_cpt_categories = $_POST['pods_cpt_categories'] ? $_POST['pods_cpt_categories'] : '';
						$post_status = ( get_post_status ( $post_id ) == 'publish' ) ? 'publish' : $pod->api->pod_data['options']['default_status'];	 
						$update_post = array('ID' => $post_id,  'post_status' => $post_status,  'post_title' =>  wp_strip_all_tags( $_POST['pods-cpt-post-title'] ));
						wp_update_post( $update_post );
						if( $_FILES['upload_pods_post_featured_img'] ){
				          pods_get_featured_image_id('upload_pods_post_featured_img', $post_id);
				        }
				        // Attach Pods Cpt post categories
						if( !empty($pods_cpt_taxonomies) ){
							foreach ($pods_cpt_taxonomies as $key => $pods_taxonomies) {
								if( !empty($pods_taxonomies) ){
									wp_set_object_terms( $post_id, $pods_cpt_categories, $pods_taxonomies);
								}
							}							
						}
						foreach ($pods_options as $key => $pods_keys) {
							if($pods_keys['type'] == 'file'){
								global $wpdb;
								$fields_data = $pod->fields ($pods_keys['name']);
								if( !empty($_POST[$pods_keys['name']]) ){
									
									$pods_file_data = array_keys($_POST[$pods_keys['name']]);
									$related_data = array_combine( $pods_file_data, $pods_file_data );
									pods_update_form_post_meta($post_id, '_pods_'.$pods_keys['name'], array_combine($pods_file_data, $pods_file_data));

									$pod_related_data = $this->get_pods_data($fields_data['pod_id'], $fields_data['id'], $post_id);
									$new_vals = $related_data;
									$pod_id= $fields_data['pod_id'];
									$field_id = $fields_data['id'];
									if( array_diff($pod_related_data,$new_vals) === array_diff($new_vals, $pod_related_data) ){
										$pods_array_data = $new_vals;
									}else{
										$pods_array_data = array_diff($pod_related_data,$new_vals);
									}
									foreach ($pods_array_data as $key => $rel_item_id) {
										$wpdb->query( "DELETE FROM wp_podsrel WHERE  pod_id= $pod_id AND field_id=$field_id AND item_id = $post_id AND related_item_id = $rel_item_id" );
									}
									foreach ( array_combine( $pods_file_data, $pods_file_data ) as $key => $image_id) {
										if( !empty($image_id) ){
											$this->pods_data_save($fields_data['pod_id'], $fields_data['id'], $post_id, '0', '0', $image_id, $fields_data['pod_id'] );
										}
									}
								}
							}
							else{
								pods_update_form_post_meta($post_id, $pods_keys['name'], $_POST[$pods_keys['name']]);
							}
						}
						 wp_redirect('?action=edit&id='.$post_id.'&updated=true');
												
					} // End Here
					if( isset($_REQUEST['updated']) && ($_REQUEST['updated'] == 'true') ){ // Success Results
						echo '<p class="kaya-success-message">'.$pods_success_msg.'</p>';
					}
					// Form Start Here
					echo '<form action="" method="post" class="pods-submittable pods-form pods-form-front" enctype="multipart/form-data">';
						echo '<div class="pods-submittable-fields">';
							echo '<ul class="pods-form-fields">';
								echo '<li>';  // Post title
									echo '<div class="pods-field-label">';
										echo '<label class="pods-form-ui-label pods-form-ui-label-pods-field-post-title" for="pods-form-ui-pods-field-post-title">'.__('Enter Post Title (required)', 'ppd') .'</label>';
									echo '</div>';
									echo '<div class="pods-field-input">';
									 ?>
										<input type="text" id ="pods-cpt-post-title" required placeholder="" name="pods-cpt-post-title" value="<?php echo get_the_title($post_id); ?>" />
									<?php
									echo '</div>';
								echo '</li>'; 
								echo '<li>'; // Post Featured Image
									echo '<div class="pods-field-label">';
										echo '<label class="pods-form-ui-label" for="">'.__('Featured Image', 'ppd').'</label>';
									echo '</div>';
									echo '<div class="pods-field-input">';
										$featured_image = wp_get_attachment_url( get_post_thumbnail_id($post_id) );
						                if( !empty($featured_image) ){ 
						                    echo '<img src="'.kaya_image_sizes( $featured_image, 100,100, 't' ).'" />'; 
						                }	 ?>
										<input type="file" id="upload_pods_post_featured_img" name="upload_pods_post_featured_img" >
									<?php 
									echo '</div>';
								echo '</li>';
								if(count($pods_cpt_taxonomies) > 0)  // Cpt Post Taxonomies list
								{	
									foreach ($pods_cpt_taxonomies as $key => $pods_cpt_taxonomy) {
										$pods_cpt_terms = get_terms($pods_cpt_taxonomy, array('hide_empty' => false) );
										if( !empty($pods_cpt_terms) ){
											$terms = wp_get_object_terms($post_id, $pods_cpt_taxonomy);
										    $category_data = array();
										    foreach($terms as $term)
										    {
										        $category_data[] = $term->slug;
										    }
											echo '<li>';
												echo '<ul>';
													echo '<div class="pods-field-label">
														<label class="pods-form-ui-label" for="">'.trim($pods_taxonomy_info[$pods_cpt_taxonomy]->label).'</label>
													</div>';
													echo '<div class="pods-field-input">';
														echo '<div class="pods-pick-values pods-pick-checkbox">';
															foreach ($pods_cpt_terms as $key => $pods_cpt_term) {
																if( !empty($pods_cpt_term) ){
																	$checked = in_array($pods_cpt_term->slug, $category_data) ? 'checked' : '';
																	echo '<li class="pods-field"><div class="class="pods-pick-values pods-pick-checkbox"">';
																		echo '<input name="pods_cpt_categories[]" data-name-clean=""  id="pods_cpt_categories" class="pods_cpt_categories" type="checkbox" '. $checked .' value="'.trim($pods_cpt_term->slug).'">';
																		echo '<label class="pods-form-ui-label">'.trim($pods_cpt_term->name).'</label>';
																	echo '</div></li>';
																}
															}
														echo '</div>';
													echo '</div>';
												echo '</ul>';
											echo '</li>';
										}
									}					 
								}
								foreach ($pods_options as $slug => $fields_info) {
									
									$field_prefix = '';				
									echo '<li class="pods-field pods-form-ui-row-type-text pods-form-ui-row-name-'.str_replace('_', '-', $fields_info['name']).'">';
										if($fields_info['pick_object'] != 'taxonomy'){
											echo '<div class="pods-field-label">';
												echo PodsForm::label( $field_prefix . $fields_info[ 'name' ], $fields_info[ 'label' ], $fields_info[ 'help' ], $fields_info );
											echo '</div>';
										}
										echo '<div class="pods-field-input">';	
											echo PodsForm::field( $field_prefix . $fields_info[ 'name' ], $pod->field( array( 'name' => $fields_info[ 'name' ], 'in_form' => true ) ), $fields_info[ 'type' ], $fields_info, $pod, $pod->id() );									
											echo PodsForm::comment( $field_prefix . $fields_info[ 'name' ], null, $fields_info );
										echo '</div>';
									echo '</li>';
								}
							echo '</ul>';
						echo '</div>';	?>
						<input id="pods-cpt-submit-button" class="" type="submit" value="<?php echo $instance['submit_button_text'] ? $instance['submit_button_text'] : __('Save Changes', 'ppd'); ?>" />
		            	<input type="hidden" name="post_action" value="user_edit_pods_cpt_profile" />
		            	<input type="hidden" name="empty-description" id="empty-description" value="1"/>
		            	<?php wp_nonce_field( 'user-edit-pods-cpt-page' );
					echo '</form>'; // Form End	


			echo '</div>';
		}elseif( isset($_REQUEST['action']) && ( $_REQUEST['action'] == 'delete' ) ){ // Post Edit absed on post ID
			echo '<p class="kaya-success-message">';
				echo !empty($instance['post_delete_msg']) ? $instance['post_delete_msg'] : __('Your post has been deleted', 'ppd');
			echo '</p>';
			wp_delete_post($_REQUEST['id']);
			$this->pods_cpt_post_list($instance['post_type']);
		}else{
			$this->pods_cpt_post_list($instance['post_type']);
		//}else{

		} // End Else
		echo $args['after_widget'];
	}
	function pods_cpt_post_list($post_type){
		global $current_user;
		$current_user_data =  wp_get_current_user($current_user->ID);
		$edit_post = isset( $current_user_data->allcaps['publish_'.$post_type.'s'] ) ? 'true' : 'false';
		$edit_published = isset( $current_user_data->allcaps['edit_published_'.$post_type.'s'] ) ? 'true' : 'false';
		$delete_published = isset( $current_user_data->allcaps['delete_published_'.$post_type.'s'] ) ? 'true' : 'false';

		echo '<table class="kaya-table list-table-content-wrapper">';
				echo '<tr>';
					echo '<th>'.__('Title','ppd').'</th>';
					echo '<th>'.__('Category','ppd').'</th>';
					echo '<th>'.__('Date','ppd').'</th>';
					echo '<th>'.__('Post Image','ppd').'</th>';
					echo '<th>'.__('Action','ppd').'</th>';
					echo '<th>'.__('Status','ppd').'</th>';
				echo '</tr>';
				if ( is_user_logged_in() ){
					global $current_user,$talents_plugin_name,$post,$wp_query;
					wp_get_current_user();
					$taxonomy = get_object_taxonomies( $post_type);
					$author_query = array('post_type' => $post_type, 'posts_per_page' => '-1', 'author' => $current_user->ID, 'post_status' => array('publish', 'pending', 'draft', 'future', 'private'));
					$author_posts = new WP_Query($author_query);
					if($author_posts->have_posts()) : while($author_posts->have_posts()) : $author_posts->the_post();
						echo '<tr>';
							$id = get_the_ID();
							echo '<td>';
								echo '<a href="'.get_permalink( $id ).'">'.get_the_title().'</a>';
							echo '</td>';
							echo '<td>';
								$terms = get_the_terms($post->ID, $taxonomy[0]);
								$post_terms =array();
								if ( !empty($terms) ) {
									foreach ( $terms as $term ){
										$post_terms[] = esc_html(sanitize_term_field('', $term->name, $term->term_id, '', 'edit'));
									}
									echo implode( ', ', $post_terms );
								} else {
									echo '<em>'.__('No terms', 'ppd').'</em>';
								}
							echo '</td>';
							echo '<td>';
								echo  get_the_date( get_option('date_format') );
							echo '</td>';
							echo '<td>';
								$img_url = wp_get_attachment_url( get_post_thumbnail_id() );
								if($img_url){
									echo the_post_thumbnail(array(75,75)); 
								}else{
									echo '<img src="'.KAYA_PCV_PLUGIN_URL.'/images/front_end_thumb.jpg" height="75" width="75" />';
								}
							echo '</td>';
							echo '<td>';
								if ( ((($edit_published == 'false') && ( get_post_status( $id ) !='publish' )) || (($edit_published == 'true') && ( get_post_status( $id ) =='publish' )) ||  (($edit_published == 'true') && ( get_post_status( $id ) !='publish' ))) || ( (($delete_published == 'false') && ( get_post_status( $id ) !='publish' )) || (($delete_published == 'true') && ( get_post_status( $id ) =='publish' )) || (($delete_published == 'true') && ( get_post_status( $id ) !='publish' ))  ) ) {

							if ((($edit_published == 'false') && ( get_post_status( $id ) !='publish' )) || (($edit_published == 'true') && ( get_post_status( $id ) =='publish' )) ||  (($edit_published == 'true') && ( get_post_status( $id ) !='publish' )) ) {
								echo '<a href="'.kaya_get_current_page().'?action=edit&id='.$id.'"><i class="fa fa-pencil"></i></a>';
							}

							if( ((($edit_published == 'false') && ( get_post_status( $id ) !='publish' )) || (($edit_published == 'true') && ( get_post_status( $id ) =='publish' )) ||  (($edit_published == 'true') && ( get_post_status( $id ) !='publish' ))) && ( (($delete_published == 'false') && ( get_post_status( $id ) !='publish' )) || ( ($delete_published == 'true') && ( get_post_status( $id ) =='publish' )) || (($delete_published == 'true') && ( get_post_status( $id ) !='publish' ))  ) ){
								echo ' | ';
							}

							if ( (($delete_published == 'false') && ( get_post_status( $id ) !='publish' )) || (($delete_published == 'true') && ( get_post_status( $id ) =='publish' )) || (($delete_published == 'true') && ( get_post_status( $id ) !='publish' )) ) {
								echo '<a href="'.kaya_get_current_page().'?action=delete&id='.$id.'"><i class="fa fa-trash-o"></i></a>';
							}
						}else{
							echo '-';
						}	
							echo '</td>';
							echo '<td>';
								if(get_post_status( get_the_ID() ) == 'publish'){
									echo '<p title="'.__('Published','ppd').'"><i class="fa fa-eye" style="color:green;"></i></p>';
								}else{
									echo '<p title="'.__('Pending', 'ppd').'"><i class="fa fa-eye-slash" ></i></p>';
								}
							echo '</td>';
						echo '</tr>';   
					endwhile;
					wp_reset_postdata();
					wp_reset_query();
					else :
						echo '<tr><td colspan="8">'.__('Nothing found','ppd').'</td></tr>';
					endif;
				}else{
					echo '<p>'.__('Nothing found','ppd').'</p>';
				} ?>
			</table>
	<?php }
	public function form($instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'disable_pod_cpt_fields' => '',
			'thank_you' =>'',
			'submit_button_text' => __('Save Changes', 'ppd'),
			'post_delete_msg' => __('Your post has been deleted', 'ppd'),
			'form_update_message' => __( 'Form submitted successfully', 'ppd' ),
			'post_limit_exceeded_msg' => __("You Don't Have Permissions to create post", 'ppd'),
		));
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$("#<?php echo $this->get_field_id('post_type'); ?>").change(function(){
					$('.cpt_post_taxonomy').hide();
					$('.cpt_options_fields').hide();
					var $cpt_name = $(this).find('option:selected').val();
					$('.post_type_'+$cpt_name).show();
					
				}).change();

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
		echo '<p>';
			echo '<label>'.__('CPT Post Types','ppd').'</label>';
			echo '<select class="widefat" id="'.$this->get_field_id('post_type') .'" name="'.$this->get_field_name('post_type').'">';
			foreach ($cpt_types as $key => $options) {
				if( $options['type'] == 'post_type' ){
					echo '<option value="'.$options['name'].'" '.selected($options['name'], $instance['post_type']).' >'.trim(ucfirst($options['label'])).'</option>';
				}
			}
			echo '</select>';
		echo '</p>'; ?>
		<?php
		echo '<p>';
			echo "<label for=".$this->get_field_id('thank_you').">"._e('Form Submit Button Text', 'ppd')."</label>";
			echo '<input type="text" class="widefat" id="'.$this->get_field_id('submit_button_text').'"  value="'.$instance['submit_button_text'].'" name="'.$this->get_field_name('submit_button_text').'" />';
		echo '</p>';
		echo '<p>';
			echo "<label for=".$this->get_field_id('post_delete_msg').">"._e('Post Delete Message', 'ppd')."</label>";
			echo '<textarea class="widefat" id="'.$this->get_field_id('post_delete_msg').'"  name="'.$this->get_field_name('post_delete_msg').'" >'.$instance['post_delete_msg'].'</textarea>';
		echo '</p>';

		echo '<p>';
			echo "<label for=".$this->get_field_id('form_update_message').">"._e('Form Update Message', 'ppd')."</label>";
			echo '<textarea class="widefat" id="'.$this->get_field_id('form_update_message').'"  name="'.$this->get_field_name('form_update_message').'" >'.$instance['form_update_message'].'</textarea>';
		echo '</p>';
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Kaya_View_Edit_Cpt_Form_Widget");'));
?>