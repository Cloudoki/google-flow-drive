<?php
require_once 'vendor/autoload.php';

/*include "src/Flowdrive/BaseLoader.php";
include	"src/Flowdrive/Activator.php";
include "src/Flowdrive/Flowdrive.php";
include "src/Flowdrive/lib/Nav.php";
include "src/Flowdrive/lib/Admin.php";

function require_files ($path)
{
	$files = scandir($path);
	
	foreach ($files as $file)
		if(substr ($file, 0, 1) != '.')
		{
			$filepath = $path . "/" . $file;
			
			if (is_file ($filepath)) require_once $filepath;
			else if (is_dir ($filepath)) require_files ($filepath);			
		}
}

require_files (__DIR__ . "/vendor/google/apiclient/src/Google/");*/


use Cloudoki\Flowdrive\Activator;
use Cloudoki\Flowdrive\Flowdrive;

/**
 * The plugin bootstrap file
 *
 * @link              https://wordpress.org/plugins/google-flow-drive/
 * @since             1.0.0
 * @package           Flowdrive
 *
 * @wordpress-plugin
 * Plugin Name:       Google Flow Drive
 * Plugin URI:        https://wordpress.org/plugins/google-flow-drive/
 * Description:       The Google Flow Drive merges Google Drive with Wordpress and Ghost into an hybrid application.
 * Version:           0.1
 * Author:            Cloudoki
 * Author URI:        http://cloudoki.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (! defined ('WPINC')) die;

// Define the Google Client settings
define('APPLICATION_NAME', 'Drive API PHP Quickstart');

define('CREDENTIALS_PATH', '~/.credentials/drive-php-quickstart.json');

define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret_763043149136-l5rg0tmia12r8aefqoukb1jcq876ejfj.apps.googleusercontent.com.json');

define('SCOPES', 'https://www.googleapis.com/auth/drive');

/**
 *	The code that runs during plugin activation.
 */
function activate_flowdrive()
{
	Cloudoki\Flowdrive\Activator::activate();
}

/**
 *	The code that runs during plugin de-activation.
 */
function deactivate_flowdrive()
{
	Cloudoki\Flowdrive\Activator::deactivate();
}

register_activation_hook( __FILE__, 'activate_flowdrive' );
register_deactivation_hook( __FILE__, 'deactivate_flowdrive' );


/**
 * Begin execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_flowdrive() {

	$plugin = new Flowdrive ();
	$plugin->run();

}
run_flowdrive();
