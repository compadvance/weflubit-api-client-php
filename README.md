Merchant API Client
===================

This library can be used to call the WeFlubit API. See the [API documentation] (http://dev.weflubit.com).

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

You can use the library by simply passing an array with your credentials to the factory like so:

```
$config  = [
    'key' => '2827-1946-2647',
    'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
    'baseUrl' => 'api.sandbox.weflubit.com',
];
$flubitClient = \Flubit\Client\FlubitApiFactory::factory($config);
```

This will provide you with a wrapper containing the available API methods at the moment.

The library has a second component, `ApiClient`, which has only some exception handling and manages the validations for each request. You can instantiate it like so:

```
$config  = [
    'key' => '2827-1946-2647',
    'secret' => 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc',
    'baseUrl' => 'api.sandbox.weflubit.com',
];
$flubitClient = new \Flubit\Client\ApiClient(new GuzzleHttp\Client(), $config);
        
```

See `test.php` for some really easy examples of how to use this library.
