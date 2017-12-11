# evias/nem-php

[![Build Status](https://api.travis-ci.org/evias/nem-php.svg?branch=dev-1.0.0)](https://travis-ci.com/evias/nem-php)
[![Latest Stable Version](https://poser.pugx.org/evias/nem-php/version)](https://packagist.org/packages/evias/nem-php)
[![Latest Unstable Version](https://poser.pugx.org/evias/nem-php/v/unstable)](//packagist.org/packages/evias/nem-php)
[![License](https://poser.pugx.org/evias/nem-php/license)](https://packagist.org/packages/evias/nem-php)

This package aims to provide with an easy-to-use PHP Laravel Namespace helping developers to communicate with the NEM blockchain through its NIS API.

This package should be an aid to any developer working on Laravel/Lumen/Symfony applications with the NEM blockchain.

**This package is currently still in development, please do not use in production.**

*The author of this package cannot be held responsible for any loss of money or any malintentioned usage forms of this package. Please use this package with caution.*

Package licensed under [MIT](LICENSE) License.

## Documentation

Reader-friendly Documentation will be added in development period and will be available on the Github Wiki at [evias/nem-php Wiki](https://github.com/evias/nem-php/wiki).

## Pot de vin

If you like the initiative, and for the sake of good mood, I recommend you take a few minutes to Donate a beer or Three [because belgians like that] by sending some XEM (or whatever Mosaic you think pays me a few beers someday!) to my Wallet:

    NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW

| Username | Role |
| --- | --- |
| [eVias](https://github.com/evias) | Project Lead |
| [RobertoSnap](https://github.com/RobertoSnap) | Developer |

## Installation

You can install this package with Composer. You only need to require evias/nem-php.

```bash
composer require evias/nem-php
```

The package can also be download manually by cloning this repository or by download the packagist archive:

- [nem-php at Packagist](https://packagist.org/packages/evias/nem-php)

Once you have required the package in your `composer.json` file (or using the command above), you can install
the dependencies of this package:

```bash
composer install
```

## Unit Tests

The library provides with a Unit Test Suite for the implemented SDK features.

The unit test suite is also configured on Travis-CI with the current Build Status:

- [![Build Status](https://api.travis-ci.org/evias/nem-php.svg?branch=dev-1.0.0)](https://travis-ci.com/evias/nem-php)

If you wish to run the unitary test suite, you can use the executable file provided by PHPUnit which is located
under `vendor/phpunit/phpunit/phpunit`.

Alernatively, you can create a symbolic link to this executable file in the `nem-php` clone root folder.

```bash
ln -s vendors/phpunit/phpunit/phpunit .
```

Now you can simply run `phpunit` in the terminal and it will launch the Rocket.. meh, the Unit Tests Suite.

### Laravel advanced features

Modify your `config/app.php` configuration file to include the NEM\ServiceProvider service provider. Look out
for the **providers** configuration array and add our class as shown below:

```php
'providers' => [
    NEM\ServiceProvider::class,
],
```

If you wish to make use of the Laravel Facades provided by this library, you will also need to list the alias
in your `config/app.php` configuration file under the **aliases** configuration array as described below:

```php
'aliases' => [
    'NemSDK' => NEM\Facades\NemSDK::class,
],
```

## Usage / Examples

When you have installed the evias/nem-php package you will be able to use the API class to send API requests to the configured NIS. By default, the config/nem.php file defines the localhost NIS to be used, this can be changed.

If you are using Laravel or Lumen, you will need to register the Service Provider of this package into your app:

```php
    // Laravel/Lumen registering the service provider
    $app = Laravel\Lumen\Application(realpath(__DIR__));
    $app->register(NEM\ServiceProvider::class);
```

### Example 1: Using the Service Provider (Laravel only)

```php
    // Example 1: Using the Service Provider
    // --------------------------------------
    // The Service Provider for Laravel/Lumen will bind "nem.config",
    // "nem" and "nem.ncc" in the IoC. "nem" and "nem.ncc" are pre-
    // configured instances of the API class using APP_ENV for the environment.
    $nemConfig = $app["nem.config"]
    $nemAPI = $app["nem"];
    $nccAPI = $app["nem.ncc"];
```

### Example 2: Using the API wrapper

```php
    // Example 2: Instantiating the API class
    // --------------------------------------
    // You can also create a new instance of the API
    $nemAPI = new NEM\API();
    $nemAPI->setOptions([
        "protocol" => "http",
        "use_ssl" => false,
        "host" 	  => "go.nem.ninja",
        "port"    => 7890,
        "endpoint" => "/",
    ]);

    // If you wish you can define your own RequestHandler, have a look at the
    // NEM\Contracts\RequestHandler interface.
    $nemAPI->setOptions(["handler_class" => Path\To\My\Handler::class]);
```

### Example 3: Sending GET/POST request to the NIS API and getting back JSON

```php
    // Example 3: Sending GET/POST JSON requests
    // -----------------------------------------
    // The API wrapper class can be used to send API requests to the
    // configured NIS host with following snippet:
    $response = $nemAPI->getJSON("heartbeat", "");

    // sending JSON through POST and receiving JSON back.
    $postData = ["myField" => "hasThisValue", "yourField" => "isNotEmpty"];
    $response = $nemAPI->postJSON("post/endpoint", json_encode($postData));
```

### Example 4: Custom Headers and response callback configurations

```php
    // Example 4: Custom headers and Callback configuration
    // -----------------------------------------------------
    // The 3rd parameter of the get() and post() methods lets you pass
    // an options array to the RequestHandler. To add specific headers for
    // example you would do as follows:
    $response = $nemAPI->getJSON("hearbeat", "", ["headers" => ["Content-Type" => "text/xml"]]);

    // You may also define onSuccess, onError and onReject callbacks to be executed
    // when the Guzzle Promises respectively complete, encounter an error or are denied.
    // @see Psr\Http\Message\ResponseInterface
    // @see GuzzleHttp\Exception\RequestException
    $response = $nemAPI->getJSON("heartbeat", "", [
        "onSuccess" => function(ResponseInterface $response) {
            echo $response->getBody();
        },
        "onError" => function(RequestException $exception) {
            echo "This is bad: " . $exception->getMessage();
        },
        "onReject" => function($reason) {
            echo "Request could not be completed: " . $reason;
        }
    ]);
```

### Example 5: Use the SDK to create NEM *NIS compliant* Objects

```php
    // Example 5: Use the SDK to create NEM *NIS compliant* Objects
    // ------------------------------------------------------------
    // You can create an instance and pass the *connection configuration*
    $sdk = new SDK([
        "protocol" => "http",
        "use_ssl" => false,
        "host" 	  => "go.nem.ninja",
        "port"    => 7890,
        "endpoint" => "/",
    ]);

    // Or you can use an already initialized API client
    $sdk = new SDK([], new API());
    $account = $sdk->models()->account(["address" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"]);

    // The \NEM\Contracts\DataTransferObject interface tells us that Models
    // always have a toDTO() method which will format the said object into
    // its *NIS compliant* object.

    // Dump [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair) object
    var_dump($account->toDTO());
```

### Example 6: Use the SDK NIS Web Service implementations

```php
    // Example 6: Use the SDK NIS Web Service implementations
    // ------------------------------------------------------------
    $sdk = new SDK();
    $service = $sdk->account();

    // Generate a new account
    $account = $service->generateAccount(); // $account is an instance of \NEM\Models\Account

    // Read account data *from the NEM blockchain* using the Address
    $account = $service->getFromAddress("TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ");

    // Read account data *from the NEM blockchain* using the Public Key
    $account = $service->getFromPublicKey("d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04");
```

### Example 7: Use the SDK to read an account's transactions

```php
    // Example 7: Use the SDK to read an account's transactions
    // ------------------------------------------------------------
    $sdk = new SDK();
    $service = $sdk->account();

    // Get incoming transaction for an account by its address
    // $incomings will be an instance of \NEM\Models\ModelCollection
    $incomings = $service->incomingTransactions("TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ");

    // Get outgoing transaction for an account by its address
    $outgoings = $service->outgoingTransactions("TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ");

    // Get unconfirmed transaction for an account by its address
    $unconfirmed = $service->unconfirmedTransactions("TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ");
```

## Changelog

Important versions listed below. Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

- [1.0.0](CHANGELOG.md#v100) - revamp of the SDK
- [0.0.3](CHANGELOG.md#v003) - ongoing development
- [0.0.2](CHANGELOG.md#v002) - 2017-02-18
- [0.0.1](CHANGELOG.md#v001) - 2017-02-04

## License

This software is released under the [MIT](LICENSE) License.

© 2017 Grégory Saive <greg@evias.be>, All rights reserved.
