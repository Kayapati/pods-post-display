<?php
add_action( 'admin_menu', 'kaya_pcv_export_option_settings' );
	/**
	 * Import and export settings
	 */
	function kaya_pcv_export_option_settings() 
	{
        add_submenu_page('kaya-settings', __('Export','ppd'), __('Export','ppd'), 'edit_theme_options', 'ppd-export', 'kaya_pcv_export_option_page');
        add_submenu_page('kaya-settings', __('Import','ppd'), __('Import','ppd'), 'edit_theme_options', 'ppd-import', 'kaya_pcv_import_option_page');
    }
    
    function kaya_pcv_export_option_page() {
        if (!isset($_POST['ppd-export'])) { ?>
            <div class="wrap">
                <div id="icon-tools" class="icon32"><br /></div>
                <h2><?php esc_html_e('Export Pod CPT Views Settings','ppd'); ?> </h2>
                <p><?php _e('When you click <tt>Dowload Pod CPT Views Settings</tt> button, system will generate a JSON file for you to save on your computer.','ppd'); ?></p>
                <form method='post'>
                    <p class="submit">
                        <?php wp_nonce_field('ppd-options-export'); ?>
                        <input type='submit' name='ppd-export' value='<?php esc_html_e('Dowload Pod CPT Views Settings','ppd'); ?>' class="button"/>
                    </p>
                </form>
            </div>
            <?php
        }
        elseif (check_admin_referer('ppd-options-export')) {
            $blogname = str_replace(" ", "", get_option('blogname'));
            $date = date("m-d-Y");
            $json_name = $blogname."-".$date; // Namming the filename will be generated.
            $options = get_option('kaya_options'); // Get all options data, return array        
                foreach ($options as $key => $value) {
                $value = maybe_unserialize($value);
                $need_options[$key] = $value;
            }
            //$json_file = json_encode($need_options);
           // $need_options['front_page_name'] .= get_option('page_on_front');
            $json_file = json_encode($need_options); // Encode data into json data
            ob_clean();
            echo $json_file;
            header("Content-Type: text/json; charset=" . get_option( 'blog_charset'));
            header("Content-Disposition: attachment; filename=$json_name.json");
            exit();
        }
    }
     function kaya_pcv_import_option_page() {
        WP_Filesystem();
        global $wp_filesystem;
    ?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"><br /></div>
        <h2><?php _e('Import Pods CPT Options', 'ppd'); ?></h2>
        <?php
            if (isset($_FILES['ppd-import']) && check_admin_referer('ppd-import')) {
                if ($_FILES['ppd-import']['error'] > 0) {
                    wp_die("Please Choose Upload json format file");
                }
                else {
                    $file_name = $_FILES['ppd-import']['name']; // Get the name of file
                    $file_path = explode('.', $file_name);
                    $file_ext = end($file_path);
                    $file_size = $_FILES['ppd-import']['size']; // Get size of file
                    /* Ensure uploaded file is JSON file type and the size not over 500000 bytes
                     * You can modify the size you want
                     */
                    if (($file_ext == "json") && ($file_size < 500000)) {
                        $encode_options = $wp_filesystem->get_contents($_FILES['ppd-import']['tmp_name']);
                        $pod_data = json_decode($encode_options, true); 
                        $kaya_options = array();
                         foreach ($pod_data as $key => $opt_val) {
                            $kaya_options[$key] = $opt_val;
                            update_option( 'kaya_options', $kaya_options );
                         }

                        echo "<div class='updated'><p>".__('All options are restored successfully','ppd')."</p></div>";
                    }
                    else {
                        echo "<div class='error'><p>".__('Invalid file or file size too big.','ppd')."</p></div>";
                    }
                }
            }
        ?>
        <p><?php _e('Click Browse button and choose a json file that you backup before.','ppd'); ?> </p>
        <p><?php _e('Press Upload File and Import, WordPress do the rest for you.','ppd'); ?></p>
        <form method='post' enctype='multipart/form-data'>
            <p class="submit">
                <?php wp_nonce_field('ppd-import'); ?>
                <input type='file' name='ppd-import' class="primary-button"  />
                <input type='submit' name='submit' value='<?php _e('Upload File and Import', 'ppd') ?>' class="button"/>
            </p>
        </form>
    </div>
    <?php
}
?>
