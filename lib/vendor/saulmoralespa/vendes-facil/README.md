VendesFacil API PHP
============================================================

## Installation

Use composer package manager

```bash
composer require saulmoralespa/vendes-facil
```

#### Bootstrapping autoloader and instantiating a client

```php
// ... please, add composer autoloader first
include_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//or load  path
//require_once ("/path/to/vendes-facil/src/autoload.php");

// import client class
use VendesFacil\Client;

// instantiate

$clientId = '';
$secret = '';

$vendeFacil = Client($clientId, $secret);
$vendeFacil->sandbox(true); //true for tests or false for production
```

### Get token

```php
try{
    $data = $vendeFacil->getAccesToken();
    return $data->token;
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```

### Quote

```php
try{
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
    
    $data = $vendeFacil->quote($params);
    return $data->total;
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```


### Transaction

```php
try{
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

    $data = $vendeFacil->transaction($params);
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```

### GetTransaction

```php
try{
     $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";
     $data = $vendeFacil->getTransaction($txid);
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```

### Documents

```php
try{
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

     $data = $vendeFacil->documents($params);
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```

### Collection

```php
try{
    $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";
    $data = $vendeFacil->collection($txid);
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```

### Get Status

```php
try{
    $txid = "809bdfe5-5ca8-0360-cc28-59738daa8ad0";
    $data = $vendeFacil->getStatus($txid);
}
catch (\Exception $exception){
    echo $exception->getMessage();
}
```