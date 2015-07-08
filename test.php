<?php

require 'vendor/autoload.php';

            $config  = [
            'key' => '2827-1946-2647',
            'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
            'baseUrl' => 'api.sandbox.weflubit.com',
        ];

$flubitClient =  \Flubit\Client\FlubitApiFactory::factory($config);

$status = $flubitClient->getAccountStatus();

var_dump($status);


        $productJson = '{
  "sku": "SKU2",
  "title": "Pencil case 1",
  "is_active": true,
  "base_price": 4.99,
  "stock": 2,
  "description": "Used for drawing",
  "images": [
    {
      "url": "http://www.flubit.com/1.jpg"
    },
    {
      "url": "http://www.flubit.com/2.jpg"
    }
  ],
  "identifiers": [
    {
      "type": "EAN",
      "value": "5010559094936"
    }
  ],
  "brand": "Pelican",
  "delivery_cost": {
    "standard": 1.50,
    "express": 2.99
  },
  "weight": "300",
  "available_from": "2005-08-15T15:52:01+0000",
  "packaging_code": "PK01",
  "additional_key_1": "additional_value_1",
  "additional_key_2": "additional_value_2"
}';


//$response = $flubitClient->addProduct($productJson);

//var_dump($response);

$response = $flubitClient->getProductsFiltered(['sku' => 'SKU2']);

var_dump($response);


       $product_feed = 'sku,title,is_active,base_price,stock,description,image1,mpn,brand,category,standard_delivery_cost,tax_rate,extended[colour],extended[size],extended[country_of_origin]
"60138","SanDisk 4GB Clip Zip","1","22.62","4","","http://placeholder.it/image/60138.jpg","111383+100350","SanDisk","Flash Memory","0","20","blue","1.90","Jamaica"' ;

$response = $flubitClient->addProductFeed($product_feed);
var_dump($response);

$feedId = $flubitClient->updateProductFeed($product_feed);

$response = $flubitClient->getProductFeedStatus($feedId);
var_dump($response);


$response = $flubitClient->getProductFeedErrors($feedId);
var_dump($response);
/*
$response = $flubitClient->cancelOrder(34,'Out of Stock');
var_dump($response);

$body = '{
          "dispatched_at": "2013-06-16T14:12:24+01:00",
          "courier": "Royal Mail",
          "consignment_number": "AB123456789GB",
          "tracking_url": "https://www.royalmail.com/track-your-item"
        }';

$response = $flubitClient->markOrderAsDispatched(34, $body);
var_dump($response);
*/

$skus = '{
  "skus": [
    "SKU1",
    "SKU2",
    "SKU3"
  ]
}';

$response = $flubitClient->postProductsFiltered($skus);
var_dump($response);
