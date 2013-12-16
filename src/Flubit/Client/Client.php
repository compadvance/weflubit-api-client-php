<?php

namespace Flubit\Client;

use Flubit\Exception\BadMethodCallException;
use Flubit\Exception\UnauthorizedException;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestInterface;

class Client implements ClientInterface
{
    /**
     * @var \Guzzle\Http\Client
     */
    private $client;

    /**
     * @var string
     */
    private $timestampFormat = "Y-m-d\TH:i:sO";

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;
    
    /**
     *
     * @var Array 
     */
    private static $allowedContentTypes = array(
        'application/xml' => 'xml',
        'application/json' => 'json',
        'text/csv' => 'csv'
    );
    
    const DEFAULT_REQUEST_FORMAT = 'xml';
    
    const DEFAULT_RESPONSE_FORMAT = 'xml';
    
    /**
     *
     * @var string 
     */
    private $requestFormat = self::DEFAULT_REQUEST_FORMAT;
    
    /**
     *
     * @var string 
     */
    private $responseFormat = self::DEFAULT_RESPONSE_FORMAT;

    /**
     * @param string $apiKey
     * @param string $apiSecret
     * @param string $domain
     */
    public function __construct($apiKey, $apiSecret, $domain = 'api.weflubit.com')
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        $this->client = new GuzzleClient('http://{domain}/{version}', array(
            'domain'  => $domain,
            'version' => '1'
        ));
    }
    
    /**
     * 
     * @param string $format
     * @return Client
     */
    public function setResponseFormat($format)
    {
        $this->responseFormat = $format;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->responseFormat;
    }
    
    /**
     * 
     * @param string $format
     * @return Client
     */
    public function setRequestFormat($format)
    {
        $this->requestFormat = $format;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRequestFormat()
    {
        return $this->requestFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccountStatus()
    {
        $request = $this->getGetRequest(sprintf('account/status.%s', $this->responseFormat));

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOrderByFlubitId($id, \DateTime $dateTime, array $params)
    {
        $payload = $this->generateDispatchOrderPayload($dateTime, $params);

        $request = $this->getPostRequest(
            sprintf('orders/dispatch.%s', $this->responseFormat),
            $payload,
            array(
                'flubit_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOrderByMerchantOrderId($id, \DateTime $dateTime, array $params)
    {
        $payload = $this->generateDispatchOrderPayload($dateTime, $params);

        $request = $this->getPostRequest(
            sprintf('orders/dispatch.%s', $this->responseFormat),
            $payload,
            array(
                'merchant_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderByFlubitId($id, $reason)
    {
        $payload = $this->generateCancelOrderPayload($reason);

        $request = $this->getPostRequest(
            sprintf('orders/cancel.%s', $this->responseFormat),
            $payload,
            array(
                'flubit_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderByMerchantOrderId($id, $reason)
    {
        $payload = $this->generateCancelOrderPayload($reason);

        $request = $this->getPostRequest(
            sprintf('orders/cancel.%s', $this->responseFormat),
            $payload,
            array(
                'merchant_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrderByFlubitId($id)
    {
        $request = $this->getPostRequest(
            sprintf('orders/refund.%s', $this->responseFormat),
            null,
            array(
                'flubit_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrderByMerchantOrderId($id)
    {
        $request = $this->getPostRequest(
            sprintf('orders/refund.%s', $this->responseFormat),
            null,
            array(
                'merchant_order_id' => $id
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders(\DateTime $from, $status)
    {
        $request = $this->getGetRequest(
            sprintf('orders/filter.%s', $this->responseFormat),
            array(
                'from' => $from->format($this->timestampFormat),
                'status' => $status
            )
        );

        return $this->call($request);
    }

    public function getProducts($isActive, $limit, $page, $sku = null)
    {
        $url = sprintf('/1/products/filter.%s', $this->responseFormat);

        if ($sku) {
            $request = $this->getGetRequest(
                $url,
                array(
                    'is_active' => $isActive,
                    'sku'       => $sku,
                )
            );
        } else {
            $request = $this->getGetRequest(
                $url,
                array(
                    'is_active' => $isActive,
                    'limit'     => $limit,
                    'page'      => $page,
                )
            );
        }

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsFeed($feedID)
    {
        $request = $this->getGetRequest(
            sprintf('products/feed/%s.%s', $feedID, $this->responseFormat)
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function createProducts($productData)
    {
        $request = $this->getPostRequest(
            sprintf('products/feed.%s',$this->responseFormat),
            $productData,
            array(
                'type' => 'create'
            )
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function updateProducts($productData)
    {
        $request = $this->getPostRequest(
            sprintf('products/feed.%s',$this->responseFormat),
            $productData
        );

        return $this->call($request);
    }

    private function generateDispatchOrderPayload(\DateTime $dateTime, array $params)
    {
        $courier = isset($params['courier']) ? $params['courier'] : '';
        $consignmentNumber = isset($params['consignment_number']) ? $params['consignment_number'] : '';
        $trackingUrl = isset($params['tracking_url']) ? $params['tracking_url'] : '';

        return <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<dispatch>
    <dispatched_at>{$dateTime->format($this->timestampFormat)}</dispatched_at>
    <courier>{$courier}</courier>
    <consignment_number>{$consignmentNumber}</consignment_number>
    <tracking_url>{$trackingUrl}</tracking_url>
</dispatch>
EOH;
    }

    private function generateCancelOrderPayload($reason)
    {
        return <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<cancel>
    <reason>{$reason}</reason>
</cancel>
EOH;
    }

    private function call(RequestInterface $request)
    {
        $responseFormat = $request->headers->get('accept');
        
        try {
            $response = $request->send(array($request));
            
            return call_user_func_array(
                    array($response, self::$allowedContentTypes[$responseFormat])
                    );

        } catch (BadResponseException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
            $xml = call_user_func_array(
                    array($e->getResponse(), self::$allowedContentTypes[$responseFormat])
                    );

            $msg = (string)$xml['message'];

            if ($statusCode === 401) {

                throw new UnauthorizedException($msg, (int)$xml['code']);
            } else {

                throw new BadMethodCallException($msg, $statusCode);
            }
        }
    }

    private function getGetRequest($uri, array $queryParams = array())
    {
        $formats = array_flip(self::$allowedContentTypes);
        $request = $this->client
            ->get(
                sprintf('%s?%s', $uri, http_build_query($queryParams)),
                array(
                    'accept'     => $formats[$this->responseFormat],
                    'auth-token' => $this->generateAuthToken()
                ),
                array('allow_redirects' => false)
            );
        $this->resetFormats();
        
        return $request;
    }

    private function getPostRequest($uri, $payload = null, array $queryParams = array())
    {
        $formats = array_flip(self::$allowedContentTypes);
        $request = $this->client
            ->post(
                sprintf('%s?%s', $uri, http_build_query($queryParams)),
                array(
                    'accept'     => $formats[$this->responseFormat],
                    'auth-token' => $this->generateAuthToken(),
                    'Content-Type' => self::$allowedContentTypes[$this->requestFormat]
                ),
                $payload,
                array('allow_redirects' => false)
            );
        $this->resetFormats();
        
        return $request;
    }

    /**
     * Create HTTP auth header
     *
     * Uses new nonce and api signature each time it's called
     *
     * @return string
     */
    private function generateAuthToken()
    {
        $dateTime = new \DateTime('UTC');
        $time = $dateTime->format($this->timestampFormat);
        $nonce = $this->generateNonce();

        $signature = base64_encode(
            sha1(
                base64_decode($nonce) . $time . $this->apiSecret,
                true
            )
        );

        return sprintf(
            "key=\"%s\", signature=\"%s\", nonce=\"%s\", created=\"%s\"",
            $this->apiKey,
            $signature,
            $nonce,
            $time
        );
    }

    /**
     * @return string
     */
    private function generateNonce()
    {
        $nonce = md5(uniqid(mt_rand(), true));

        return $nonce;
    }

    private function resetFormats()
    {
        $this->requestFormat = self::DEFAULT_REQUEST_FORMAT;
        $this->responseFormat = self::DEFAULT_RESPONSE_FORMAT;
    }
}
