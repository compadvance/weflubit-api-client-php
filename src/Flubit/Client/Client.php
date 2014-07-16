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
    
    /**
     * @var string
     */
    const DEFAULT_REQUEST_FORMAT = 'xml';
    
    /**
     * @var string
     */
    const DEFAULT_RESPONSE_FORMAT = 'xml';
    
    /**
     * @var string 
     */
    private $requestFormat;
    
    /**
     * @var string 
     */
    private $responseFormat;
    
    /**
     * @var string
     */
    const XML_DISPATCH_PAYLOAD = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<dispatch>
    <dispatched_at>%s</dispatched_at>
    <courier>%s</courier>
    <consignment_number>%s</consignment_number>
    <tracking_url>%s</tracking_url>
</dispatch>
EOH;
    
    /**
     * @var string
     */
    const JSON_DISPATCH_PAYLOAD = <<<EOH
{
    "dispatched_at" : "%s",
    "courier" : "%s",
    "consignment_number" : "%s",
    "tracking_url" : "%s"
}
EOH;
    
    /**
     * @var string
     */
    const XML_CANCEL_PAYLOAD = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<cancel>
    <reason>%s</reason>
</cancel>
EOH;
    
    /**
     * @var string
     */
    const JSON_CANCEL_PAYLOAD = <<<EOH
{
    "reason" : "%s"
}
EOH;
    
    /**
     * @var string
     */
    const XML_REFUND_PAYLOAD = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<refund>
    <reason>%s</reason>
    <amount>%s</amount>
</refund>
EOH;
    
    /**
     * @var string
     */
    const JSON_REFUND_PAYLOAD = <<<EOH
{
    "reason" : "%s",
    "amount" : %s
}
EOH;

    /**
     * @param string    $apiKey
     * @param string    $apiSecret
     * @param string    $domain
     * @param boolean   $useHTTPS
     */
    public function __construct($apiKey, $apiSecret, $domain = 'api.weflubit.com', $useHTTPS = false)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        
        $this->requestFormat = self::DEFAULT_REQUEST_FORMAT;
        $this->responseFormat = self::DEFAULT_RESPONSE_FORMAT;
        
        $protocol = (true == $useHTTPS) ? 'https' : 'http';
        
        $this->client = new GuzzleClient('{protocol}://{domain}/{version}', array(
            'protocol'  => $protocol,
            'domain'    => $domain,
            'version'   => '1'
        ));
    }
    
    /**
     * 
     * @param string $format
     * @return Client
     */
    public function setResponseFormat($format)
    {
        $this->responseFormat = strtolower($format);
        
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
        $this->requestFormat = strtolower($format);
        
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
    public function refundOrderByFlubitId($id, $reason, $amount)
    {
        $payload = $this->generateRefundOrderPayload($reason, $amount);
        
        $request = $this->getPostRequest(
            sprintf('orders/refund.%s', $this->responseFormat),
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
    public function refundOrderByMerchantOrderId($id, $reason, $amount)
    {
        $payload = $this->generateRefundOrderPayload($reason, $amount);
        
        $request = $this->getPostRequest(
            sprintf('orders/refund.%s', $this->responseFormat),
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
    public function getOrders($status, \DateTime $from = null)
    {
        $params = array();
        
        if (!empty($from)) {
            $params['from'] = $from->format($this->timestampFormat);
        }
        
        if (!empty($status)) {
            $params['status'] = $status;
        }
        
        $request = $this->getGetRequest(
            sprintf('orders/filter.%s', $this->responseFormat),
            $params
        );

        return $this->call($request);
    }

    public function getProducts($isActive, $limit, $page, $sku = null)
    {
        $url = sprintf('/1/products/filter.%s', $this->responseFormat);
        
        $params = array();
        
        if (isset($isActive)) {
            $params['is_active'] = $isActive;
        }

        if ($sku) {
            $params['sku'] = $sku;
            $request = $this->getGetRequest(
                $url,
                $params
            );
        } else {
            $params['limit'] = $limit;
            $params['page']  = $page;
            
            $request = $this->getGetRequest(
                $url,
                $params
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
    public function getProductsFeedErrors($feedID, $page, $limit)
    {
        $request = $this->getGetRequest(
            sprintf('products/feed/%s/errors.%s', $feedID, $this->responseFormat),
            array('page' => $page, 'limit' => $limit)
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
    public function createSingleProduct($productData)
    {
        $request = $this->getPostRequest(
            sprintf('products.%s',$this->responseFormat),
            $productData
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
    
    /**
     * 
     * {@inheritdoc}
     */
    public function updateSingleProduct($productData)
    {
        $request = $this->getPatchRequest(
            sprintf('products.%s',$this->responseFormat),
            $productData
        );

        return $this->call($request);
    }

    private function generateDispatchOrderPayload(\DateTime $dateTime, array $params)
    {
        $courier = isset($params['courier']) ? $params['courier'] : '';
        $consignmentNumber = isset($params['consignment_number']) ? $params['consignment_number'] : '';
        $trackingUrl = isset($params['tracking_url']) ? $params['tracking_url'] : '';
        $payLoad = null;

        if ('xml' == $this->requestFormat) {
            
            $payLoad = sprintf(
                        self::XML_DISPATCH_PAYLOAD, 
                        $dateTime->format($this->timestampFormat), 
                        $courier, $consignmentNumber, $trackingUrl
                        );
        } else {
            $payLoad = sprintf(
                        self::JSON_DISPATCH_PAYLOAD, 
                        $dateTime->format($this->timestampFormat), 
                        $courier, $consignmentNumber, $trackingUrl
                        );
        }
        
        return $payLoad;
    }

    private function generateCancelOrderPayload($reason)
    {
        return ('xml' == $this->requestFormat) ? 
                sprintf(self::XML_CANCEL_PAYLOAD, $reason) :
                sprintf(self::JSON_CANCEL_PAYLOAD, $reason);
    }
    
    private function generateRefundOrderPayload($reason, $amount)
    {
        return ('xml' == $this->requestFormat) ? 
                sprintf(self::XML_REFUND_PAYLOAD, $reason, $amount) :
                sprintf(self::JSON_REFUND_PAYLOAD, $reason, $amount);
    }

    private function call(RequestInterface $request)
    {
        $responseFormat = trim($request->getHeader('accept'));
        
        try {
            $response = $request->send(array($request));
            
            return call_user_func_array(
                    array($response, self::$allowedContentTypes[$responseFormat]),
                    array()
                    );

        } catch (BadResponseException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
            $xml = call_user_func_array(
                    array($e->getResponse(), self::$allowedContentTypes[$responseFormat]),
                    array()
                    );
            
            if ($statusCode === 401) {

                throw new UnauthorizedException(is_object($xml) ? $xml->asXML() : json_encode($xml, JSON_PRETTY_PRINT), $statusCode);
            } else {

                throw new BadMethodCallException(is_object($xml) ? $xml->asXML() : json_encode($xml, JSON_PRETTY_PRINT), $statusCode);
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
                    'Content-Type' => $formats[$this->requestFormat]
                ),
                $payload,
                array('allow_redirects' => false)
            );
        $this->resetFormats();
        
        return $request;
    }
    
    private function getPatchRequest($uri, $payload = null, array $queryParams = array())
    {
        $formats = array_flip(self::$allowedContentTypes);
        $request = $this->client
            ->patch(
                sprintf('%s?%s', $uri, http_build_query($queryParams)),
                array(
                    'accept'     => $formats[$this->responseFormat],
                    'auth-token' => $this->generateAuthToken(),
                    'Content-Type' => $formats[$this->requestFormat]
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