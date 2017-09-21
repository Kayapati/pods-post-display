<?php
class Kaya_Pods_Cpt_frontend_Form_Widget extends WP_Widget{
	public function __construct(){
		global $pods_success_msg;
		parent::__construct('cpt-front-end-forms',__('Pods - Frontend posting form','ppd'),
			array('description' => __('Use this widget to add  frontend form for posting.','ppd'))
		);
		add_filter('pods_pod_form_success_message',array(&$this, 'pods_form_success_message'));
	}
	function pods_form_success_message(){
		global $pods_success_msg;
		return  $pods_success_msg;
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
	public function widget( $args,$instance){
		global $current_user, $pods_success_msg, $kaya_settings;
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'disable_pod_cpt_fields' => '',
			'thank_you' =>'',
			'submit_button_text' => __('Save Changes', 'ppd'),
			'form_success_message' => __( 'Form submitted successfully', 'ppd' ),
			'non_user_role_form_access_msg' => __("You Don't Have Permissions to create post", 'ppd'),
		));
		wp_enqueue_style( 'pods-form' );
		echo $args['before_widget'];
			if( !function_exists('pods_api') ){
				return false;
			}
			if ( !is_user_logged_in() ){ // when user not logged in return false
				echo '<p class="kaya-error-message">'.(!empty($kaya_settings['non_logged_users_msg']) ? stripslashes($kaya_settings['non_logged_users_msg']) : __('Please Login', 'ppd')).'</p>';
				return;
			}
			$user_id = $current_user->ID;
			$current_user_data =  wp_get_current_user($current_user->ID);
			$edit_post = isset( $current_user_data->allcaps['edit_'.$instance['post_type'].'s'] ) ? 'true' : 'false';
			$post_count = $this->kaya_pods_cpt_post_count($current_user->ID, $instance['post_type']);
			$role_post_access = get_option('role_post_restrict');
			$pods_cpt_taxonomies = get_object_taxonomies($instance['post_type']);
			$pods_taxonomy_info = get_object_taxonomies($instance['post_type'],  'objects' );
			$pods_options = kaya_get_cpt_fields($instance['post_type']);			
			$pod = pods($instance['post_type']);
			if( is_user_logged_in() ){
				$limit = !empty($role_post_access[$current_user->roles[0]]['limit'][$instance['post_type']]) ? $role_post_access[$current_user->roles[0]]['limit'][$instance['post_type']] : '100000';
			}else{
				$limit = '1000'; 
			}
			// Checking post capability
			if( ( $edit_post == 'true' ) ){
				if( $post_count >= $limit ){
					if( isset($_REQUEST['success']) && ( $_REQUEST['success'] == '1' ) ){
						echo '<p class="kaya-success-message">';
						echo __('Form submitted successfully', 'ppd');
						echo '</p>';
					}
					if( !isset($_REQUEST['success'])){
						echo '<p class="kaya-success-message">';
						echo  !empty($role_post_access[$current_user->roles[0]]['limit']['message'][$instance['post_type']]) ? $role_post_access[$current_user->roles[0]]['limit']['message'][$instance['post_type']] : __('Your limit has been exceeded', 'ppd');
						echo '</p>';
					}
				}else{
					// Submitting the post data
					if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['action'] ) && !empty( $_POST['action'] ) && $_POST['action'] == 'user_talent_profile' ){

						if( current_user_can('administrator')) { 
							$post_status = 'publish';
						}else{
							$post_status = $pod->api->pod_data['options']['default_status'];
						}	
						$pods_cpt_categories = $_POST['pods_cpt_categories'] ? $_POST['pods_cpt_categories'] : '';
						$pods_cpt_post_data = array(
							'post_type' => $instance['post_type'],
							'post_title'    => wp_strip_all_tags( $_POST['pods-cpt-post-title'] ),
							'post_status'   => $post_status,
							'post_author'   => $user_id,
							);					 
							$post_id = wp_insert_post( $pods_cpt_post_data );
							if( $post_id ){
								// Update featured image to the current cpt post
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
										$fields_data = $pod->fields ($pods_keys['name']);
										if( !empty($_POST[$pods_keys['name']]) ){
											$pods_file_data = array_keys($_POST[$pods_keys['name']]);
											pods_update_form_post_meta($post_id, '_pods_'.$pods_keys['name'], array_combine($pods_file_data, $pods_file_data));
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
							wp_redirect(get_the_permalink().'/?success=1');

						}else{
							wp_redirect(get_the_permalink().'/?success=0');
						}							
					} // End Here
					if( isset($_REQUEST['success']) && ($_REQUEST['success'] == '1') ){ // Success Results
						echo '<p class="kaya-success-message">'. ( !empty($instance['form_success_message']) ? $instance['form_success_message'] : __('Form submitted successfully','ppd')).'</p>';
					}
					// Form Start Here
					echo '<form action="" method="post" class="pods-submittable pods-form pods-form-front" enctype="multipart/form-data">';
						echo '<div class="pods-submittable-fields">';
							echo '<ul class="pods-form-fields">';
								echo '<li>';  // Post title
									echo '<div class="pods-field-label">';
										echo '<label class="pods-form-ui-label pods-form-ui-label-pods-field-post-title" for="pods-form-ui-pods-field-post-title">'.__('Enter Post Title (required)', 'ppd') .'</label>';
									echo '</div>';
									echo '<div class="pods-field-input">'; ?>
										<input type="text" id ="pods-cpt-post-title" required placeholder="" name="pods-cpt-post-title" value="" />
									<?php
									echo '</div>';
								echo '</li>'; 
								echo '<li>'; // Post Featured Image
									echo '<div class="pods-field-label">';
										echo '<label class="pods-form-ui-label" for="">'.__('Featured Image', 'ppd').'</label>';
									echo '</div>';
									echo '<div class="pods-field-input">';	 ?>
										<input type="file" id="upload_pods_post_featured_img" name="upload_pods_post_featured_img" >
									<?php 
									echo '</div>';
								echo '</li>';
								if(count($pods_cpt_taxonomies) > 0)  // Cpt Post Taxonomies list
								{	
									foreach ($pods_cpt_taxonomies as $key => $pods_cpt_taxonomy) {
										$pods_cpt_terms = get_terms($pods_cpt_taxonomy, array('hide_empty' => false) );
										if( !empty($pods_cpt_terms) ){
											echo '<li>';
												echo '<ul>';
													echo '<div class="pods-field-label">
														<label class="pods-form-ui-label" for="">'.trim($pods_taxonomy_info[$pods_cpt_taxonomy]->label).'</label>
													</div>';
													echo '<div class="pods-field-input">';
														echo '<div class="pods-pick-values pods-pick-checkbox">';
															foreach ($pods_cpt_terms as $key => $pods_cpt_term) {
																if( !empty($pods_cpt_term) ){
																	echo '<li class="pods-field"><div class="class="pods-pick-values pods-pick-checkbox"">';
																		echo '<input name="pods_cpt_categories[]" data-name-clean=""  id="pods_cpt_categories" class="pods_cpt_categories" type="checkbox" value="'.trim($pods_cpt_term->slug).'">';
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
						<input id="pods-cpt-submit-button" class="" type="submit" value="<?php echo !empty($instance['submit_button_text']) ? $instance['submit_button_text'] : __('Submit','ppd'); ?>" />
		            	<input type="hidden" name="action" value="user_talent_profile" />
		            	<input type="hidden" name="empty-description" id="empty-description" value="1"/>
		            	<?php wp_nonce_field( 'user-talent-profile-page' );
					echo '</form>'; // Form End
				}
			}else{
				echo '<p class="kaya-success-message">'. ( !empty($instance['non_user_role_form_access_msg']) ? $instance['non_user_role_form_access_msg'] : __('You Don\'t Have Permissions to create post','ppd')).'</p>';
			}
		echo $args['after_widget'];
	}
	public function kaya_pods_cpt_post_count($userid, $post_type) {
	    global $wpdb;
	   	$pstatus = "IN ('publish', 'pending', 'draft', 'future', 'private')";
	    $count = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status ". $pstatus ." AND post_author = $userid AND post_type ='".$post_type."'");
	    return $count;
	}
	public function form($instance){
		$instance = wp_parse_args($instance,array(
			'post_type' => '',
			'disable_pod_cpt_fields' => '',
			'button_text' =>'',
			'thank_you' => '',
			'submit_button_text' => '',
			'form_success_message' => __( 'Form submitted successfully', 'ppd' ),
			'non_user_role_form_access_msg' => __("You Don't Have Permissions to create post", 'ppd'),
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
			});
		</script>
		<?php
		$cpt_types = kaya_get_pod_cpt_fields();		
		echo '<p>';
			echo '<label>'.__('CPT Post Types','ppd').'</label>';
			echo '<select class="widefat" id="'.$this->get_field_id('post_type') .'" name="'.$this->get_field_name('post_type').'">';
			foreach ($cpt_types as $key => $options) {
				if( $options['type'] == 'post_type' ){
					echo '<option value="'.$options['name'].'" '.selected($options['name'], $instance['post_type']).' >'.trim(ucfirst($options['label'])).'</option>';
				}
			}
			echo '</select>';
		echo '</p>'; 
		echo '<p>';
			echo "<label for=".$this->get_field_id('thank_you').">"._e('Form Submit Button Text', 'ppd')."</label>";
			echo '<input type="text" class="" id="'.$this->get_field_id('submit_button_text').'"  value="'.$instance['submit_button_text'].'" name="'.$this->get_field_name('submit_button_text').'" />';
		echo '</p>';

		echo '<p>';
			echo "<label for=".$this->get_field_id('form_success_message').">"._e('Form Success Message', 'ppd')."</label>";
			echo '<textarea class="widefat" id="'.$this->get_field_id('form_success_message').'"  name="'.$this->get_field_name('form_success_message').'" >'.$instance['form_success_message'].'</textarea>';
		echo '</p>';

		echo '<p>';
			echo "<label for=".$this->get_field_id('non_user_role_form_access_msg').">"._e('Non User Role Form Accessing Message', 'ppd')."</label>";
			echo '<textarea class="widefat" id="'.$this->get_field_id('non_user_role_form_access_msg').'"  name="'.$this->get_field_name('non_user_role_form_access_msg').'" >'.$instance['non_user_role_form_access_msg'].'</textarea>';
		echo '</p>';
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Kaya_Pods_Cpt_frontend_Form_Widget");'));
?>