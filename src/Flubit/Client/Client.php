<?php

namespace Flubit\Client;

use Flubit\Exception\BadMethodCallException;
use Flubit\Exception\UnauthorizedException;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
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

        $request = $this->getPostRequest('orders/dispatch.xml?flubit_order_id=' . $id, $payload);

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatchOrderByMerchantOrderId($id, \DateTime $dateTime, array $params)
    {
        $payload = $this->generateDispatchOrderPayload($dateTime, $params);

        $request = $this->getPostRequest('orders/dispatch.xml?merchant_order_id=' . $id, $payload);

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderByFlubitId($id, $reason)
    {
        $payload = $this->generateCancelOrderPayload($reason);

        $request = $this->getPostRequest('orders/cancel.xml?flubit_order_id=' . $id, $payload);

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderByMerchantOrderId($id, $reason)
    {
        $payload = $this->generateCancelOrderPayload($reason);

        $request = $this->getPostRequest('orders/cancel.xml?merchant_order_id=' . $id, $payload);

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrderByFlubitId($id)
    {
        $request = $this->getPostRequest('orders/refund.xml?flubit_order_id=' . $id);

        return $this->call($request);
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrderByMerchantOrderId($id)
    {
        $request = $this->getPostRequest('orders/refund.xml?merchant_order_id=' . $id);

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

        } catch (ClientErrorResponseException $e) {

            $statusCode = $e->getResponse()->getStatusCode();
            $msg = (string) $e->getResponse()->xml()['message'];

            if ($statusCode === 401) {

                throw new UnauthorizedException($msg, (int) $e->getResponse()->xml()['code']);
            } else {

                throw new BadMethodCallException($msg, $statusCode);
            }
        }
    }

    private function getGetRequest($uri)
    {
        return $this->client
            ->get(
                $uri,
                array(
                    'accept'     => 'application/xml',
                    'auth-token' => $this->generateAuthToken()
                )
            );
    }

    private function getPostRequest($uri, $payload = array())
    {
        return $this->client
            ->post(
                $uri,
                array(
                    'accept'     => 'application/xml',
                    'auth-token' => $this->generateAuthToken()
                ),
                $payload
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
        $time = (new \DateTime('UTC'))->format($this->timestampFormat);
        $nonce = $this->generateNonce();

        $signature = base64_encode(
            sha1(
                base64_decode($nonce) . $time . $this->apiSecret,
                true
            )
        );

        return sprintf(
            "\tauth-token\n\tkey=\"%s\",\n\tsignature=\"%s\",\n\tnonce=\"%s\",\n\tcreated=\"%s\"",
            $this->apiKey,
            $signature,
            $nonce,
            $time
        );
    }

    /**
     * Create new nonce
     *
     * Uses the given string if supplied, or current timestamp if not
     *
     * @param  string $randomString
     *
     * @return string
     */
    private function generateNonce($randomString = null)
    {
        if (!$randomString) {
            $randomString = (new \DateTime)->getTimestamp();
        }

        $nonce = md5($randomString);

        return $nonce;
    }


//
//    /**
//     * Query API feed method in create mode
//     *
//     * @param  string $xmlString
//     *
//     * @return string
//     */
//    public function createProduct($xmlString)
//    {
//        return $this->queryApi(
//            '1/products/feed.xml?type=create',
//            self::METHOD_TYPE_POST,
//            $xmlString
//        );
//    }
//
//    /**
//     * Query API feed method in update mode
//     *
//     * @param  string $xmlString
//     *
//     * @return string
//     */
//    public function updateProduct($xmlString)
//    {
//        return $this->queryApi(
//            '1/products/feed.xml',
//            self::METHOD_TYPE_POST,
//            $xmlString
//        );
//    }
//
//    /**
//     * Query API feed method with feed ID
//     *
//     * @param  integer $feedID
//     *
//     * @return string
//     */
//    public function getProductFeedStatus($feedID)
//    {
//        return $this->queryApi(
//            sprintf("1/products/feed/%s.xml", $feedID),
//            self::METHOD_TYPE_GET
//        );
//    }
//
//    /**
//     * Query API filter method
//     *
//     * @param  string $from
//     * @param  string $status
//     *
//     * @return string
//     */
//    public function filterOrders($from, $status = null)
//    {
//        $params = ['from' => $from, 'status' => $status];
//
//        return $this->queryApi(
//            '1/orders/filter.xml',
//            self::METHOD_TYPE_GET,
//            null,
//            $params
//        );
//    }

}
