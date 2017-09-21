<?php
/**
 * CPT POst Thumbnail, if featured image exist or not
 * @param ( int ) $image_sizes
 */
function kaya_pod_featured_img($image_sizes, $img_crop=''){
	$img_url = get_the_post_thumbnail_url();

	if($img_url){
		if( ($img_crop == 'custom_thumb_sizes') || ($img_crop == 'custom_image_sizes') ){
			echo '<img src="'.kaya_image_sizes($img_url, $image_sizes[0], $image_sizes[1], true ).'" />';
		}else{
			the_post_thumbnail($image_sizes);
		}
	}else{
		echo '<img src="'.KAYA_PCV_PLUGIN_URL.'/images/default_image.jpg" height="400" width="400" />';
	}
}
/**
 * Image Resizer functionality
 */
if( !function_exists('kaya_image_sizes') ){
	function kaya_image_sizes($url, $width, $height=0, $align='') {
		return mr_image_resize($url, $width, $height, true, $align, false);
	}
}
/**
 * Get POD CPT Fields names and information, based on cpt slug names
 * @param ( string ) $cpt_slug
 */
if( !function_exists('kaya_get_cpt_fields') ){
	function kaya_get_cpt_fields($cpt_slug=''){
		
	 	$pod_slug = pods_v( 'last', 'url' );
	    $pod_options_data = pods( $cpt_slug, $pod_slug );
	    if( !empty($pod_options_data) ){	    	
	      return $pod_options_data->api->fields;
	    }
	}
}

/**
 * Get POD CPT fields meta information, based on cpt slug names, it's not displayed files meta data like: images, video and richtextarea... 
 * @param ( string ) $cpt_slug
 */
if( !function_exists('kaya_general_info_section') ){
	function kaya_general_info_section($cpt_slug){
		global $kaya_options;
		$cpt_meta_fields = kaya_get_cpt_fields($cpt_slug);
		$pod = pods( $cpt_slug, get_the_id() );
		if( !empty($cpt_meta_fields) ){
			echo '<div class="general-meta-fields-info-wrapper">';
				echo '<ul>';
					foreach ($cpt_meta_fields as $key => $meta_fields) {
						$opt_data = '';
						
						if($meta_fields['type'] == 'pick'){

							$post_meta_data_info = get_post_meta(get_the_ID(), $meta_fields['name'], false);
							$post_type_data_ids = $pod->display(  $meta_fields['name']);
							if( !empty($post_type_data_ids) ){
								if( $meta_fields['pick_object'] == 'post_type' ){
									$post_type_data_replace_text = str_replace('and ', ',', $post_type_data_ids);
									$post_type_data = explode(',', $post_type_data_replace_text);
									$get_post_type_info = array_map('trim',$post_type_data);
									$i=0;
									foreach (array_filter($get_post_type_info) as $key => $post_type_info) {
										if( !empty($post_type_info) ){
											$post_data[] = '<a href="'.get_the_permalink($post_meta_data_info[$i]['ID']).'">'.$post_type_info.'</a>';
										}
										$i++;
									}
									$opt_data .= implode(', ', $post_data);
								}elseif( $meta_fields['pick_object'] == 'taxonomy' ){
									$meta_data_id = $pod->display(  $meta_fields['name']);
									$meta_post_type_data = str_replace('and ', ',', $meta_data_id);
									$post_type_data_to_array = explode(',', $meta_post_type_data);
									$get_meta_info = array_map('trim',$post_type_data_to_array);
									$c=0;
									foreach (array_filter($get_meta_info) as $key => $post_info) {
										if( !empty($post_info) ){

											if( $meta_fields['pick_val'] == 'post_tag'){
												$post_data[] = '<a href="'.esc_url(home_url('/tag/'.$post_meta_data_info[$c]['slug'])).'?post_type='.$cpt_slug.'">'.$post_info.'</a>';
											}else{
												$post_data[] = '<a href="'.esc_url(home_url('/'.str_replace('_','-', $post_meta_data_info[$c]['taxonomy'])).'/'.$post_meta_data_info[$c]['slug']).'">'.$post_info.'</a>';
											}
										}
										$c++;
									}
									$opt_data .= implode(', ', $post_data);
								}else{
									$opt_data .= $pod->display(  $meta_fields['name']);
								}
							}          
						}
						elseif( ($meta_fields['type'] == 'date') && ($meta_fields['name'] == 'age')  ){
							$age = kaya_age_calculate($pod->display($meta_fields['name']));
							if( $age == '0' ){
								$opt_data .= '<1';
							}else{
								$opt_data .= $age;
							}
						}
						elseif(($meta_fields['type'] == 'file') || ($meta_fields['type'] == 'wysiwyg')  ){ }
						else{
							$fields_data = get_post_meta(get_the_ID(), $meta_fields['name'], true);
							if( !empty($fields_data) && !empty($fields_data[0])){
								$opt_data .=  $fields_data;
							}
						}
						if(($meta_fields['type'] == 'file') || ($meta_fields['type'] == 'wysiwyg')  ){
							if( $meta_fields['type'] == 'file'){
								if( ($meta_fields['options']['file_format_type'] == 'single') && ($meta_fields['name'] != 'featured_image')){
									if( $meta_fields['options']['file_type'] == 'image'){
										echo $data = $pod->display( $meta_fields['name'] );
									}
								}
							}
						 }
						else{
							if( !empty($kaya_options->{'enable_'.$cpt_slug.'_data'}) ){
								$opt_array_info = array_combine( $kaya_options->{'enable_'.$cpt_slug.'_data'},  $kaya_options->{'enable_'.$cpt_slug.'_data'});
							}else{
								$opt_array_info = array('noelements');
							}
							if( !is_single() ){
								if( in_array($meta_fields['name'], $opt_array_info) ){
									if( !empty($opt_data)  ){
										echo '<li><strong>'.$meta_fields['label'].':</strong> <span>'.$opt_data.'</span> </li>';
									}	
								}	
							}else{
								if( !empty($opt_data)  ){
									echo '<li><strong>'.$meta_fields['label'].':</strong> <span>'.$opt_data.'</span> </li>';
								}
							}					
						}
					} 
				echo '</ul>';
			echo '</div>';    
		}  
	}
}

if( !function_exists('kaya_compcard_info_section') ){
	function kaya_compcard_info_section($cpt_slug){
		global $kaya_options;
		$compcard ='';
		$option_fields = kaya_get_cpt_fields($cpt_slug);
		if( !empty($kaya_options->{'enable_'.$cpt_slug.'_data'}) ){
			$opt_array_info = array_combine( $kaya_options->{'enable_'.$cpt_slug.'_data'},  $kaya_options->{'enable_'.$cpt_slug.'_data'});
		}else{
			$opt_array_info = array('noelements');
		}
		if( !empty($opt_array_info) ){
			foreach ($opt_array_info as $key => $opt_key) {
					if( ( $option_fields[$opt_key]['type'] == 'date') && ( $option_fields[$opt_key]['name'] == 'age')  ){
						$age = kaya_age_calculate(get_post_meta(get_the_ID(), $opt_key, true ));
							if( $age == '0' ){
								$fields_data = '<1';
							}else{
								$fields_data = $age;
							}
						
					}else{
						$fields_data = get_post_meta(get_the_ID(), $opt_key, true );
					}
					$compcard .= '<div><strong>'.$option_fields[$opt_key]['label'].':</strong> <span>'.$fields_data.'</span> </div>';
			}
		}
		return $compcard; 
	}
}
/**
 * It displayed 'files' meta data  as tab section, like: images, video and richtextarea...
 * @param ( string ) $cpt_slug
 */
function kaya_tab_section($cpt_slug){
	$cpt_meta_fields = kaya_get_cpt_fields($cpt_slug);
	$pod = pods( $cpt_slug, get_the_id() );
	if( !empty($cpt_meta_fields) ){
			echo '<ul class="tabs_content_wrapper">';
				foreach ($cpt_meta_fields as $key => $meta_fields) {
					if(($meta_fields['type'] == 'file') || ($meta_fields['type'] == 'wysiwyg') || ($meta_fields['type'] == 'video') ){
						$fields_data = get_post_meta(get_the_ID(), $meta_fields['name'], false);

						if(!empty($fields_data) && !empty($fields_data[0])){
							//print_r($fields_data);
		          			echo '<li><a href="#'.$meta_fields['name'].'">'.$meta_fields['label'].'</a></li>';
						}
					}
				}
			echo '</ul>';
	}
}
/**
 * It displayed 'files' meta data  as tab section, like: images, video and richtextarea...
 * @param ( string ) $cpt_slug
 */
function kaya_media_section($cpt_slug){
	$cpt_meta_fields = kaya_get_cpt_fields($cpt_slug);
	$pod = pods( $cpt_slug, get_the_id() );
	if( !empty($cpt_meta_fields) ){
		foreach ($cpt_meta_fields as $key => $meta_fields) {
			if($meta_fields['type'] == 'file'){
				
				if( ($meta_fields['name'] != 'featured_image') ){
					$images_urls = get_post_meta(get_the_ID(), $meta_fields['name'], false);
					if( !empty($images_urls) && ( !empty($images_urls[0])) ){ // Images
						echo '<div  id="'.$key.'" class="file-data-content-wrapper single-page-meta-content-wrapper">';
							echo '<h3>'.$meta_fields['label'].'</h3>';
							if(( $meta_fields['options']['file_type'] == 'video') ||  $meta_fields['options']['file_type'] == 'audio' ){
								$video_srcs = $pod->display(  $meta_fields['name']);
								$videos = explode(' ', $video_srcs);
								foreach ($videos as $key => $src) {
									if( $meta_fields['options']['file_type'] == 'video' ){
										echo wp_video_shortcode( array( 'src' => $src ) );
									}elseif($meta_fields['options']['file_type']== 'audio'){

										$attr = array(
										        'src'      => $src,
										        'loop'     => '',
										        'autoplay' => '',
										        'preload' => 'none'
										        );
										echo wp_audio_shortcode( $attr );

									}else{

									}
								}
							}else{
								if( $meta_fields['options']['file_type'] == 'text' ){
									$files = get_post_meta(get_the_ID(), $meta_fields['name'], false);
									if( !empty($files) ){
										foreach ($files as $key => $file) {
											if( ($meta_fields['options']['file_format_type'] != 'single')){
												echo '<a href="'.$file['guid'].'" target="_blank">'.$file['guid'].'</a><br />';
											}else{
												echo '<a href="'.$file['guid'].'" target="_blank">'.$meta_fields['label'].'</a><br />';
											}
										}
									}
									$data = $pod->display( $meta_fields['name'] );
								}else{
									echo $data = $pod->display( $meta_fields['name'] );
								}
							}
						echo '</div>';
					} 
				}
			}
			if($meta_fields['type'] == 'wysiwyg'){
			$richcontent = get_post_meta(get_the_ID(), $meta_fields['name'], true);
			if( !empty($richcontent) ){ // Images
			echo '<div id="'.$key.'" class="richtext-data-content-wrapper single-page-meta-content-wrapper">';
			echo '<h3>'.$meta_fields['label'].'</h3>';
			echo trim($richcontent);
			echo '</div>';
			}             
			}
		}   
	}
}


/**
* Display the posts Based on user roles
*/
if( !function_exists('kaya_talent_filter_posts_list') ){
	add_action('pre_get_posts', 'kaya_talent_filter_posts_list');
	function kaya_talent_filter_posts_list($query)
	{
	    global $pagenow; 
	    global $current_user;
	    wp_get_current_user();
	    if(!current_user_can('administrator') && !current_user_can('editor')&& ('edit.php' == $pagenow)){
	        $query->set('author', $current_user->ID); 
	    }
	}
}

function kaya_get_template_part( $slug, $name = '' ) {
	$template = '';
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", "{$slug}-{$name}.php" ) );
	}
	if ( ! $template && $name && file_exists( KAYA_PCV_PLUGIN_PATH . "/templates/{$slug}-{$name}.php" ) ) {
		$template = KAYA_PCV_PLUGIN_PATH . "/templates/{$slug}-{$name}.php";
	}
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", "{$slug}.php" ) );
	}
	if ( $template ) {
		load_template( $template, false );
	}

}

/**
 * Include the template files
 */
add_filter( 'template_include', 'kaya_set_template'  );
function kaya_set_template( $template ){
		$template_name = '';
        if ( is_tax() ) {
            if( file_exists(locate_template('taxonomy.php') )) {
                $template = locate_template( 'taxonomy.php');
            }
            else {
                $template = KAYA_PLUGIN_PATH . '/templates/taxonomy.php';
            }
        }

        return $template;   
        
    }

/**
 * Pods Cpt Fields data
 */
function kaya_get_pod_cpt_fields(){
	if( !function_exists('pods_api') ){
		return false;
	}
	return pods_api()->load_pods( array( 'fields' => false ) );
}

/**
 * Pagination
 */
if ( ! function_exists( 'kaya_pagination' ) ) :
function kaya_pagination() {
    // Don't print empty markup if there's only one page.
    if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
        return;
    }
    $paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
    $pagenum_link = html_entity_decode( get_pagenum_link() );
    $query_args   = array();
    $url_parts    = explode( '?', $pagenum_link );
    if ( isset( $url_parts[1] ) ) {
        wp_parse_str( $url_parts[1], $query_args );
    }
    $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
    $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';
    $format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
    $format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';
    // Set up paginated links.
    $links = paginate_links( array(
        'base'     => $pagenum_link,
        'format'   => $format,
        'total'    => $GLOBALS['wp_query']->max_num_pages,
        'current'  => $paged,
        'mid_size' => 3,
        //'add_args' => array_map( 'urlencode', $query_args ),
        'prev_text' => '<i class="fa fa-angle-left"></i>',
        'next_text' => '<i class="fa fa-angle-right"></i>',
        'type'      => 'list',
    ) );
    $pagination_allowed_tags = array(
        'a' => array(
            'href' => array(),
            'title' => array(),
            'class' => array()
        ),
        'i' => array(
            'class' => array()
        ),
        'span' => array(
            'class' => array()
        ),
        'ul' => array(
            'class' => array()
        ),
        'li' => array(),
    );
    if ( $links ) :
    ?>  <div class="pagination">
            <?php echo wp_kses($links,$pagination_allowed_tags); ?>
        </div>       
    <?php
    endif;
}
endif;


/*---------------------------------------------
Get Current Page URL
--------------------------------------------- */
if ( ! function_exists( 'kaya_get_current_page_url' ) ){
    function kaya_get_current_page_url(){
        global $wp;
        return add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
    }
}
if( !function_exists('kaya_get_current_page') ){
	function kaya_get_current_page(){
		kaya_get_current_page_url();
	}
}

/*---------------------------------------------------------------------------------------
 Calculating age automatically Based on year
---------------------------------------------------------------------------------------*/
function kaya_age_calculate($dob)
{ 
    $dob=explode("-",$dob);
   // print_r( $dob); 
    $curMonth = date("m");
    $curDay = date("j");
    $curYear = date("Y");
    $age = $curYear - $dob[0];
    //if($curMonth<$dob[1] || ($curMonth==$dob[1] && $curDay<$dob[2]))
       //   $age--;

    return $age;
}

/*---------------------------------------------------------------------------------------
	age stored in database, if we need to get the age in years use this
 	id :age_filter 
---------------------------------------------------------------------------------------*/
function kaya_update_age_calculation(){
	/**
	* It will check the pods plugins exist or not if not return 
	*/
	if( !function_exists('pods_api') ){
		return false;
	}

	$pods_options = pods_api()->load_pods( array( 'fields' => false ) );
	foreach ($pods_options as $key => $options) {
		if( $options['type'] == 'post_type' ){
			 $args = array('post_type' => $options['name'], 'posts_per_page' => -1);
		    $loop = new WP_Query( $args );
		    while ( $loop->have_posts() ) : $loop->the_post();
		    	foreach ($options['fields'] as $fields_key => $field_val) {
		    		if( ($options['fields'][$fields_key]['type'] == 'text') && ($options['fields'][$fields_key]['name'] == 'year_of_birth') ||  ($options['fields'][$fields_key]['type'] == 'date') && ($options['fields'][$fields_key]['name'] == 'age') ){

		    			$age = get_post_meta(get_the_ID(), 'age', true);
		    			update_post_meta(get_the_ID(), 'age_filter', kaya_age_calculate($age));
		    		}
		    	}
		    endwhile;
		}
	}
}
add_action('init', 'kaya_update_age_calculation');


function kaya_widget_loop_data(){
	//if( $instance['disable_post_thumbnail'] != 'on' ){
		echo '<a href="'.get_the_permalink().'">';
			$img_url = get_the_post_thumbnail_url();
			if($img_url){
				the_post_thumbnail($image_sizes);
			}else{
				echo '<img src="'.KAYA_PCV_PLUGIN_URL.'/images/default_image.jpg" height="400" width="400" />';
			}
		echo '</a>';
	//}
	echo '<div class="description">';
		$option_fields = kaya_get_cpt_fields($instance['post_type']);
		if( $instance['disable_post_title'] != 'on' ){
			echo '<h4><a href="'.get_the_permalink().'">'.get_the_title().'</a></h4>';
		}
		if( !empty($instance['enbale_selected_cpt_fields']) ){
			echo '<div class="post-meta-info-wrapper">';
				echo '<ul>';
				foreach ($instance['enbale_selected_cpt_fields'] as $key => $fields_data) {
					$meta_data = get_post_meta(get_the_ID(), $fields_data, true);
					if( !empty($meta_data) ){
						echo '<li><strong>'.$option_fields[$fields_data]['label'].'</strong>: '.$meta_data.'</li>';
					}
				}
				echo '</ul>';
			echo '</div>';
		}
		if( $instance['disable_post_content'] != 'on' ){
			echo '<p>'.wp_trim_words( get_the_content(), $instance['post_content_limit'], null ).'</p>';
		}
	echo '</div>';
}
// Get post type
function kaya_get_post_type(){
	return get_post_type( get_the_ID() );
}

/**
 * Disable restict cpt meta box panels in kaya roles manager
 */
function kaya_remove_metabox_panel(){ ?>
	<style type="text/css">
		.restrict-metabox-panels, .nav-tab-wrapper .nav-tab.cpt_meta_box_restrict, .kta-users-note{
			display: none;
		}
	</style>
<?php }
add_action('admin_head', 'kaya_remove_metabox_panel');


add_action('pre_get_posts', 'kaya_taxonomy_post_per_page');
function kaya_taxonomy_post_per_page( $query ){
	global $kaya_options;
	$limit = !empty($kaya_options->post_limit) ? $kaya_options->post_limit : '-1';
      if ( !is_admin() && $query->is_tax() && $query->is_main_query() || ( ($query->is_search) && !is_admin() ) ):
        $query->set('posts_per_page', $limit);
        return;
    endif;
}


/**
 * Pods Cpt post taxonomy & Featured Image
 */

function kaya_pods_cpt_taxonomy_post_init(){
	if( !function_exists('pods_api') ){
        return false;
    }
         
	$pods_options = pods_api()->load_pods( array( 'fields' => false ) );
	foreach ($pods_options as $key => $options) {		
		if( ($options['type'] == 'post_type')){
			//add_action( "pods_api_post_save_pod_item_{$options['name']}", 'kaya_actors_pods_cpt_taxonomy_update', 10, 3 );
		}
	}
}
//add_action('pods_api_post_save_pod_item_talent', 'kaya_actors_pods_cpt_taxonomy_update');
function kaya_actors_pods_cpt_taxonomy_update( $pieces, $is_new_item, $id ) {
	// Pods cpt categories
	$pods_options = pods_api()->load_pods( array( 'fields' => false ) );
	$terms = $pieces[ 'fields' ]['post_categories'][ 'value' ];
	
	if ( empty( $terms ) ) {
		$terms = null;
	} else {
		if ( ! is_array($terms) ) {
			// create an array out of the comma separated values
			$terms = explode(',', $terms);
		}
        	$terms = array_map('intval', $terms);
	}
    foreach ($pods_options as $key => $options) {
    	if( $options['type'] == 'taxonomy' ){
	        //wp_set_object_terms( $id, $terms, $options[ 'name' ], false );
    	}
	}
	// Pods cpt Featured Image
	$featured_image = $pieces['fields']['featured_image']['value'];
	$array_keys = array_keys($featured_image);
	set_post_thumbnail( $id, $array_keys[0] );	
}

function pods_update_form_post_meta($post_id, $field_name, $value = '', $val_array = 'true')
{
    if (empty($value) OR !$value)
    {
        delete_post_meta($post_id, $field_name);
    }
    elseif (!get_post_meta($post_id, $field_name))
    {
        add_post_meta($post_id, $field_name, $value);
    }
    else
    {
        update_post_meta($post_id, $field_name, $value);
    }
}

/**
 * Multiple Images Upload
 * @param Image ID
 * @param Post ID
 */

function pods_get_featured_image_id($image_id, $post_id)
{
    //print_r($_POST['upload_pf_profile_cover_image']);
    if (!empty($_FILES[$image_id]['name']))
    {
        $upload = wp_upload_bits($_FILES[$image_id]['name'], null, file_get_contents($_FILES[$image_id]['tmp_name']));
        $wp_filetype = wp_check_filetype(basename($upload['file']) , null);
        $wp_upload_dir = wp_upload_dir();
        $attachment = array(
            'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($upload['file']) ,
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])) ,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        require_once (ABSPATH . 'wp-admin/includes/image.php');

        $attach_data1 = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $attach_data1);
        update_post_meta($post_id, '_thumbnail_id', $attach_id);
    }
}
?>