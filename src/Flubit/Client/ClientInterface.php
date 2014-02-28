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
     * @return \SimpleXMLElement
     */
    public function dispatchOrderByFlubitId($id, \DateTime $dateTime, array $params);

    /**
     * @param string    $id
     * @param \DateTime $dateTime
     * @param array     $params
     *
     * @return \SimpleXMLElement
     */
    public function dispatchOrderByMerchantOrderId($id, \DateTime $dateTime, array $params);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement
     */
    public function cancelOrderByFlubitId($id, $reason);

    /**
     * @param string $id
     * @param string $reason
     *
     * @return \SimpleXMLElement
     */
    public function cancelOrderByMerchantOrderId($id, $reason);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement
     */
    public function refundOrderByFlubitId($id);

    /**
     * @param string $id
     *
     * @return \SimpleXMLElement
     */
    public function refundOrderByMerchantOrderId($id);

    /**
     * @param \DateTime $from
     * @param string    $status
     *
     * @return \SimpleXMLElement
     */
    public function getOrders(\DateTime $from, $status);

    /**
     * @param boolean $isActive
     * @param integer $limit
     * @param integer $page
     *
     * @return \SimpleXMLElement
     */
    public function getProducts($isActive, $limit, $page, $sku = null);

    /**
     * @param string $feedID
     *
     * @return \SimpleXMLElement
     */
    public function getProductsFeed($feedID);
    
    /**
     * 
     * @param type $feedID
     * @param type $page
     * @param type $limit
     * 
     * @return \SimpleXMLElement
     */
    public function getProductsFeedErrors($feedID, $page, $limit);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement
     */
    public function createProducts($productData);

    /**
     * @param $productData
     * @param $dataFormat
     *
     * @return \SimpleXMLElement
     */
    public function updateProducts($productData);
}
