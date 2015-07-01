<?php

namespace Flubit\Client;

use Flubit\BadRequestException;
use Flubit\UnauthorizedException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use \DateTime;
use GuzzleHttp\Message\Request;

class ApiClient
{
    private $client;

    private $baseUrl;

    private $key;

    private $secret;

    private $format = 'json';

    private $version = 1;


    public function __construct(HttpClient $client, array $config)
    {
        $this->client = $client;

        if (!isset($config['key']) || !isset($config['secret']) || !isset($config['baseUrl'])) {
            throw new \InvalidArgumentException('The config must contain at least key, secret and baseUrl parameters');
        }

        $this->key = $config['key'];
        $this->secret = $config['secret'];
        $baseUrl = $config['baseUrl'];

        if (isset($config['version'])) {
            $this->version = $config['version'];
        }

        if (isset($config['format'])) {
            $this->format = $config['format'];
        }

        //Make sure that the base Url ends on / and adding version
        if (substr($baseUrl, '-1') != '/') {
            $baseUrl .='/';
        }

        $this->baseUrl = $baseUrl.$this->version.'/';
    }

    /**
     * @param string $endpoint
     */
    private function get($endpoint)
    {
        $token = $this->generateAuthToken();
        $request = $this->client->createRequest('GET',$this->buildUrl($endpoint),
            ['headers' => ['auth-token' => $token]]
        );

        return $this->call($request);
    }


    private function generateAuthToken()
    {
        $dateTime = new DateTime('UTC');
        $time = $dateTime->format("Y-m-d\TH:i:sO");

        $nonce = md5(uniqid(mt_rand(), true));

        $signature = base64_encode(
            sha1(
                base64_decode($nonce) . $time . $this->secret,
                true
            )
        );

        return sprintf(
            "key=\"%s\", signature=\"%s\", nonce=\"%s\", created=\"%s\"",
            $this->key,
            $signature,
            $nonce,
            $time
        );
    }

    public function getAccountStatus()
    {
        return $this->get('account/status')->getBody()->getContents();
    }

    /**
     * @param sting $product
     */
    public function addProduct($product)
    {
        return $this->post('products', $product);
    }

    private function post($endpoint, $body)
    {
            $token = $this->generateAuthToken();
            $request = $this->client->createRequest('POST',$this->buildUrl($endpoint),
                                    ['headers' => ['auth-token' => $token],
                                      'body'  => $body
                                    ]);

            $response = $this->call($request);
            if ($response->getStatusCode() == 201) {
                return true;
            }
            return false;
    }

    private function call(Request $request)
    {
        try {
            return $this->client->send($request);

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 401) {
                throw new UnauthorizedException('You have to be authenticated to access this resource');
            }
            if ($e->getResponse()->getStatusCode() == 400) {
                throw new BadRequestException($e->getResponse()->getBody()->getContents());
            }
            throw new \Exception($e->getMessage());
        }
    }

    private function buildUrl($endpoint)
    {
        return $this->baseUrl . $endpoint.'.'.$this->format;
    }
}
