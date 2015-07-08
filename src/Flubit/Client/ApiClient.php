<?php

namespace Flubit\Client;

use Flubit\BadRequestException;
use Flubit\NotFoundException;
use Flubit\UnauthorizedException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use \DateTime;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Stream\Stream;

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
     * @param array $params
     * @return string
     */
    public function get($endpoint, array $params = [])
    {
        $token = $this->generateAuthToken();
        $request = $this->client->createRequest('GET', $this->buildUrl($endpoint, $params),
            ['headers' => ['auth-token' => $token]]
        );

        return $this->call($request);
    }



    /**
     * @param string $endpoint
     * @param string $body
     * @param array $params
     * @return boolean
     */
    public function post($endpoint, $body, array $params = [])
    {
        $token = $this->generateAuthToken();
        $request = $this->client->createRequest('POST', $this->buildUrl($endpoint, $params),
                                    ['headers' => ['auth-token' => $token]
                                    ]);
        $bodyStream = Stream::factory($body);
        $request->setBody($bodyStream);
        return $this->call($request);
    }

     /**
     * @param string $endpoint
     * @param string $body
      * @return boolean
     */
    public function patch($endpoint, $body)
    {
        $token = $this->generateAuthToken();
        $request = $this->client->createRequest('PATCH', $this->buildUrl($endpoint),
                                    ['headers' => ['auth-token' => $token]
                                    ]);

        $bodyStream = Stream::factory($body);
        $request->setBody($bodyStream);
        $response = $this->call($request);
        if ($response->getStatusCode() == 204) {
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
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new NotFoundException($e->getMessage());
            }

            throw new \Exception($e->getMessage());
        }
    }

    private function buildUrl($endpoint, array $params = [])
    {
        return sprintf('%s%s.%s?%s', $this->baseUrl, $endpoint, $this->format, http_build_query($params));
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
}
