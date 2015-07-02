Merchant API Client
===================

This library can be used to call the WeFlubit API. See the [API documentation] (https://docs.google.com/document/d/16jF1xNzJ2J7aTH8Xfijok9-sMHKAVGTl9ggATR4_Ybk/edit?usp=sharing).

## Setup

### Install Dependencies

You will need to use the PHP dependency manager [Composer](https://getcomposer.org) to install the vendor libraries required by the API client:

```
git clone git@github.com:Flubit/merchant-api-client-php.git
cd merchant-api-client-php
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```
### Library Use

You can use the library by simply setting an array with you credentials and calling the factory:

```

        $config  = [
            'key' => '2827-1946-2647',
            'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
            'baseUrl' => 'api.sandbox.weflubit.com',
        ];
        $flubitClient =  \Flubit\Client\FlubitApiFactory::factory($config);

```

this will provide you of a wrapper of the available methods at the moment on the API


The library has a second component ApiClient that has only some exception handle and manages the validations for each request 

That you could instantiate as  
```

        $config  = [
            'key' => '2827-1946-2647',
            'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
            'baseUrl' => 'api.sandbox.weflubit.com',
        ];
        $flubitClient = new ApiClient(new GuzzleHttp\Client(), $config);
        
```

And you can call to the endpoints directly and will return an Guzzle\Message\Response object


On the test.php file you can find some really easy examples of use of the library
