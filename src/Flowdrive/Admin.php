<?php
namespace Cloudoki\Flowdrive;

use Cloudoki\Flowdrive\API;

class Admin
{
	public function __construct ()
	{
		$this->mustache = new \Mustache_Engine(
		[
			'loader' => new \Mustache_Loader_FilesystemLoader(__DIR__.'/views')
		]);
	}
	
	
	/**
	 * Flowdrive main page - admin page content.
	 *
	 * @since    1.0.0
	 */
	public function view_general ()
	{
		// Load method
		$method = get_option("flowdrive_method");
		$params = [
			'title'=> get_admin_page_title()
		];
		
		if ($method == 'Compare') $this->view_Compare ($params);
	}

	/**
	 * Flowdrive settings page.
	 *
	 * @since    1.0.0
	 */
	public function view_settings ()
	{
		if ($_GET['updating']) $this->update_settings ();
		
		$params = [
			'page'=> 'flowdrive-settings',
			'title'=> get_admin_page_title(),
			'options'=> [
				"flowdrive_method"=> get_option("flowdrive_method"),
				"flowdrive_basefolder"=> get_option("flowdrive_basefolder"),	
				"flowdrive_md_convert"=> (int) get_option("flowdrive_md_convert"),
				"flowdrive_max_attach"=> (int) get_option("flowdrive_max_attach"),
				"flowdrive_compress"=> (int) get_option("flowdrive_compress"),
				"flowdrive_soft_delete"=> (int) get_option("flowdrive_soft_delete")
			]
		];
		
		# Render template
		echo $this->mustache->loadTemplate('settings')
			->render($params);
	}
	
	/**
	 * Flowdrive update settings.
	 *
	 * @since    1.0.0
	 */
	public function update_settings ()
	{
		# Create basefolder
		if ($_GET["flowdrive_basefolder"] == "new")
		{
			$api = new API();
			
			$basefolder = $api->getFolder ('Wordpress');
			
			if (!$basefolder)
			
				$basefolder = ''; //$api->createFolder ('Wordpress');
		}	
		
		update_option ("flowdrive_method", isset($basefolder)? $basefolder: $_GET["flowdrive_method"]);
		update_option ("flowdrive_basefolder", $_GET["flowdrive_basefolder"]);
		update_option ("flowdrive_md_convert", (int) $_GET["flowdrive_md_convert"]);
		update_option ("flowdrive_max_attach", (int) $_GET["flowdrive_max_attach"]);
		update_option ("flowdrive_compress", (int) $_GET["flowdrive_compress"]);
		update_option ("flowdrive_soft_delete", (int) $_GET["flowdrive_soft_delete"]);
	}
	
	/**
	 * Flowdrive Compare method.
	 *
	 * @since    1.0.0
	 */
	public function view_Compare ($params = [])
	{		
		// One time ony
		
		
		// Manual store
		// $api = new API();
		// $api->storeClient ();	
		
		
		//$file = $api->createFile ('.flowdrive', get_option("flowdrive_basefolder"));
		
		$params = [
			'title'=> get_admin_page_title(),
			"flowdrive_basefolder"=> get_option("flowdrive_basefolder")
		];
		
		
		# Render template
		echo $this->mustache->loadTemplate('method-compare')
			->render($params);
	}
	
}

?>