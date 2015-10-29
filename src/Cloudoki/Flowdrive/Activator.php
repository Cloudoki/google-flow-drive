<?php 
namespace Cloudoki\Flowdrive;

class Activator
{
	/**
	 * Add Flow Drive core functionalities.
	 * Activator adds Flow Drive basic option defaults.
	 */
	public static function activate ()
	{
		// Create or update options
		self::generate_options ();
	}
	
	/**
	 * Disable Flow Drive core functionalities.
	 */
	public static function deactivate ()
	{
		// Create or update db table
		// self::generate_table ();
		
		// Create or update options
		// self::generate_options ();
	}
	
	/**
	 *	Add The Flow Drive Wordpress Options
	 */
	public static function generate_options ()
	{
		// View options
		add_option("flowdrive_method", 'compare'); // Compare | Automatic | Master | Slave
		
		add_option("flowdrive_md_convert", '1'); // 1 | 0
		
		add_option("flowdrive_max_attach", '{}'); // Define formats and cpt's first
		
		add_option("flowdrive_soft_delete", '1'); // 1 | 0
	}
}
