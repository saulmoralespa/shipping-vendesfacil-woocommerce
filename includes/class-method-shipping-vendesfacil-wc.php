<?php


class WC_Shipping_Method_Shipping_VendeFacil_WC extends WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id                 = 'shipping_vendesfacil_wc';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'VendesFácil' );
        $this->method_description = __( 'VendesFácil transporte' );
        $this->title              = __( 'VendesFácil' );

        $this->init_form_fields();
        $this->init_settings();

        $this->debug = $this->get_option( 'debug' );
        $this->isTest = (bool)$this->get_option( 'environment' );

        $this->client_id = $this->get_option( 'client_id' );
        $this->secret = $this->get_option( 'secret' );
        $this->identification_number = $this->get_option( 'identification_number' );
        $this->phone_sender = $this->get_option( 'phone_sender' );
        $this->city_sender = $this->get_option('city_sender');

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_'.strtolower(get_class($this)), array( 'Shipping_VendesFacil_WC', 'confirmation_ipn' ) );
    }

    public function is_available($package)
    {
        return parent::is_available($package)
            && $this->client_id
            && $this->secret
            && $this->identification_number
            && $this->phone_sender
            && $this->city_sender;
    }

    /**
     * Init the form fields for this shipping method
     */
    public function init_form_fields()
    {
        $this->form_fields = include( dirname( __FILE__ ) . '/admin/settings.php' );
    }

    public function admin_options()
    {
        ?>
        <h3><?php echo $this->title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php
            if (!empty($this->client_id) && !empty($this->secret) && !empty($this->city_sender))
                Shipping_VendesFacil_WC::test_conection();

            $this->generate_settings_html();
            ?>
        </table>
        <?php
    }


    public function calculate_shipping( $package = array() )
    {
        global $woocommerce;
        $state_destination = $package['destination']['state'];
        $city_destination  = $package['destination']['city'];
        $items = $woocommerce->cart->get_cart();

        $cart_prods = array();

        foreach ( $items as $item => $values ) {
            $_product = wc_get_product( $values['data']->get_id() );

            if ( !$_product->get_weight() || !$_product->get_length()
                || !$_product->get_width() || !$_product->get_height() )
                break;
            $cart_prods[] = array(
                'alto'     => $_product->get_height(),
                'ancho'    => $_product->get_width(),
                'largo'    => $_product->get_length(),
                'peso'     => $_product->get_weight(),
                'unidades' => $values['quantity']
            );
        }

        if (empty($cart_prods) &&  $this->debug === 'yes')
            shipping_vendesfacil_wc_svwc()->log('All products have to have a weight, a width, a lenght, and a height, otherwise this shipping method can not generate a valid rate');

        $apply_cost = false;

        if ( ! empty( $cart_prods ) && 'CO' === $package['destination']['country']
            && $state_destination && $city_destination ) {

            $result_destination = Shipping_VendesFacil_WC::destination_code($state_destination, $city_destination);

            if ( ! empty( $result_destination ) ) {

                $params = array(
                    'pais_origen' => 'CO',
                    'ciudad_origen' => $this->city_sender,
                    'pais_destino' => 'CO',
                    'ciudad_destino' => $result_destination->codigo,
                    'valoracion' => WC()->cart->subtotal,
                    'detalle' => $cart_prods
                );

                if ($this->debug === 'yes')
                    shipping_vendesfacil_wc_svwc()->log($params);

                $data = Shipping_VendesFacil_WC::cotizar($params);

                if (isset($data)){
                    $apply_cost = true;
                    $rate       = array(
                        'id'      => $this->id,
                        'label'   => $this->title,
                        'cost'    => $data->total,
                        'package' => $package
                    );
                }
            }
        }

        if ( $apply_cost ) {
            $this->add_rate( $rate );
        } else {
            apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );
        }
    }

}