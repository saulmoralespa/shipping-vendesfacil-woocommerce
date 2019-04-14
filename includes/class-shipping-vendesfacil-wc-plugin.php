<?php


class Shipping_VendesFacil_WC_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
    }

    public function run_vendesfacil_wc()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Shipping VendesFacil Woocommerce can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    shipping_coordinadora_wc_cswc_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {

        if (!class_exists('\VendesFacil\Client') && !class_exists('\WebService\Coordinadora'))
            require_once ($this->lib_path . 'vendor/autoload.php');
        require_once ($this->includes_path . 'class-shipping-vendesfacil-wc-admin.php');
        require_once ($this->includes_path . 'class-method-shipping-vendesfacil-wc.php');
        require_once ($this->includes_path . 'class-shipping-vendesfacil-wc.php');
        $this->admin = new Shipping_VendesFacil_WC_Admin();

        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_action( 'shipping_vendesfacil_wc_svwc_schedule',array('Shipping_VendesFacil_WC', 'update_cities'));
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_vendesfacil_wc_add_method') );
        add_filter( 'woocommerce_billing_fields', array($this, 'custom_woocommerce_billing_fields'));

        add_action( 'woocommerce_order_status_changed',array('Shipping_VendesFacil_WC', 'generate_transaction'), 20, 4 );
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_vendesfacil_wc') . '">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a href="https://saulmoralespa.github.io/shipping-vendesfacil-wc/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function shipping_vendesfacil_wc_add_method( $methods ) {
        $methods['shipping_vendesfacil_wc'] = 'WC_Shipping_Method_Shipping_VendeFacil_WC';
        return $methods;
    }

    public function custom_woocommerce_billing_fields($fields)
    {
        if (!$fields['billing_dni']) {
            $fields['billing_dni'] = array(
                'label' => __('Documento Nacional de Identidad'),
                'placeholder' => _x('Su DNI aquí...', 'placeholder'),
                'required' => true,
                'clear' => false,
                'type' => 'number',
                'class' => array('my-css')
            );
        }
        return $fields;
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('shipping-vendesfacil', $message);
    }
}