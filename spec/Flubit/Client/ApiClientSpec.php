<?php

namespace spec\Flubit\Client;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use \InvalidArgumentException;
use GuzzleHttp\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use \DateTime;

class ApiClientSpec extends ObjectBehavior
{
    private function getDefaultConfigArray()
    {
        return  [
            'key' => '2827-1946-2647',
            'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
            'baseUrl' => 'api.sandbox.weflubit.com',
        ];
    }
    public function let(Client $httpClient)
    {
        $this->beConstructedWith($httpClient, $this->getDefaultConfigArray());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Flubit\Client\ApiClient');
    }

    public function it_will_trow_an_exception_if_we_dont_send_correct_config(Client $httpClient)
    {
        $config  = [];
        $this->beConstructedWith($httpClient, $config);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $config  = [
            'key' => '2827-1946-2647',
            'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
        ];
        $this->beConstructedWith($httpClient, $config);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $config  = [
            'key' => '2827-1946-2647',
            'baseUrl' => 'api.sandbox.weflubit.com'
        ];
        $this->beConstructedWith($httpClient, $config);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_will_throw_Unauthorized_exception_when_the_respose_is_a_401()
    {
        $client = new client();
        $mock = new mock([new response(401)]);
        $client->getemitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());
        $this->shouldThrow('Flubit\UnauthorizedException')->during('getAccountStatus');
    }

    public function it_should_return_an_account_status()
    {
        $client = new Client();
        $accountJson =  '{
              "active_products": 10354,
              "available_products": 10354,
              "total_products": 10354,
              "last_update_at": "2014-12-01T14:12:24+01:00"
            }';

        $stream= Stream::factory($accountJson);

        $mock = new Mock([new Response(200, ['aaa' => '1'], $stream)]);

        $client->getEmitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());

        $this->getAccountStatus()->shouldReturn($accountJson) ;
    }

    public function it_submits_a_product()
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
        $client = new client();
        $mock = new mock([new response(201)]);

        $client->getemitter()->attach($mock);

        $this->beconstructedwith($client, $this->getdefaultconfigarray());
        $this->addproduct($productjson)->shouldreturn(true);
    }


    public function it_will_throw_a_bad_request_exception()
    {
        $productjson = '{
                        "sku": "sku1"
                        }';

        $stream = Stream::factory('{"code":400,"message":"You must specify at least one valid identifier."}');
        $client = new client();
        $mock = new mock([new response(400, [], $stream)]);

        $client->getemitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());
        $this->shouldThrow('Flubit\BadRequestException')->during('addProduct', [$productjson]);
    }
}
