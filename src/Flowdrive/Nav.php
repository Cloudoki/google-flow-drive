<?php
namespace Cloudoki\Flowdrive;

use Cloudoki\Flowdrive\Admin;

class Nav
{
	/**
	 * Navigation injection into the admin area.
	 * The Navigation class functions as Plugin Router
	 *
	 * @since    1.0.0
	 */
	public function plugin_menu ()
	{
		
		# Views
		$admin = new Admin ();
		
		add_menu_page( 'Flow Drive', 'Flow Drive', 'manage_options', 'flowdrive', [$admin, 'view_general'], 'dashicons-backup', 28 );
		add_submenu_page( 'flowdrive', 'Flow Drive', 'Flow Drive', 'manage_options', 'flowdrive-general', [$admin, 'view_general']);
		add_submenu_page( 'flowdrive', 'Settings', 'Settings', 'manage_options', 'flowdrive-settings', [$admin, 'view_settings']);
		
		remove_submenu_page('flowdrive', 'flowdrive');
		
		# API
		$api = new API ();
		
		//add_action( 'wp_ajax_get_base_folders', 'getBaseFolders');
		
		//add_submenu_page( 'flowdrive', 'SMMP Publications', 'All Publications', 'manage_options', 'smmp-list', array( $this, 'admin_page_list' ));
		//add_submenu_page( 'smmp', 'SMMP Social Accounts', 'Social Accounts', 'manage_options', 'smmp-accounts', array( $this, 'admin_page_accounts' ));
		//add_submenu_page( 'smmp', 'SMMP Settings', 'SMMP Settings', 'manage_options', 'smmp-settings', array( $this, 'admin_page_settings' ));
		
		
	}

}


?>