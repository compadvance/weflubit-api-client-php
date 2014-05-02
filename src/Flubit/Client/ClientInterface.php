<?php

namespace Flubit\Client;

interface ClientInterface
{
     /**
     * 
     * @param string $format
     * @return Client
     */
    public function setResponseFormat($format);
    
    /**
     * 
     * @return string
     */
    public function getResponseFormat();
    
    /**
     * 
     * @param string $format
     * @return Client
     */
    public function setRequestFormat($format);
    
    /**
     * 
     * @return string
     */
    public function getRequestFormat();
    
    /**
     * @return \SimpleXMLElement|array
     */
    public function getAccountStatus();

    /**
     * @param string    $id
     * @param \DateTime $dateTime
     * @param array     $params
     *
     * @return \SimpleXMLElement|array
     */
    public function dispatchOrderByFlubitId($id, \DateTime $dateTime, array $params);

    /**
     * @param string    $id
     * @param \DateTime $dateTime
     * @param array     $params
     *
     * @return \SimpleXMLElement|array
     */
    public function dispatchOrderByMerchantOrderId($id, \DateTime $dateTime, array $params);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement|array
     */
    public function cancelOrderByFlubitId($id, $reason);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement|array
     */
    public function cancelOrderByMerchantOrderId($id, $reason);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement|array
     */
    public function refundOrderByFlubitId($id);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement|array
     */
    public function refundOrderByMerchantOrderId($id);

    /**
     * @param string       $status
     * @param \DateTime    $from
     *
     * @return \SimpleXMLElement|array
     */
    public function getOrders($status, \DateTime $from = null);

    /**
     * @param boolean $isActive
     * @param integer $limit
     * @param integer $page
     *
     * @return \SimpleXMLElement|array
     */
    public function getProducts(
        $isActive = '', 
        $limit = null, 
        $page = null, 
        $sku = null
    );

    /**
     * @param string $feedID
     *
     * @return \SimpleXMLElement|array
     */
    public function getProductsFeed($feedID);
    
    /**
     * 
     * @param type $feedID
     * @param type $page
     * @param type $limit
     * 
     * @return \SimpleXMLElement|array
     */
    public function getProductsFeedErrors($feedID, $page, $limit);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement|array
     */
    public function createProducts($productData);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement|array
     */
    public function updateProducts($productData);
}
