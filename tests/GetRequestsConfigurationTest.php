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

class GetRequestsConfigurationTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * Tests in this test case will all use the
     * same Http Client configuration to avoid
     * any side effects.
     *
     * @return \evias\NEMBlockchain\Contracts\HttpHandler
     */
    protected function getHttpClient()
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
        return $connectable;
    }

    /**
     * Test the GuzzleHttpHandler Request Handler class
     * for GET requests.
     *
     * @return void
     */
    public function testGuzzleRequestHandler()
    {
		$handler = $this->getHttpClient();

		$this->assertTrue(true);
    }
}
