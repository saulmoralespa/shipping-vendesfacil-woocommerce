<?php


namespace VendesFacil;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;


class Client
{
    const API_BASE_URL = "https://secure.vendesfacil.com/api/txns/v1/";

    protected $clientId;
    protected $secret;
    protected $accessToken;
    public $sandbox = false;

    public function __construct($clientId, $secret)
    {
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function sandbox($status = false)
    {
        if ($status)
            $this->sandbox = true;
        return $this;
    }

    public function client()
    {
        $guzzle = new GuzzleClient([
            'base_uri' => self::API_BASE_URL,
        ]);

        return $guzzle;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAccesToken()
    {

        try{
            $response = $this->client()->post("auth/token", [
                "json" => [
                    "client_id" => $this->clientId,
                    "secret" => $this->secret
                ]
            ]);


            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function quote($params)
    {
        try{
            $response = $this->client()->post("transporte/cotizacion", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ],
                "json" => $params


            ]);

            return self::responseJson($response);


        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function transaction(array $params)
    {

        try{
            $response = $this->client()->post("txn", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ],
                "json" => array_merge(
                    [
                        "sandbox" => $this->sandbox
                    ],
                    $params
                )

            ]);

            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getTransaction($txid)
    {
        try{
            $response = $this->client()->get("txn/$txid/estado", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ]
            ]);

            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function documents(array $params)
    {
        try{
            $response = $this->client()->post("transporte/{$params['txid']}/documentos", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ],
                "json" => $params
            ]);

            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param $txid
     * @return mixed
     * @throws \Exception
     */
    public function collection($txid)
    {
        try{
            $response = $this->client()->post("transporte/$txid/recogida", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ],
                "json" => [
                    "txid" => $txid
                ]
            ]);

            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }

    /**
     * @param $txid
     * @return mixed
     * @throws \Exception
     */
    public function getStatus($txid)
    {
        try{
            $response = $this->client()->get("txn/$txid/estado", [
                "headers" => [
                    "Authorization" => "Bearer {$this->getAccesToken()->token}"
                ],
                "json" => [
                    "txid" => $txid
                ]
            ]);

            return self::responseJson($response);

        } catch (RequestException $exception){
            throw  new  \Exception($exception->getMessage());
        }
    }


    public static function responseJson($response)
    {
        return \GuzzleHttp\json_decode(
            $response->getBody()->getContents()
        );
    }

}