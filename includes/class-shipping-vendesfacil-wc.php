<?php

use Coordinadora\WebService;
use VendesFacil\Client;

class Shipping_VendesFacil_WC extends WC_Shipping_Method_Shipping_VendeFacil_WC
{

    public $vendesFacil;

    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->vendesFacil = new Client($this->client_id, $this->secret);
        $this->vendesFacil->sandbox($this->isTest);
    }

    public function update_cities()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shipping_coordinadora_cities';
        $sql = "DELETE FROM $table_name";
        $wpdb->query($sql);

        try{
            $cities = $cities = WebService::Cotizador_ciudades();
            foreach ($cities->item as  $city){

                if ($city->estado == 'activo'){
                    $name = explode(' (', $city->nombre);
                    $name = ucfirst(mb_strtolower($name[0]));
                    $wpdb->insert(
                        $table_name,
                        array(
                            'nombre' => $name,
                            'codigo' => $city->codigo,
                            'nombre_departamento' => $city->nombre_departamento
                        )
                    );
                }
            }
        }catch (\Exception $exception){
            shipping_vendesfacil_wc_svwc()->log($exception->getMessage());
        }

    }

    public static function test_conection()
    {
        $instance = new self();

        $products = [];

        $products[] = [
            'alto' => 25,
            'ancho' => 10,
            'largo' => 15,
            'peso' => 2,
            'unidades' => 1
        ];

        $params = [
            'pais_origen' => 'CO',
            'ciudad_origen' => '05001000',
            'pais_destino' => 'CO',
            'ciudad_destino' => '05266000',
            'valoracion' => '10000',
            "detalle" => $products
        ];

        try{
            $instance->vendesFacil->quote($params);
        }
        catch (\Exception $exception){
            shipping_vendesfacil_wc_svwc_notices( $exception->getMessage() );
        }
    }

    public static function destination_code($state, $city)
    {
        global $wpdb;
        $table_name        = $wpdb->prefix . 'shipping_coordinadora_cities';

        $countries_obj        = new WC_Countries();
        $country_states_array = $countries_obj->get_states();
        $state_name           = $country_states_array['CO'][ $state ];
        $state_name           = self::short_name_location($state_name);

        $query = "SELECT codigo FROM $table_name WHERE nombre_departamento='$state_name' AND nombre='$city'";

        $result = $wpdb->get_row( $query );

        return $result;

    }

    public static function cotizar($params)
    {
        $res = null;

        try{
            $instance = new self();
            $res = $instance->vendesFacil->quote($params);
        }
        catch (\Exception $exception){
            shipping_vendesfacil_wc_svwc_notices( $exception->getMessage() );
        }
        return $res;
    }

    public function generate_transaction($order_id, $old_status, $new_status, $order)
    {
        $instance = new self();

        global $wpdb;
        $table_name_vendes_facil = $wpdb->prefix . 'coordinadora_vendes_facil';

        if( !$order->has_shipping_method($instance->id) )
            return;
        $txid = get_post_meta($order_id, 'txid_vendesfacil', true);

        if (empty($txid) &&  $new_status === 'processing'){

            $res = $instance->transaction($order);

            if (is_null($res))
                return;

            $wpdb->insert(
                $table_name_vendes_facil,
                array(
                    'order_id' => $order_id,
                    'txid' => $res->txid
                )
            );

            $txid_note = sprintf( __( 'Url de pago transporte VendeFácil <a target="_blank" href="%1$s">clic aquí</a>.' ), $res->url );
            update_post_meta($order_id, 'txid_vendesfacil', $res->txid);
            $order->add_order_note($txid_note);
        }

    }

    public function transaction($order)
    {
        $instance = new self();

        $nombre_destinatario = $order->get_shipping_first_name() ? $order->get_shipping_first_name() .
            " " . $order->get_shipping_last_name() : $order->get_billing_first_name() .
            " " . $order->get_billing_last_name();
        $direccion_destinatario = $order->get_shipping_address_1() ? $order->get_shipping_address_1() .
            " " . $order->get_shipping_address_2() : $order->get_billing_address_1() .
            " " . $order->get_billing_address_2();
        $state = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        $ciudad_destinatario = self::destination_code($state, $city);
        $email_destinatario = $order->get_billing_email();
        $phone = $order->get_billing_phone();
        $total_shipping = $order->get_total_shipping();
        $order_id = $order->get_id();
        $dni = get_post_meta( $order_id, '_billing_dni', true );

        $products = [];

        foreach ( $order->get_items() as $item ) {
            $_product = wc_get_product( $item['product_id'] );

            $products[] = [
                'nombre' => $_product->get_name(),
                'referencia' => !empty($_product->get_sku()) ? $_product->get_sku() : $_product->get_slug(),
                'imagen' => wp_get_attachment_url($_product->get_image_id()),
                'precio' => $_product->get_price(),
                'por_impuesto' => 19,
                'alto'     => $_product->get_height(),
                'ancho'    => $_product->get_width(),
                'largo'    => $_product->get_length(),
                'peso'     => $_product->get_weight(),
                'unidades' => $item['quantity']
            ];

        }

        $params = [
            "ip_host" => $instance->getIP(),
            "ip_cliente" => $instance->getIP(),
            "referencia" => $order_id,
            "moneda" => "COP",
            "base_devolucion_impuestos" => 0,
            "total_impuestos" => 0,
            "total" => $total_shipping,
            "total_transporte" => $total_shipping,
            "descripcion" => "Compra en " . get_bloginfo( 'name' ),
            "fechahora_expiracion" => "",
            "formas_pago_habilitadas" => [],
            "comprador" => [
                "correo_electronico" => $email_destinatario,
                "identificacion" => $dni,
                "nombre" => $nombre_destinatario,
                "direccion" => $direccion_destinatario,
                "telefono" => $phone,
                "pais" => "Colombia",
                "departamento" => $state,
                "ciudad" => $city,
                "codigo_postal" => ""
            ],
            "transporte" => true,
            "destinatario" => [
                "nombre" => $nombre_destinatario,
                "identificacion" => $dni,
                "direccion" => $direccion_destinatario,
                "telefono" => $phone,
                "codigo_pais" => "CO",
                "codigo_ciudad" => $ciudad_destinatario->codigo,
                "codigo_postal" =>""
            ],
            "detalle" => $products,
            "return_url" => get_bloginfo( 'url' ),
            "success_url" => get_bloginfo( 'url' ),
            "cancel_url" => get_bloginfo( 'url' ),
            "ipn_url" => $instance->getUrlNotify(),
            'correos_deshabilitados' => []
        ];

        try{
            $data = $instance->vendesFacil->transaction($params);
            return $data;
        }
        catch (\Exception $exception){
            shipping_vendesfacil_wc_svwc()->log($exception->getMessage());
        }

        return null;
    }

    public static function short_name_location($name_location)
    {
        if ( 'Valle del Cauca' === $name_location )
            $name_location =  'Valle';
        return $name_location;
    }

    public function getIP()
    {
        return ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '::' ||
            !preg_match('/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m',
                $_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
    }

    public function getUrlNotify()
    {
        $url = trailingslashit(get_bloginfo( 'url' )) . trailingslashit('wc-api') . strtolower(get_parent_class($this));
        return $url;
    }

    public function confirmation_ipn()
    {
        global $wpdb;
        $table_name_vendes_facil = $wpdb->prefix . 'coordinadora_vendes_facil';
        $instance = new self();

        $body = file_get_contents('php://input');
        parse_str($body, $data);

        shipping_vendesfacil_wc_svwc()->log('data received');

        shipping_vendesfacil_wc_svwc()->log($data);

        if(isset($data['estado_validacion']) && $data['estado_validacion'] === 'aprobado'){

            $txid = $data['txid'];

            $query = "SELECT order_id FROM $table_name_vendes_facil WHERE txid='$txid'";

            $result = $wpdb->get_row( $query );

            shipping_vendesfacil_wc_svwc()->log('data processing');

            shipping_vendesfacil_wc_svwc()->log($result);


            if (!isset($result->order_id))
                return;

            $order_id = $result->order_id;
            $order = wc_get_order($order_id);
            $items = $order->get_items();
            $direccion_remitente = get_option( 'woocommerce_store_address' ) .
                " " .  get_option( 'woocommerce_store_address_2' ) .
                " " . get_option( 'woocommerce_store_city' );

            $params = array(
                "txid" => $txid,
                "unidades_empaque" => $instance->getQuantityProduct($items),
                "referencia" => $order->get_id(),
                "identificacion" => $instance->identification_number,
                "nombre" => get_bloginfo( 'name' ),
                "direccion" => $direccion_remitente,
                "telefono" => $instance->phone_sender,
                "codigo_pais" => "CO",
                "codigo_ciudad" => $instance->city_sender,
                "contenido" => $instance->nameProducts($items)
            );

            try{
                $instance->vendesFacil->documents($params);
                $instance->vendesFacil->collection($txid);
            }catch (\Exception $exception){
                shipping_vendesfacil_wc_svwc()->log($exception->getMessage());
            }

        }

        header("HTTP/1.1 200 OK");
    }


    public function getQuantityProduct($items)
    {
        $item_quantity = 0;

        foreach ($items as $item_id => $item_data){
            $item_quantity += $item_data->get_quantity();
        }

        return $item_quantity;
    }

    public function nameProducts($items)
    {
        $namesProducts = array();

        foreach ($items as $item){
            $namesProducts[] = $item->get_name();
        }

        $names = implode(",",  $namesProducts);
        return $names;
    }
}