<?php

use PHPUnit\Framework\TestCase;
use VendesFacil\Client;

class VendeFacilTest extends TestCase
{
    public $vendeFacil;

    public function setUp()
    {
        $clientId = '3911800cc3734f2a93ded2003ae9255e';
        $secret = '27f7005a23';

        $this->vendeFacil = new Client($clientId, $secret);
        $this->vendeFacil->sandbox(true);
    }

    public function testGetToken()
    {
        $token = $this->vendeFacil->getAccesToken();
        $this->assertObjectHasAttribute('token', $token);
    }

    public function testquote()
    {

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

        $data = $this->vendeFacil->quote($params);
        $this->assertObjectHasAttribute('total', $data);
    }

    public function testTransaction()
    {

        $products = [];

        $products[] = [
            'nombre' => 'Nombre del Producto 1',
            'referencia' => 'PRD-0001',
            'imagen' => 'https://www.tiendavendesfacil.com/imagen-producto.jpg',
            'precio' => 150000,
            'por_impuesto' => 19,
            'alto' => 25,
            'ancho' => 10,
            'largo' => 15,
            'peso' => 2,
            'unidades' => 1
        ];

        $products[] = [
            'nombre' => 'Nombre del Producto 2',
            'referencia' => 'PRD-0002',
            'imagen' => 'https://www.tiendavendesfacil.com/imagen-producto.jpg',
            'precio' => 150000,
            'por_impuesto' => 19,
            'alto' => 25,
            'ancho' => 10,
            'largo' => 15,
            'peso' => 2,
            'unidades' => 1
        ];

        $params = [
            "ip_host" => "0.0.0.0",
            "ip_cliente" => "0.0.0.0",
            "referencia" => "123",
            "moneda" => "COP",
            "base_devolucion_impuestos" => 0,
            "total_impuestos" => 0,
            "total" => 100000,
            "total_transporte" => 150000,
            "descripcion" => "Compra en yourshop.com",
            "fechahora_expiracion" => "",
            "formas_pago_habilitadas" => [],
            "comprador" => [
                "correo_electronico" => "juan.perez@tiendavendesfacil.com",
                "identificacion" => "000555",
                "nombre" => "Juan Perez",
                "direccion" => "",
                "telefono" => "5554455",
                "pais" => "Colombia",
                "departamento" => "Antioquia",
                "ciudad" => "Bogotá",
                "codigo_postal" => ""
            ],
            "transporte" => true,
            "destinatario" => [
                "nombre" => "Juan Perez",
                "identificacion" => "000555",
                "direccion" => "Cll 10 # 83 - 17",
                "telefono" => "5554455",
                "codigo_pais" => "CO",
                "codigo_ciudad" => "11001000",
                "codigo_postal" =>""
            ],
            "detalle" => $products,
            "return_url" => "https://dummy.vendesfacil.com/carrito?id_pedido=123",
            "success_url" => "https://dummy.vendesfacil.com/pedido-finalizado?id_pedido=!23",
            "cancel_url" => "https://dummy.vendesfacil.com/pedido-cancelado?id_pedido=123",
            "ipn_url" => "https://dummy.vendesfacil.com/ipn.php",
            'correos_deshabilitados' => []
        ];

        $data = $this->vendeFacil->transaction($params);
        $this->assertObjectHasAttribute('txid', $data);
    }

    public function testGetTransaction()
    {

        $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";

        $data = $this->vendeFacil->getTransaction($txid);
        $this->assertObjectHasAttribute('estado', $data);
    }

    public function testDocuments()
    {

        $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";

        $params = [
            "txid" => $txid,
            "unidades_empaque" => 1,
            "referencia" => "factura-123",
            "identificacion" => "1234567890",
            "nombre" => "Dummy Tienda VendesFacil",
            "direccion" => "Cll 10 # 10 - 10",
            "telefono" => "5554455",
            "codigo_pais" => "CO",
            "codigo_ciudad" => "05001000",
            "contenido" => "Descripción de la venta"
        ];

        $data = $this->vendeFacil->documents($params);
        $this->assertObjectHasAttribute('codigo_remision', $data);

    }

    public function testCollection()
    {
        $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";

        $data = $this->vendeFacil->collection($txid);
        $this->assertObjectHasAttribute('codigo_remision', $data);
    }

    public function testGetStatus()
    {
        $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";

        $data = $this->vendeFacil->getStatus($txid);
        $this->assertObjectHasAttribute('estado', $data);

    }

}