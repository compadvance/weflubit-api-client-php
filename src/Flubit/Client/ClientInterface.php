<?php

namespace Flubit\Client;

interface ClientInterface
{
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

//    public function createProduct($xmlString);
//
//    public function updateProduct($xmlString);
//
//    public function getProductFeedStatus($feedID);
//
//    public function filterOrders($from, $status);
//
//    public function dispatchOrder($xmlString, $orderId, $type = 'flubit');
//
//    public function cancelOrder($xmlString, $orderId, $type = 'flubit');
//
//    public function refundOrder($orderId, $type = 'flubit');
}
