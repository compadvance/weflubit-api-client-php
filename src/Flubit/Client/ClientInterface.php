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
     * @return \SimpleXMLElement
     */
    public function getAccountStatus();

    /**
     * @param string    $id
     * @param \DateTime $dateTime
     * @param array     $params
     *
     * @return \SimpleXMLElement|string
     */
    public function dispatchOrderByFlubitId($id, \DateTime $dateTime, array $params);

    /**
     * @param string    $id
     * @param \DateTime $dateTime
     * @param array     $params
     *
     * @return \SimpleXMLElement|string
     */
    public function dispatchOrderByMerchantOrderId($id, \DateTime $dateTime, array $params);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement|string
     */
    public function cancelOrderByFlubitId($id, $reason);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement|string
     */
    public function cancelOrderByMerchantOrderId($id, $reason);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement|string
     */
    public function refundOrderByFlubitId($id);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement|string
     */
    public function refundOrderByMerchantOrderId($id);

    /**
     * @param string       $status
     * @param \DateTime    $from
     *
     * @return \SimpleXMLElement|string
     */
    public function getOrders($status, \DateTime $from = null);

    /**
     * @param boolean $isActive
     * @param integer $limit
     * @param integer $page
     *
     * @return \SimpleXMLElement|string
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
     * @return \SimpleXMLElement|string
     */
    public function getProductsFeed($feedID);
    
    /**
     * 
     * @param type $feedID
     * @param type $page
     * @param type $limit
     * 
     * @return \SimpleXMLElement|string
     */
    public function getProductsFeedErrors($feedID, $page, $limit);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement|string
     */
    public function createProducts($productData);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement|string
     */
    public function updateProducts($productData);
}
