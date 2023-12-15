<?php
/**
 * @wordpress-plugin
 * Plugin Name: AsapWo
 * Plugin URI: https://corellainnovations.com/asapwo
 * Description: Synchronize SAP inventory with WooCommerce inventory.
 * Version: 1.1
 * Author: Jhonatan Corella
 * Author URI: https://corellainnovations.com/jhonatancorella
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 1.1
 * Requires PHP: 7.4
 *
 * @package AsapWo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class AsapWo
 */
class AsapWo {
    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'create_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_styles_and_scripts']);
    }

    /**
     * Menu
     */
    public static function create_menu() {
        $icon_url = plugin_dir_url(__FILE__) . '/assets/img/AsapWo.png';

        add_menu_page(
            'AsapWo',
            'AsapWo',
            'manage_options',
            'asapwo_dashboard',
            [__CLASS__, 'redirect_to_external_url'],
            $icon_url,
            6
        );

        // Add sub-menu items as children of the 'asapwo_dashboard' menu
        self::add_submenu_page('Connect SAP', 'asapwo_connect_sap', 'render_connect_sap_page');
        self::add_submenu_page('WooCommerce connection', 'asapwo_woocommerce', 'render_woocommerce_page');
        self::add_submenu_page('Synchronize', 'asapwo_synchronize', 'render_synchronize_page');


    }

    /**
     * Add submenu page
     */
    private static function add_submenu_page($page_title, $menu_slug, $callback) {
        add_submenu_page('asapwo_dashboard', $page_title, $page_title, 'manage_options', $menu_slug, [__CLASS__, $callback]);
    }

    /**
     * Redirect to an external URL
     */
    public static function redirect_to_external_url() {
        wp_redirect('https://corellainnovations.com/asapwo');
        exit();
    }

    /**
     * Enqueue styles and scripts for AsapWo plugin
     */
    public static function enqueue_styles_and_scripts() {
        wp_enqueue_style('asapwo_style', plugin_dir_url(__FILE__) . 'assets/css/styles.css');
        wp_enqueue_script('asapwo_script', plugin_dir_url(__FILE__) . 'assets/js/scripts.js');
    }

    /**
     * Render the 'Connect SAP' page
     */
    public static function render_connect_sap_page() {
        AsapWoConnectSap::render_page();
    }

    /**
     * Render the 'WooCommerce Credentials' page
     */
    public static function render_woocommerce_page() {
        AsapWoWooCommerce::render_page();
    }
        /**
     * Render the 'Real Synchronize' page
     */
    public static function render_synchronize_page() {
        RealSynchronization::synchronizePage();
    }
    /**
     * Render the dashboard page
     */
    public static function render_dashboard() {
        if (isset($_POST['asapwo_save_settings'])) {
            self::save_settings();
        } elseif (isset($_POST['asapwo_perform_update'])) {
            self::perform_update();
        }

        self::render_dashboard_page();
    }

    /**
     * Perform plugin update
     */
    private static function perform_update() {
        // Implement your update logic here
    }

    /**
     * Render the dashboard page
     */
    private static function render_dashboard_page() {
        // Implement your dashboard rendering logic here
    }
}

$r_message = "";



// Include General Plugin Components
require_once __DIR__ . '/src/asapwo_connection_sap.php';
require_once __DIR__ . '/src/asapwo_sync.php';
require_once __DIR__ . '/src/asapwo_update.php';
require_once __DIR__ . '/src/asapwo_woo.php';

// Initialize the AsapWo plugin
AsapWo::init();
