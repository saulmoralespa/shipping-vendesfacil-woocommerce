<?php


class Shipping_VendesFacil_WC_Admin
{
    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'shipping_vendesfacil_wc_svwc_menu'));
        add_action( 'wp_ajax_shipping_vendesfacil_wc_svwc',array($this,'shipping_vendesfacil_wc_svwc_ajax'));
    }

    public function shipping_vendesfacil_wc_svwc_menu()
    {
        add_submenu_page(
            null,
            '',
            '',
            'manage_options',
            'vendesfacil-install-setp',
            array($this, 'vendesfacil_install_step')
        );

        add_action( 'admin_footer', array( $this, 'enqueue_scripts_admin' ) );
    }

    public function vendesfacil_install_step()
    {
        ?>
        <div class="wrap about-wrap">
            <h3><?php _e( 'Actualicemos y estaremos listos para iniciar :)' ); ?></h3>
            <button class="button-primary shipping_vendesfacil_update_cities" type="button">Actualizar</button>
        </div>
        <?php
    }

    public function shipping_vendesfacil_wc_svwc_ajax()
    {
        do_action('shipping_vendesfacil_wc_svwc_schedule');
        die();
    }

    public function enqueue_scripts_admin()
    {
        wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@8', array('jquery'), false, true );
        wp_enqueue_script( 'shipping_vendesfacil_wc_svwc', shipping_vendesfacil_wc_svwc()->plugin_url . 'assets/js/config.js', array( 'jquery' ), shipping_vendesfacil_wc_svwc()->version, true );
        wp_localize_script( 'shipping_vendesfacil_wc_svwc', 'shippingVendesFacil', array(
            'urlConfig' => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_vendesfacil_wc')
        ) );
    }
}