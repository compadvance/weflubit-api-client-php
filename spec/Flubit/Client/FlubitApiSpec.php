<?php

namespace spec\Flubit\Client;

use Flubit\Client\ApiClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FlubitApiSpec extends ObjectBehavior
{
    public function it_is_initializable(ApiClient $apiClient)
    {
        $this->beConstructedWith($apiClient);
        $this->shouldHaveType('Flubit\Client\FlubitApi');
    }

    public function it_should_return_an_account_status(ApiClient $apiClient)
    {
        $accountJson =  '{
              "active_products": 10354,
              "available_products": 10354,
              "total_products": 10354,
              "last_update_at": "2014-12-01T14:12:24+01:00"
            }';

        $stream= Stream::factory($accountJson);
        $response = new Response(200, ['aaa' => '1'], $stream);
        $apiClient->get('account/status')->willReturn($response);

        $this->beConstructedWith($apiClient);

        $this->getAccountStatus()->shouldReturn($accountJson) ;
    }

    public function it_submits_a_product(ApiClient $apiClient)
    {
        $productjson = '{
  "sku": "sku1",
  "title": "pencil case 1",
  "is_active": true,
  "base_price": 4.99,
  "stock": 2,
  "description": "used for drawing",
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
      "type": "ean",
      "value": "5010559094936"
    }
  ],
  "brand": "pelican",
  "delivery_cost": {
    "standard": 1.50,
    "express": 2.99
  },
  "weight": "300",
  "available_from": "2005-08-15t15:52:01+0000",
  "packaging_code": "pk01",
  "additional_key_1": "additional_value_1",
  "additional_key_2": "additional_value_2"
}';
        $response = new response(201);
        $apiClient->post('products', $productjson)->willReturn($response);

        $this->beconstructedwith($apiClient);
        $this->addproduct($productjson)->shouldreturn(true);
    }

    public function it_should_be_able_to_patch_a_product(ApiClient $apiClient)
    {
        $productjson = '{
                        "sku": "sku1"
                        "is_active" : "false"
                        }';

        $response = new response(204);

        $apiClient->patch('products', $productjson)->willReturn($response);

        $this->beconstructedwith($apiClient);
        $this->patchProduct($productjson)->shouldReturn(true);
    }

    public function it_should_be_able_to_get_products_filtered(ApiClient $apiClient)
    {
        $productsFiltered = ' {
            "products": [
        {
            "sku": "SKU1",
          "title": "Product1",
          "is_active": "true",
          "base_price": 24.99,
          "stock": 15,
          "updated_at": "2014-06-24T09:05:09+01:00"
        },
        {
            "sku": "SKU2",
          "title": "Product2",
          "is_active": "true",
          "base_price": 10.99,
          "stock": 3,
          "updated_at": "2014-06-24T09:05:09+01:00"
        }
      ],
      "page": 1,
      "limit": 100,
      "total": 2
    }';

        $response = new response(200, [], Stream::factory($productsFiltered));

        $filters = ['is_active' => true];
        $apiClient->get('products/filter', $filters)->willReturn($response);
        $filters = ['is_active' => true];

        $this->beConstructedWith($apiClient);
        $this->getProductsFiltered($filters)->shouldReturn($productsFiltered);
    }


    public function it_should_post_create_products_feed(ApiClient $apiClient)
    {
        $product_feed = 'sku,title,is_active,base_price,stock,description,image1,mpn,brand,category,standard_delivery_cost,tax_rate,extended[colour],extended[size],extended[country_of_origin]
"60138","SanDisk 4GB Clip Zip","1","22.62","4","","http://placeholder.it/image/60138.jpg","111383+100350","SanDisk","Flash Memory","0","20","blue","1.90","Jamaica"' ;

        $response = new response(202, [], Stream::factory('{"id":"8f173289c78b3020a0b338ced995bd55"}'));

        $apiClient->post('products/feed', $product_feed, ["type" =>"create"])->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->addProductFeed($product_feed)->shouldReturn('8f173289c78b3020a0b338ced995bd55');
    }

    public function it_should_post_update_products_feed(ApiClient $apiClient)
    {
        $product_feed = 'sku,title,is_active,base_price,stock,description,image1,mpn,brand,category,standard_delivery_cost,tax_rate,extended[colour],extended[size],extended[country_of_origin]
"60138","SanDisk 4GB Clip Zip","1","22.62","4","","http://placeholder.it/image/60138.jpg","111383+100350","SanDisk","Flash Memory","0","20","blue","1.90","Jamaica"' ;

        $response = new response(202, [], Stream::factory('{"id":"8f173289c78b3020a0b338ced995bd55"}'));

        $apiClient->post('products/feed', $product_feed, [])->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->updateProductFeed($product_feed)->shouldReturn('8f173289c78b3020a0b338ced995bd55');
    }

    public function it_should_return_status_of_a_feed(ApiClient $apiClient)
    {
        $feedResponse = '{
          "status": "processed",
          "results": {
            "errors": {
              "sample": [
                {
                  "text": "Invalid ASIN: \"1234\".",
                  "product_sku": "123456SKU"
                }
              ],
              "total": "10,000"
            },
            "created": "50,305"
          }
        }';
        $feedId = '8f173289c78b3020a0b338ced995bd55';
        $response = new response(200, [], Stream::factory($feedResponse));

        $apiClient->get('products/feed/'.$feedId)->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->getProductFeedStatus($feedId)->shouldReturn($feedResponse);
    }

    public function it_should_return_the_errors_of_a_feed(ApiClient $apiClient)
    {
        $feedResponse = '{
          "error": [
            {
              "text": "Product not found.",
              "product_sku": "123456SKU"
            }
          ],
          "page": "1",
          "limit": "2"
        }';

        $response = new response(200, [], Stream::factory($feedResponse));

        $feedId = '8f173289c78b3020a0b338ced995bd55';
        $apiClient->get('products/feed/'.$feedId.'/errors')->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->getProductFeedErrors($feedId)->shouldReturn($feedResponse);
    }


    public function it_can_cancel_an_order(ApiClient $apiClient)
    {
        $response = '{
            "sucess":  "sucess"
            }';

        $response = new response(200, [], Stream::factory($response));

        $orderId = '34';
        $reason = 'Asked by the client';

        $body = json_encode(['reason' => $reason]);
        $params =['flubit_order_id' => $orderId];
        $apiClient->post('orders/cancel', $body, $params)->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->cancelOrder($orderId, $reason)->shouldReturn(true);
    }

    public function it_can_mark_an_order_as_dispatched(ApiClient $apiClient)
    {
        $response = '{
            "sucess":  "sucess"
            }';

        $body = '{
          "dispatched_at": "2013-06-16T14:12:24+01:00",
          "courier": "Royal Mail",
          "consignment_number": "AB123456789GB",
          "tracking_url": "https://www.royalmail.com/track-your-item"
        }';

        $orderId = '34';
        $response = new response(200, [], Stream::factory($response));

        $params =['flubit_order_id' => $orderId];
        $apiClient->post('orders/dispatch', $body, $params)->willReturn($response);

        $this->beConstructedWith($apiClient);

        $this->markOrderAsDispatched($orderId, $body)->shouldReturn(true);
    }

    public function it_can_get_a_list_of_products_filtered_by_skus(ApiClient $apiClient)
    {
        $jsonResponse = '{
          "products": [
            {
              "sku": "SKU1",
              "title": "Product1",
              "is_active": "true",
              "base_price": 24.99,
              "stock": 15,
              "updated_at": "2014-06-24T09:05:09+01:00"
            },
            {
              "sku": "SKU2",
              "title": "Product2",
              "is_active": "true",
              "base_price": 10.99,
              "stock": 3,
              "updated_at": "2014-06-24T09:05:09+01:00"
            }
          ],
          "page": 1,
          "limit": 100,
          "total": 2
        }';

        $skus = '{
          "skus": [
            "SKU1",
            "SKU2",
            "SKU3"
          ]
        }';

        $response = new response(200, [], Stream::factory($jsonResponse));
        $apiClient->post('products/filter', $skus)->willReturn($response);

        $this->beConstructedWith($apiClient);
        $this->postProductsFiltered($skus)->shouldReturn($jsonResponse);
    }
}
