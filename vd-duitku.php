<?php
/**
 * Plugin Name:       VD Duitku
 * Plugin URI:        https://velocitydeveloper.com
 * Description:       Stand-alone payment gateway Duitku.
 * Version:           1.0.0
 * Author:            Velocity
 * Author URI:        https://velocitydeveloper.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vd-duitku
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VD_DUITKU_VERSION', '1.0.0');
define('VD_DUITKU_PLUGIN_FILE', __FILE__);
define('VD_DUITKU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VD_DUITKU_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once VD_DUITKU_PLUGIN_DIR . 'includes/class-vd-duitku-activator.php';
require_once VD_DUITKU_PLUGIN_DIR . 'includes/class-vd-duitku.php';

register_activation_hook(__FILE__, array('VD_Duitku_Activator', 'activate'));

function run_vd_duitku()
{
    $plugin = new VD_Duitku();
    $plugin->run();
}

run_vd_duitku();
