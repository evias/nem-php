<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Tests\API;

use PHPUnit_Framework_TestCase;
use NEM\API;

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
        $config = ["handler_class" => \NEM\Handlers\GuzzleRequestHandler::class];

        // each test should have its own API configured
        $client = new API();
        $client->setOptions($config);

        $handler = $client->getRequestHandler();
        $this->assertTrue($handler instanceof \NEM\Handlers\GuzzleRequestHandler);
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

    /**
     * This test checks whether the API class correctly
     * handles the username and password pair
     * options provided per config.
     *
     * @return void
     */
    public function testHttpBasicAuthenticationConfiguration()
    {
        $config = [
            "use_ssl"  => true,
            "protocol" => "http",
            "username" => "nem",
            "password" => "nemdev",
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ];

        $client = new API();
        $client->setOptions($config);

        $connectable = $client->getRequestHandler();

        $this->assertEquals("nem", $connectable->getUsername());
        $this->assertEquals("nemdev", $connectable->getPassword());
        $this->assertEquals("https://nem:nemdev@127.0.0.1:7890/", $connectable->getBaseUrl());
    }
}
