<?php

/**
 * Plugin Name:       Plugin name
 * Plugin URI:        
 * Description:       
 * Version:           1.0.0
 * Author:            k3x4
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       k3x4-listings-migrate
 * Domain Path:       /languages
 */

namespace k3x4\ListingsMigrate;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'K3X4_LISTINGS_MIGRATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'K3X4_LISTINGS_MIGRATE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// We load Composer's autoload file
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

register_activation_hook( __FILE__, function(){
	utils\Activator::activate();
} );

register_deactivation_hook( __FILE__, function() {
	utils\Deactivator::deactivate();
} );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */

$plugin = new Main();
$plugin->run();