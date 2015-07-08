<?php

namespace Flubit\Client;

use GuzzleHttp\Client as httpClient;

abstract class FlubitApiFactory
{
    public function factory(array $config)
    {
        $flubitClient = new ApiClient(new httpClient(), $config);
        return new FlubitApi($flubitClient);
    }
}
