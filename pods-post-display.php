<?php
/**
 * Plugin Name:Pods Post Display
 * Plugin URI: http://themeforest.net/user/kayapati
 * Description: Pods Add-on allow you to display posts custom fields data and Taxonomy layout styles like number of column, thumbnail sizes and number of posts to display.
 * Version: 1.0.4
 * Author: Venisha IT Team
 * Author URI: http://themeforest.net/user/kayapati
 * Text Domain: ppd
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
* Main PODs CPT Views Classes
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/automatic-updater.php';  
new WPFDGitHubPluginUpdater(__FILE__,'Kayapati', 'pods-post-display', ' 18d1d2beed69dc6988a784450ce32bee131fb20a');


if( !class_exists('Kaya_Pods_Post_Display') ){
	class Kaya_Pods_Post_Display
	{
		function __construct(){
			add_action( 'wp_enqueue_scripts', array( &$this, 'kaya_pods_cpt_enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'kaya_pods_cpt_admin_scripts' ) );
			add_action('plugins_loaded', array(&$this,'kaya_pods_cpt_plugin_textdomain'));
			$this->kaya_pods_cpt_include_files();
		}
		/**
		 * Load all files and functions
		 */
		function kaya_pods_cpt_include_files(){
			define( 'KAYA_PCV_PLUGIN_PATH',plugin_dir_path( __FILE__ ) );
			define( 'KAYA_PCV_PLUGIN_URL',plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );
			require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
			require_once plugin_dir_path( __FILE__ ) . 'includes/taxonomy-cpt-fields-settings.php';
			require_once plugin_dir_path( __FILE__ ) . 'widgets/pod-cpt-post-grid-view.php';
			require_once plugin_dir_path( __FILE__ ) . 'widgets/pod-cpt-post-slider.php';
			require_once plugin_dir_path( __FILE__ ) . 'widgets/pod-cpt-front-end-form.php';
			require_once plugin_dir_path( __FILE__ ) . 'widgets/pod-cpt-front-end-view-edit-forms.php';
			require_once plugin_dir_path( __FILE__ ) . 'widgets/advanced_search.php';
			require_once plugin_dir_path( __FILE__ ) . 'includes/mr-image-resize.php';
			require_once plugin_dir_path( __FILE__ ) . 'includes/search.php'; 			
			
		}
		/**
		 * Loading Front End Css & Js Files
		 */
		public function kaya_pods_cpt_enqueue_styles() {
			wp_enqueue_style('ppd-jquery-ui-css', plugins_url('css/jquery-ui-css.css', __FILE__));
			wp_enqueue_script('masonry');
			wp_enqueue_style('ppd-styles', plugins_url('css/styles.css', __FILE__));
			wp_enqueue_style('owl.carousel.min', plugins_url('css/owl.carousel.min.css', __FILE__));
			wp_enqueue_script( 'owl.carousel.min', plugin_dir_url( __FILE__ ) . 'js/owl.carousel.min.js', array(),'', 'true' );
			wp_enqueue_script('pod-cpt-scripts', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array(),'', 'true' );
			wp_enqueue_style('ppd-responsive', plugins_url('css/responsive.css', __FILE__));
			wp_localize_script( 'jquery', 'kaya_ajax_url', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-slider');
		}
		
		/**
		 * Loading Admin Css & Js Files
		 */
		public function kaya_pods_cpt_admin_scripts(){
			wp_enqueue_script('wp-color-picker');
			wp_enqueue_script( 'ppd-admin-scripts', plugin_dir_url( __FILE__ ) . 'js/admin-scripts.js', array(),'', 'true' );
			wp_enqueue_style('ppd-admin-styles', plugins_url('css/admin-styles.css', __FILE__));
		}
		
		/** 
		* Load language Translation Text Domain
		*/
	    public  function kaya_pods_cpt_plugin_textdomain() {
	        $locale = apply_filters( 'plugin_locale', get_locale(), 'ppd' );
	        load_textdomain( 'ppd', trailingslashit( WP_LANG_DIR ) . '/' . $locale . '.mo' );
	        load_plugin_textdomain( 'ppd', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	    }		
	}
}
$kaya_pods_taxonomy = new Kaya_Pods_Post_Display();  ?>
