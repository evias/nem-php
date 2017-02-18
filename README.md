# evias/php-nem-laravel

[![Build Status](https://api.travis-ci.org/evias/php-nem-laravel.svg?branch=master)](https://travis-ci.com/evias/php-nem-laravel)
[![Latest Stable Version](https://poser.pugx.org/evias/php-nem-laravel/version)](https://packagist.org/packages/evias/php-nem-laravel)
[![Latest Unstable Version](https://poser.pugx.org/evias/php-nem-laravel/v/unstable)](//packagist.org/packages/evias/php-nem-laravel)
[![License](https://poser.pugx.org/evias/php-nem-laravel/license)](https://packagist.org/packages/evias/php-nem-laravel)

This package aims to provide with an easy-to-use PHP Laravel Namespace helping developers to communicate with the NEM blockchain through its NIS API.

This package should be an aid to any developer working on Laravel/Lumen/Symfony applications with the NEM blockchain.

Package licensed under [MIT](LICENSE) License.

## Documentation

Reader-friendly Documentation will be added in development period and will be available on the Github Wiki at [evias/php-nem-laravel Wiki](https://github.com/evias/php-nem-laravel/wiki).

## Pot de vin

If you like the initiative, and for the sake of good mood, I recommend you take a few minutes to Donate a beer or Three [because belgians like that] by sending some XEM (or whatever Mosaic you think pays me a few beers someday!) to my Wallet:

    NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW

## Installation

You can install this package with Composer. You only need to require evias/php-nem-laravel.

    $ composer require evias/php-nem-laravel

The package can also be download manually by cloning this repository or by download the packagist archive:

	$ https://packagist.org/packages/evias/php-nem-laravel

## Usage / Examples

When you have installed the evias/php-nem-laravel package you will be able to use the API class to send API requests to the configured NIS. By default, the config/nem.php file defines the localhost NIS to be used, this can be changed.

If you are using Laravel or Lumen, you will need to register the Service Provider of this package into your app:

	// Laravel/Lumen registering the service provider
	$app = Laravel\Lumen\Application(realpath(__DIR__));
    $app->register(evias\NEMBlockchain\NemBlockchainServiceProvider::class);

	// Example 1: Using the Service Provider
	// --------------------------------------
    // The Service Provider for Laravel/Lumen will bind "nem.config",
    // "nem" and "nem.ncc" in the IoC. "nem" and "nem.ncc" are pre-
    // configured instances of the API class using APP_ENV for the environment.
    $nemConfig = $app["nem.config"]
    $nemAPI = $app["nem"];
    $nccAPI = $app["nem.ncc"];

	// Example 2: Instantiating the API class
	// --------------------------------------
    // You can also create a new instance of the API
    $myAPI = new evias\NEMBlockchain\API();
    $myAPI->setOptions([
		"use_ssl" => false,
		"host" 	  => "go.nem.ninja",
		"port"    => 7890,
		"endpoint" => "/",
    ]);

    // If you wish you can define your own RequestHandler, have a look at the
    // evias\NEMBlockchain\Contracts\RequestHandler interface.
    $myAPI->setOptions(["handler_class" => Path\To\My\Handler::class]);

	// Example 3: Sending GET/POST JSON requests
	// -----------------------------------------
    // The API wrapper class can be used to send API requests to the
    // configured NIS host with following snippet:
	$response = $nemAPI->getJSON("heartbeat", "");

	// sending JSON through POST and receiving JSON back.
	$postData = ["myField" => "hasThisValue", "yourField" => "isNotEmpty"];
	$response = $nemAPI->postJSON("post/endpoint", json_encode($postData));

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

## Changelog

Important versions listed below. Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

- [0.1.0](CHANGELOG.md#v010) - future release
- [0.0.3](CHANGELOG.md#v003) - ongoing development
- [0.0.2](CHANGELOG.md#v002) - 2017-02-18
- [0.0.1](CHANGELOG.md#v001) - 2017-02-04

## License

This software is released under the [MIT](LICENSE) License.

© 2017 Grégory Saive <greg@evias.be>, All rights reserved.
