<?php 
namespace Cloudoki\Flowdrive;

use Cloudoki\Flowdrive\BaseLoader;
use Cloudoki\Flowdrive\Nav;
use Cloudoki\Flowdrive\API;
use Cloudoki\Flowdrive\Admin;

class Flowdrive extends BaseLoader
{
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	static $version = "0.1.0";
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = [];
		$this->filters = [];
		
		# Components
		$this->nav = new Nav ();
		$this->api = new API ();
		$this->admin = new Admin ();
		
		# Define hooks
		$this->enqueue ();
		$this->admin_hooks ();
		$this->admin_ajax_routing ();
		$this->public_hooks ();
	}
	
	/**
	 * Register all of the hooks related to files
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function enqueue () {

		$this->add_action ('admin_enqueue_scripts', $this, 'enqueue_files' );
	}
	
	/**
	 * Register the files for Flowdrive.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_files ()
	{
		// Flowdrive
		wp_register_style( 'flowdrive_admin_css', plugin_dir_url( __DIR__ ) . 'assets/css/flowdrive-admin.css', false, $this->version );
		wp_enqueue_style( 'flowdrive_admin_css' );
		
		// wp_enqueue_script( $this, plugin_dir_url( __FILE__ ) . 'js/cloudoki-smmp-admin.js', array( 'jquery' ), $this->version, false );
		
		// Chosen
		wp_register_style( 'chosen_admin_css', plugin_dir_url( __DIR__ ) . '../vendor/drmonty/chosen/css/chosen.min.css', false, $this->version );
		wp_enqueue_style( 'chosen_admin_css' );
		
		wp_register_script( 'chosen_admin_js', plugin_dir_url( __DIR__ ) . '../vendor/drmonty/chosen/js/chosen.jquery.min.js', ['jquery'], $this->version );
		wp_enqueue_script( 'chosen_admin_js' );
		
		// wp_enqueue_script( $this, plugin_dir_url( __DIR__ ) . '../vendor/drmonty/chosen/js/chosen.jquery.min.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function admin_hooks() {
		
		// Actions
		// Load Nav injection
		$this->add_action ('admin_menu', $this->nav, 'plugin_menu' );
		
		// Load Dashboard widget
		$this->add_action ('wp_dashboard_setup', $this->admin, 'dashboard');
		
		// Load Edit Post additions
		// $this->add_action ('add_meta_boxes', $this->admin, 'post_edit' );

		// Load social toggles on submitbox, if the setting is available
		// if (get_option('smmp_view_submitbox'))
		//	$this->add_action ('post_submitbox_misc_actions', $this->admin, 'admin_post_submitbox' );
		
		// On post update/save
		// $this->add_action ('save_post', $this->admin, 'admin_post_submitbox_submit');
		
		
		// Load Expired Account notice
		/*try {$this->admin->validate_accounts (); }
		catch (Exception $e)
		{
			$this->add_action ('admin_notices', $this->admin, 'notice_accounts' );
		}*/
		
		// Filters
		// Prevent inner links for flow-drive images
		$this->add_action ('image_downsize', $this->admin, 'filter_image_downsize', 10, 3);
	}
	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function admin_ajax_routing() {

		// Load API injection
		$this->add_action( 'wp_ajax_flowdrive_get_profile', $this->api, 'getUserProfile');
		$this->add_action( 'wp_ajax_flowdrive_get_base_folders', $this->api, 'getBaseFolders');
		$this->add_action( 'wp_ajax_flowdrive_get_layer', $this->api, 'getLayer');
		$this->add_action( 'wp_ajax_flowdrive_compare', $this->api, 'compare');
		$this->add_action( 'wp_ajax_flowdrive_get_folder_contents', $this->api, 'getFolderContents');
		$this->add_action( 'wp_ajax_flowdrive_post_item', $this->api, 'postItem');
	}
	
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function public_hooks()
	{
	}
}



