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
     * {@inheritdoc}
     */
    public function getAccountStatus()
    {
        $request = $this->getGetRequest('account/status.xml');

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOrderByFlubitId($id, \DateTime $dateTime, array $params)
    {
        $payload = $this->generateDispatchOrderPayload($dateTime, $params);

        $request = $this->getPostRequest(
            'orders/dispatch.xml',
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
            'orders/dispatch.xml',
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
            'orders/cancel.xml',
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
            'orders/cancel.xml',
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
            'orders/refund.xml',
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
            'orders/refund.xml',
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
            'orders/filter.xml',
            array(
                'from' => $from->format($this->timestampFormat),
                'status' => $status
            )
        );

        return $this->call($request);
    }

    public function getProducts($isActive, $limit, $page, $sku = null)
    {
        $url = '/1/products/filter.xml';

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
            sprintf('products/feed/%s.xml', $feedID)
        );

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function createProducts($productData, $dataFormat = 'xml')
    {
        $request = $this->getPostRequest(
            'products/feed.xml',
            $dataFormat,
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
    public function updateProducts($productData, $dataFormat = 'xml')
    {
        $request = $this->getPostRequest(
            'products/feed.xml',
            $dataFormat,
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
        try {
            return $request
                   ->send(array($request))
                   ->xml();

        } catch (BadResponseException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
            $xml = $e->getResponse()->xml();
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
        return $this->client
            ->get(
                sprintf('%s?%s', $uri, http_build_query($queryParams)),
                array(
                    'accept'     => 'application/xml',
                    'auth-token' => $this->generateAuthToken()
                ),
                array('allow_redirects' => false)
            );
    }

    private function getPostRequest($uri, $payloadFormat, $payload = null, array $queryParams = array())
    {
        return $this->client
            ->post(
                sprintf('%s?%s', $uri, http_build_query($queryParams)),
                array(
                    'accept'     => 'application/xml',
                    'auth-token' => $this->generateAuthToken(),
                    'Content-Type' => ('csv' == strtolower($payloadFormat)) ? 'text/csv' : 'application/xml'
                ),
                $payload,
                array('allow_redirects' => false)
            );
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
}
