<?php

namespace spec\Flubit\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use PhpSpec\ObjectBehavior;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;

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

    public function it_should_trow_an_exception_if_we_dont_send_correct_config(Client $httpClient)
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


    public function it_should_throw_Unauthorized_exception_when_the_respose_is_a_401()
    {
        $client = new client();
        $mock = new mock([new response(401)]);
        $client->getemitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());
        $this->shouldThrow('Flubit\UnauthorizedException')->during('get', ['account/status']);
    }

    public function it_should_throw_a_bad_request_exception_when_the_response_is_a_400()
    {
        $productjson = '{
                        "sku": "sku1"
                        }';

        $stream = Stream::factory('{"code":400,"message":"You must specify at least one valid identifier."}');
        $mock = new mock([new response(400, [], $stream)]);

        $client = new client();
        $client->getemitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());
        $this->shouldThrow('Flubit\BadRequestException')->during('post', ['product',$productjson]);
    }


    public function it_should_throw_a_Not_found_exception_when_the_response_is_a_404()
    {
        $client = new client();
        $mock = new mock([new response(404)]);
        $client->getemitter()->attach($mock);

        $this->beConstructedWith($client, $this->getDefaultConfigArray());
        $this->shouldThrow('Flubit\NotFoundException')->during('get', ['account/ss']);
    }
}
