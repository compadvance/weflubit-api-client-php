<?php
/**
 * @file
 */

require_once 'vendor/autoload.php';
require_once 'config.php';

$client = new \Flubit\Client\Client(CONSUMER_KEY, CONSUMER_SECRET, DOMAIN);

##############################
# Call account/status
##############################

try {

    $xml = $client->getAccountStatus();
    printf("You have %s active products\n", (int) $xml->active_products);

} catch (\Flubit\Exception\UnauthorizedException $e) {

    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

##############################
# Post a feed
##############################

$productXml = <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product sku="123456SKU">
        <title>iPhone 5 Hybrid Rubberised Back Cover Case</title>
        <identifiers>
            <identifier type="ASIN">B008OSEQ64</identifier>
        </identifiers>
    </product>
</products>
EOH;

try {

    $xml = $client->createProducts($productXml);
    $feedId = (string) $xml;
    printf("Feed %s created\n", $feedId);

} catch (\Flubit\Exception\UnauthorizedException $e) {

    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

##############################
# Post a feed - CSV
##############################

$productXml = <<<EOH
sku,stock
"174",999
EOH;

try {

    $xml = $client->setRequestFormat('csv')
            ->createProducts($productXml);
    $feedId = (string) $xml;
    printf("Feed %s created\n", $feedId);

} catch (\Flubit\Exception\UnauthorizedException $e) {

    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

##############################
# Call product search
##############################
try {
    $isActive = true;
    $page     = 1;
    $limit    = 10;

    $xml = $client->getProducts($isActive, $limit, $page);
    echo $xml->asXML();

} catch (\Flubit\Exception\UnauthorizedException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

##############################
# Call product search - JSON
##############################
try {
    $isActive = true;
    $page     = 1;
    $limit    = 10;

    $xml = $client->setResponseFormat('json')
            ->getProducts($isActive, $limit, $page);
    
    echo json_encode($xml);

} catch (\Flubit\Exception\UnauthorizedException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Check feed status
########################################

try {

    $xml = $client->getProductsFeed($feedId);
    printf("Feed %s has status: %s\n", $feedId, (string) $xml->attributes()->status);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Check feed status - JSON
########################################

try {

    $xml = $client->setResponseFormat('json')
            ->getProductsFeed($feedId);
    printf("Feed %s has status: %s\n", $feedId, $xml['status']);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Check feed errors
########################################

try {

    $page = 0;
    $limit = 1;
    $xml = $client->getProductsFeedErrors($feedId,$page,$limit);
    
    echo $xml->asXML();

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Check feed errors - JSON
########################################

try {

    $page = 0;
    $limit = 1;
    $xml = $client->setResponseFormat('json')
            ->getProductsFeedErrors($feedId,$page,$limit);
    
    echo json_encode($xml);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Get orders awaiting dispatch
########################################

try {

    $xml = $client->getOrders("-1 year", 'awaiting_dispatch');

} catch (\Flubit\Exception\BadMethodCallException $e) {

    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Dispatch an order
########################################

try {

    $xml = $client->dispatchOrderByFlubitId(
        1,
        new DateTime(),
        array(
            'courier'            => 'Lorem Ipsum',
            'consignment_number' => '123456789',
            'tracking_url'       => 'http://someurl.com/tracking'
        )
    );

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Dispatch an order - JSON
########################################

try {

    $xml = $client->setRequestFormat('json')
            ->dispatchOrderByFlubitId(
                1193,
                new DateTime(),
                array(
                    'courier'            => 'JB',
                    'consignment_number' => '123456789',
                    'tracking_url'       => 'http://someurl.com/tracking'
                )
            );

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Cancel an order
########################################

try {

    $xml = $client->cancelOrderByMerchantOrderId(
                1,
                "Wrong colour."
            );

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Cancel an order - JSON
########################################

try {

    $xml = $client->setRequestFormat('json')
            ->cancelOrderByMerchantOrderId(
                1,
                "Wrong colour."
            );

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Refund an order
########################################

try {

    $xml = $client->refundOrderByMerchantOrderId(1);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}