<?php
/**
 * Plugin Name: Shipping VendesFacil Woocommerce
 * Description: Shipping VendesFacil  Woocommerce is available for Colombia
 * Version: 1.0.0
 * Author: Saul Morales Pacheco
 * Author URI: https://saulmoralespa.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC tested up to: 3.5
 * WC requires at least: 2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!defined('SHIPPING_VENDESFACIL_WC_SVWC_VERSION')){
    define('SHIPPING_VENDESFACIL_WC_SVWC_VERSION', '1.0.0');
}

add_action( 'plugins_loaded', 'shipping_vendesfacil_wc_svwc_init', 0 );

function shipping_vendesfacil_wc_svwc_init(){
    if ( !shipping_vendesfacil_wc_svwc_requirements() ){
        return;
    }

    shipping_vendesfacil_wc_svwc()->run_vendesfacil_wc();

    if ( get_option( 'shipping_vendesfacil_wc_svwc_redirect', false ) ) {
        delete_option( 'shipping_vendesfacil_wc_svwc_redirect' );
        wp_redirect( admin_url( 'admin.php?page=vendesfacil-install-setp' ) );
    }
}

function shipping_vendesfacil_wc_svwc_notices( $notice ){
    ?>
    <div class="error notice is-dismissible">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function shipping_vendesfacil_wc_svwc_requirements(){

    if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_vendesfacil_wc_svwc_notices( 'Shipping VendesFacil Woocommerce: Requiere que la versión de php 5.6 o superior' );
                }
            );
        }
        return false;
    }

    if (!function_exists("curl_init")){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_vendesfacil_wc_svwc_notices( 'Shipping VendesFacil Woocommerce: Requiere que la extensión cURL se encuentre instalada' );
                }
            );
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_vendesfacil_wc_svwc_notices( 'Shipping VendesFacil Woocommerce: Requiere que se encuentre instalado y activo el plugin Woocommerce' );
                }
            );
        }
        return false;
    }

    if ( ! in_array(
        'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    shipping_vendesfacil_wc_svwc_notices( 'Shipping VendesFacil Woocommerce: Requiere que se encuentre instalado y activo el plugin: Departamentos y ciudades de Colombia para Woocommerce' );
                }
            );
        }
        return false;
    }

    $woo_countries   = new WC_Countries();
    $default_country = $woo_countries->get_base_country();

    if ( ! in_array( $default_country, array( 'CO' ), true ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    $country = 'Shipping VendesFacil Woocommerce: Requiere que el país donde se encuentra ubicada la tienda sea Colombia '  .
                        sprintf(
                            '%s',
                            '<a href="' . admin_url() .
                            'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' .
                            'Click para establecer</a>' );
                    shipping_coordinadora_wc_cswc_notices( $country );
                }
            );
        }
        return false;
    }

    return true;
}

function shipping_vendesfacil_wc_svwc(){
    static $plugin;
    if ( ! isset( $plugin ) ) {
        require_once 'includes/class-shipping-vendesfacil-wc-plugin.php';
        $plugin = new Shipping_VendesFacil_WC_Plugin( __FILE__, SHIPPING_VENDESFACIL_WC_SVWC_VERSION );
    }
    return $plugin;
}

function activate_shipping_vendesfacil_wc_svwc() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'shipping_coordinadora_cities';
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		nombre varchar(60) NOT NULL,
		codigo varchar(8) NOT NULL,
		nombre_departamento varchar(60) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
    update_option( 'shipping_vendesfacil_wc_svwc_version', SHIPPING_VENDESFACIL_WC_SVWC_VERSION );
    add_option( 'shipping_vendesfacil_wc_svwc_redirect', true );
    wp_schedule_event( time(), 'daily', 'shipping_vendesfacil_wc_svwc_schedule' );
}

function deactivation_shipping_vendesfacil_wc_svwc() {
    wp_clear_scheduled_hook( 'shipping_vendesfacil_wc_svwc_schedule' );
}

register_activation_hook( __FILE__, 'activate_shipping_vendesfacil_wc_svwc' );
register_deactivation_hook( __FILE__, 'deactivation_shipping_vendesfacil_wc_svwc' );