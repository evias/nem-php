<?php
/**
 * Part of the evias/php-nem-laravel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/php-nem-laravel
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace evias\NEMBlockchain\Tests;

use PHPUnit_Framework_TestCase;
use evias\NEMBlockchain\API;

class RequestHandlersConfigurationTest
    extends PHPUnit_Framework_TestCase
{
	/**
	 * This test checks whether the API class returns
	 * a valid GuzzleRequestHandler instance when a
	 * `handler_class` config provides the given
	 * GuzzleRequestHandler class.
	 *
	 * @return void
	 */
    public function testGuzzleConfiguration()
    {
		$config = ["handler_class" => \evias\NEMBlockchain\Handlers\GuzzleRequestHandler::class];

		// each test should have its own API configured
		$client = new API();
		$client->setOptions($config);

		$handler = $client->getRequestHandler();
		$this->assertTrue($handler instanceof \evias\NEMBlockchain\Handlers\GuzzleRequestHandler);
    }

	/**
	 * This test checks whether the API class returns
	 * a valid UnirestRequestHandler instance when a
	 * `handler_class` config provides the given
	 * UnirestRequestHandler class.
	 *
	 * @return void
	 */
    public function testUnirestConfiguration()
    {
		$config = ["handler_class" => \evias\NEMBlockchain\Handlers\UnirestRequestHandler::class];

		// each test should have its own API configured
		$client = new API();
		$client->setOptions($config);

		$handler = $client->getRequestHandler();
		$this->assertTrue($handler instanceof \evias\NEMBlockchain\Handlers\UnirestRequestHandler);
    }

    /**
     * This test checks whether the API class correctly
     * handles the host, port, endpoint and use_ssl
     * options provided per config.
     *
     * @return void
     */
    public function testConnectableTraitHttpConfiguration()
    {
    	$config = [
            "use_ssl"  => true,
            "protocol" => "http",
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ];

        $client = new API();
        $client->setOptions($config);

        $connectable = $client->getRequestHandler();

        $this->assertTrue($connectable->getUseSsl());
        $this->assertEquals("http", $connectable->getProtocol());
        $this->assertEquals("127.0.0.1", $connectable->getHost());
        $this->assertEquals(7890, $connectable->getPort());
        $this->assertEquals("/", $connectable->getEndpoint());
        $this->assertEquals("https://", $connectable->getScheme());
        $this->assertEquals("https://127.0.0.1:7890/", $connectable->getBaseUrl());
    }

    /**
     * This test checks whether the API class correctly
     * handles the host, port, endpoint and use_ssl
     * options provided per config.
     *
     * @return void
     */
    public function testConnectableTraitWebsocketConfiguration()
    {
        $config = [
            "use_ssl"  => true,
            "protocol" => "ws",
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ];

        $client = new API();
        $client->setOptions($config);

        $connectable = $client->getRequestHandler();

        $this->assertTrue($connectable->getUseSsl());
        $this->assertEquals("ws", $connectable->getProtocol());
        $this->assertEquals("127.0.0.1", $connectable->getHost());
        $this->assertEquals(7890, $connectable->getPort());
        $this->assertEquals("/", $connectable->getEndpoint());
        $this->assertEquals("wss://", $connectable->getScheme());
        $this->assertEquals("wss://127.0.0.1:7890/", $connectable->getBaseUrl());
    }

    //XXX add test for Basic Authentication feature!
}
