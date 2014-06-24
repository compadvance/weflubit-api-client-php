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

### Configure your API credentials

First copy the distribution config file to the correct location:

```
cp config.php.dist config.php
```

Then edit the file to include your WeFlubit API credentials:

```
define('CONSUMER_KEY', '2827-1946-2647');
define('CONSUMER_SECRET', 'yiddjcoyyssksk04oc8sooc8wk2sk0ksgw40cw0gosgkwwocc');
define('DOMAIN', 'api.sandbox.weflubit.com');

```
(You can copy your API credentials from the [integration tab](https://weflubit.com/merchant#/settings/integration) of your WeFlubit account settings page.)

### Run the test script

You can run the PHP test script to call the GET account/status API endpoint. A successful respnse means that everything is configured correctly (Note: The script terminates at [line 25] (https://github.com/Flubit/merchant-api-client-php/blob/master/test.php#L25) to prevent test data being sent to the API). 

```
php test.php
```
