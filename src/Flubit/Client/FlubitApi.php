<?php

namespace Flubit\Client;

use Flubit\Client\ApiClient;

class FlubitApi
{
    private $apiClient;

    private $format;

    public function __construct(ApiClient $apiClient, $format = 'json')
    {
        $this->apiClient = $apiClient;
        $this->format = $format;
    }
    public function getAccountStatus()
    {
        return $this->apiClient->get('account/status')->getBody()->getContents();
    }

    /**
     * @param string $product
     * @return boolean
     */
    public function addProduct($product)
    {
        $response = $this->apiClient->post('products', $product);
        if ($response->getStatusCode() == 201) {
            return true;
        }
        return false;
    }

    /**
     * @param string $product
     * @return string
     */
    public function patchProduct($product)
    {
        $response  = $this->apiClient->patch('products', $product);

        if ($response->getStatusCode() == 204) {
            return true;
        }
        return false;
    }

    public function getProductsFiltered(array $filters)
    {
        return $this->apiClient->get('products/filter', $filters)->getBody()->getContents();
    }

    public function postProductsFiltered($skus)
    {
        return $this->apiClient->post('products/filter', $skus)->getBody()->getContents();
    }

    public function addProductFeed($productFeed)
    {
        $response = $this->apiClient->post('products/feed', $productFeed, ['type' => 'create']);
        if ($this->format == 'json') {
            $jsonResponse = $response->json() ;
            $feedId = $jsonResponse['id'];
        } else {
            $xmlResponse = $response->xml() ;
            $feedId = $xmlResponse->id;
        }
        return $feedId;
    }

    public function updateProductFeed($productFeed)
    {
        $response = $this->apiClient->post('products/feed', $productFeed, []);
        if ($this->format == 'json') {
            $jsonResponse = $response->json() ;
            $feedId = $jsonResponse['id'];
        } else {
            $xmlResponse = $response->xml() ;
            $feedId = $xmlResponse->id;
        }

        return $feedId;
    }

    /**
     * @param string $feedId
     * @return string
     */
    public function getProductFeedStatus($feedId)
    {
        return $this->apiClient->get('products/feed/'.$feedId)->getBody()->getContents();
    }

    /**
     * @param string $feedId
     * @return string
     */
    public function getProductFeedErrors($feedId)
    {
        return $this->apiClient->get('products/feed/'.$feedId.'/errors')->getBody()->getContents();
    }

    /**
     * @param $orderId
     * @param $reason
     * @return bool
     */
    public function cancelOrder($orderId, $reason)
    {
        $body = json_encode(['reason' => $reason]);
        $params =['flubit_order_id' => $orderId];
        $response  =  $this->apiClient->post('orders/cancel', $body, $params);
        if ($response->getStatusCode() == 200) {
            return true;
        }
        return false;
    }

    /**
     * @param $orderId
     * @param $body
     * @return bool
     */
    public function markOrderAsDispatched($orderId, $body)
    {
        $params =['flubit_order_id' => $orderId];
        $response = $this->apiClient->post('orders/dispatch', $body, $params);

        if ($response->getStatusCode() == 200) {
            return true;
        }
        return false;
    }
}
