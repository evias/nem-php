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

class HttpHandlersConfigurationTest
    extends PHPUnit_Framework_TestCase
{
	/**
	 * This test checks whether the API class returns
	 * a valid GuzzleHttpHandler instance when a
	 * `handler_class` config provides the given
	 * GuzzleHttpHandler class.
	 *
	 * @return void
	 */
    public function testGuzzleConfiguration()
    {
		$config = ["handler_class" => \evias\NEMBlockchain\Handlers\GuzzleHttpHandler::class];

		// each test should have its own API configured
		$client = new API();
		$client->setOptions($config);

		$handler = $client->getHttpHandler();
		$this->assertTrue($handler instanceof \evias\NEMBlockchain\Handlers\GuzzleHttpHandler);
    }

	/**
	 * This test checks whether the API class returns
	 * a valid UnirestHttpHandler instance when a
	 * `handler_class` config provides the given
	 * UnirestHttpHandler class.
	 *
	 * @return void
	 */
    public function testUnirestConfiguration()
    {
		$config = ["handler_class" => \evias\NEMBlockchain\Handlers\UnirestHttpHandler::class];

		// each test should have its own API configured
		$client = new API();
		$client->setOptions($config);

		$handler = $client->getHttpHandler();
		$this->assertTrue($handler instanceof \evias\NEMBlockchain\Handlers\UnirestHttpHandler);
    }

    /**
     * This test checks whether the API class correctly
     * handles the host, port, endpoint and use_ssl
     * options provided per config.
     *
     * @return void
     */
    public function testConnectionPolicyConfiguration()
    {
    	$config = [
            "use_ssl" => true,
            "host" => "127.0.0.1",
            "port" => 7890,
            "endpoint" => "/",
        ];

        $client = new API();
        $client->setOptions($config);

        $connectable = $client->getHttpHandler();

        $this->assertTrue($connectable->getUseSsl());
        $this->assertEquals("127.0.0.1", $connectable->getHost());
        $this->assertEquals(7890, $connectable->getPort());
        $this->assertEquals("/", $connectable->getEndpoint());
    }
}
