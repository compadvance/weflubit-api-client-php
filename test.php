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

    printf("You have %s active products\n", (int)$xml);

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

########################################
# Check feed status
########################################

try {

    $xml = $client->getProductsFeed($feedId);

    printf("Feed %s has status: %s\n", $feedId, (string) $xml->status);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}

########################################
# Get orders awaiting dispatch
########################################

try {

    $xml = $client->getOrders(new \DateTime("-1 year"), 'awaiting_dispatch');

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
# Refund an order
########################################

try {

    $xml = $client->refundOrderByMerchantOrderId(1);

} catch (\Flubit\Exception\BadMethodCallException $e) {
    printf("API Error (%d): %s\n", $e->getCode(), $e->getMessage());
}