<?php
/**
 * POD CPT Custom fields Enable settings for taxonomy pages 
 */
include_once 'export-options.php';
class Kaya_Taxonomy_Cpt_Fields_Settings{
      private $pods_options;
      function __construct(){
         add_action( 'admin_menu', array($this, 'kaya_settings_page_init'),0,11 );
         add_action( 'init', array($this, 'kaya_options_data') );
         add_action( "init", array($this, 'kaya_load_settings_page') );
      }
      // Creating Menu Page, admin settings
      function kaya_settings_page_init() {
         add_menu_page(__('Pods Post Display', 'ppd'), __('Pods Post Display', 'ppd'), 'manage_options', 'kaya-settings', array($this, 'kaya_page_settings_options'));      
      }
      function kaya_options_data() {
         if( !function_exists('pods_api') ){
             return false;
         }

         $this->pods_options = pods_api()->load_pods( array( 'fields' => false ) );
         $settings = get_option( "kaya_options" );
         $pods_fields = array();
           foreach ($this->pods_options as $key => $options) {
               if( $options['type'] == 'post_type' ){ 
                  $pods_fields['enable_'.$options['name'].'_data'] = '0';
               }
          }
         // Settings Options Data
         if ( empty( $settings ) ) {
            $settings = array(
               'taxonomy_columns' => '4',
               'choose_image_sizes' => 'wp_image_sizes',
               'taxonomy_gallery_width' => '',
               'taxonomy_gallery_height' => '',
               'wp_default_img_sizes' => '',
               'post_limit' => '-1',
            );
            $fields_opt_data = array_merge( $settings, $pods_fields );
            add_option( "kaya_options", $fields_opt_data, '', 'yes' );
         }   
      }
      // Save Settings page Options data
      function kaya_load_settings_page() {
         if ( isset($_POST["kaya-settings-submit"]) && ( $_POST["kaya-settings-submit"] == 'Y' ) ) {
            check_admin_referer( "kaya-settings-page" );
            $this->kta_save_theme_settings();
            $url_parameters = isset($_GET['tab'])? 'updated=true&tab='.$_GET['tab'] : 'updated=true';
            wp_redirect(admin_url('admin.php?page=kaya-settings&'.$url_parameters));
            exit;
         }
      }
      // Updating The Options Data From Input Fields
      function kta_save_theme_settings(){
         global $pagenow;
         $settings = get_option( "kaya_options" );           
         if ( $pagenow == 'admin.php' && $_GET['page'] == 'kaya-settings' ){ 
            if ( isset ( $_GET['tab'] ) )
            $tab = $_GET['tab'];
            else
            $tab = 'cpt_fields'; 
            switch ( $tab ){ 
               case 'cpt_fields' :
                  foreach ($this->pods_options as $key => $options) {
                     if( $options['type'] == 'post_type' ){ 
                        $settings['enable_'.$options['name'].'_data'] = $_POST['enable_'.$options['name'].'_data'] ? $_POST['enable_'.$options['name'].'_data'] : '';
                     }
                   }
                  break;
               case 'taxonomy' :
                  $settings['taxonomy_columns'] = !empty($_POST['taxonomy_columns']) ? $_POST['taxonomy_columns'] : '4';
                  $settings['choose_image_sizes'] = !empty($_POST['choose_image_sizes']) ? $_POST['choose_image_sizes'] : 'wp_image_sizes';
                  $settings['taxonomy_gallery_width'] = !empty($_POST['taxonomy_gallery_width']) ? $_POST['taxonomy_gallery_width'] : '420';
                  $settings['taxonomy_gallery_height'] = !empty($_POST['taxonomy_gallery_height']) ? $_POST['taxonomy_gallery_height'] : '580';
                  $settings['wp_default_img_sizes'] = !empty($_POST['wp_default_img_sizes']) ? $_POST['wp_default_img_sizes'] : 'thumbnail';
                  $settings['post_limit'] = !empty($_POST['post_limit']) ? $_POST['post_limit'] : '-1';
                  break; 
            }
         }
         $updated = update_option( "kaya_options", $settings );
      }
      // Tabs Section
      function kta_admin_tabs( $current = 'general' ) { 
         $tabs = array(
            'cpt_fields' => __('CPT Fields','ppd'), 
            'taxonomy' => __('Taxonomy','ppd'),

         );
         $links = array();
         echo '<div id="icon-admin" class="icon32"><br></div>';
         echo '<h2 class="nav-tab-wrapper">';
         foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=kaya-settings&tab=$tab'>$name</a>";
         }
         echo '</h2>';
      }
      // Cpt Fields Options Settings Page Data
      function kaya_page_settings_options() {
         global $pagenow;
         $settings = array_filter(get_option( "kaya_options" )); ?>
         <div class="wrap kaya-admin-options-dashboard">
            <script type='text/javascript'>
               jQuery(document).ready(function($) {
                  jQuery('.opt_color_pickr').each(function(){
                     jQuery(this).wpColorPicker();
                  });
               });
            </script>
            <div class="kaya-header-title">
               <h1><?php _e('Options Settings','ppd'); ?></h1> 
            </div>   
            <?php
            if ( isset($_GET['updated']) && ( 'true' == esc_attr( $_GET['updated'] ) ) ) echo '<div class="updated" ><p>Theme Settings updated.</p></div>';
            if ( isset ( $_GET['tab'] ) ) $this->kta_admin_tabs($_GET['tab']); else $this->kta_admin_tabs('cpt_fields');
            ?>
            <div id="poststuff">
               <form method="post" action="<?php admin_url( 'admin.php?page=kaya-settings' ); ?>"> 
                  <?php
                  wp_nonce_field( "kaya-settings-page" );                
                  if ( $pagenow == 'admin.php' && $_GET['page'] == 'kaya-settings' ){ 
                  if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
                     else $tab = 'cpt_fields';
                     switch ( $tab ){
                        case 'cpt_fields' : ?>
                           <div class="kta-admin-panel">
                           <?php echo '<table class="form-table">'; ?>                             
                                    <?php                                     
                                        foreach ($this->pods_options as $key => $options) {
                                          if( $options['type'] == 'post_type' ){
                                             echo ' <tr>
                                                <th>'.__('Enable ', 'ppd').' '.$options['label'].' '.__('CPT Fields','ppd').'</th>
                                                <td>';                                         
                                                   echo '<h3>'.$options['label'].'</h3>';
                                                   echo '<div class="cpt_options_fields" id="cpt_'.$options['name'].'">';
                                                      foreach ($options['fields'] as $fields_key => $field_val) {
                                                         echo '<div>';   
                                                            echo '<label>';                                                        
                                                               if( isset($settings['enable_'.$options['name'].'_data']) ){
                                                                  $checked = in_array($fields_key,  $settings['enable_'.$options['name'].'_data']) ? 'checked' : '';
                                                               }else{
                                                                  $checked ='';
                                                               }
                                                               if( $options['fields'][$fields_key]['type'] != 'file' ){   ?>
                                                                  <input type="checkbox" class="" id="enable_<?php echo $options['name']; ?>_data"  value="<?php echo trim($fields_key) ?>" name="enable_<?php echo $options['name']; ?>_data[]" <?php echo $checked; ?> />
                                                                  <?php echo $field_val['label']; 
                                                               }        
                                                            echo '</label>';
                                                         echo '</div>';
                                                      }                                                   
                                                   echo '</div>';
                                                 echo '</td>
                                             </tr>';    
                                          }                                            
                                       }
                                    ?>                                                                                     
                                 </table>
                           </div>  

                          <?php break;
                          case 'taxonomy': ?>
                              <table class="form-table">
                              <tr>
                              <th> <label for="taxonomy_columns"><?php _e('Taxonomy Columns','ppd'); ?></label> </th>
                              <td>
                                 <select name="taxonomy_columns">
                                    <option val="6" <?php selected('6', ( isset($settings['taxonomy_columns']) ? $settings['taxonomy_columns'] : '' )) ?> >6</option>
                                    <option val="5" <?php selected('5', ( isset($settings['taxonomy_columns']) ? $settings['taxonomy_columns'] : '' )) ?> >5</option>
                                    <option val="4" <?php selected('4', ( isset($settings['taxonomy_columns']) ? $settings['taxonomy_columns'] : '' )) ?> >4</option>
                                    <option val="3" <?php selected('3', ( isset($settings['taxonomy_columns']) ? $settings['taxonomy_columns'] : '' )) ?> >3</option>
                                    <option val="2" <?php selected('2', ( isset($settings['taxonomy_columns']) ? $settings['taxonomy_columns'] : '' )) ?> >2</option>
                                 </select>
                              </td>
                           </tr>
                           <tr>
                              <th> <label for="choose_image_sizes"><?php _e('Choose Image Size','ppd'); ?></label> </th>
                              <td>
                                 <select name="choose_image_sizes" class="choose_image_sizes">
                                    <option value="wp_image_sizes" <?php selected('wp_image_sizes', ( isset($settings['choose_image_sizes']) ? $settings['choose_image_sizes'] : '' )) ?> ><?php _e(' Wordpress Default Image Sizes', 'ppd'); ?></option>
                                    <option value="custom_image_sizes" <?php selected('custom_image_sizes', ( isset($settings['choose_image_sizes']) ? $settings['choose_image_sizes'] : '' )) ?> ><?php _e('Custom Image Sizes', 'ppd'); ?></option>
                                 </select>
                              </td>
                           </tr>
                           <tr>
                              <th> <label for="wp_default_img_sizes"><?php _e('Wordpress Default Image Sizes','ppd'); ?></label> </th>
                              <td>
                              <?php  
                                 $default_image_sizes = array( 'thumbnail', 'medium', 'large' );
                                 echo '<select name="wp_default_img_sizes" class="wp_default_img_columns">';
                                 foreach ($default_image_sizes as $key => $image_size) { ?>
                                    <option value="<?php echo $image_size; ?>" <?php selected($image_size, ( isset($settings['wp_default_img_sizes']) ? $settings['wp_default_img_sizes'] : '' )) ?> ><?php echo ucfirst($image_size); ?></option>
                                 <?php } ?>
                              </td>
                           </tr>
                            <tr>
                              <th><label for="taxonomy_gallery_width"><?php _e('Images Custom Width & Height','ppd'); ?></label></th>
                              <td>
                                 <input type="text" name="taxonomy_gallery_width" id="taxonomy_gallery_width" value="<?php echo isset($settings['taxonomy_gallery_width']) ? $settings['taxonomy_gallery_width'] : '420'; ?>" class="small-text" />X
                                 <input type="text" name="taxonomy_gallery_height" id="taxonomy_gallery_height" value="<?php echo isset($settings['taxonomy_gallery_height']) ? $settings['taxonomy_gallery_height'] : '580'; ?>" class="small-text" /><?php esc_html_e('px','ppd'); ?>
                              </td>
                           </tr>
                           <tr>
                              <th><label for="images"><?php _e('Limit','ppd'); ?></label></th>
                              <td>
                                 <input type="text" name="post_limit" id="post_limit" value="<?php echo !empty($settings['post_limit']) ? $settings['post_limit'] : '-1'; ?>" class="small-text" />
                              </td>
                           </tr>
                           <?php break;
                           echo '</table>';
                        
                     }
                  echo '</table>';
                  }
                  ?>
                  <p class="submit" style="clear: both;">
                     <input type="submit" name="Submit"  class="button-primary" value="<?php esc_html_e('Update Settings', 'ppd'); ?>" />
                     <input type="hidden" name="kaya-settings-submit" value="Y" />
                  </p>
               </form>
            </div>
         </div>
         <?php
      }
   }
   // Options data object
   new Kaya_Taxonomy_Cpt_Fields_Settings;

   // initilizing options as a globally, just call  global  $kaya_options; where you need options
   function kaya_options(){
      global $kaya_options;
      $kaya_options = (object) get_option( "kaya_options" );
      return $kaya_options;
   }
   kaya_options();
 ?>