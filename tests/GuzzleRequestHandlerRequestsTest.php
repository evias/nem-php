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
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace evias\NEMBlockchain\Tests;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

use evias\NEMBlockchain\API;
use evias\NEMBlockchain\Exception\NISNotAvailableException;

class GuzzleRequestHandlersRequestsTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The evias NEM Blockchain API Client
     * @var \evias\NEMBlockchain\API
     */
    protected $client;

	/**
	 * The setUp method of this test case will
     * instantiate the API using the go.nem.ninja
     * NIS node.
     *
     * @see :Execution of this Test Case requires an Internet Connection
	 * @return void
	 */
    public function setUp()
    {
        parent::setUp();

        $config = [
            "use_ssl"  => false,
            "protocol" => "http",
            "host" => "go.nem.ninja", // testing uses online NIS
            "port" => 7890,
            "endpoint" => "/",
        ];

		// each test should have its own API configured
		$this->client = new API();
		$this->client->setOptions($config);

        // test hearbeat on NIS to make sure the Internet Connection is up.
        try {
            $response = $this->client->getJSON("heartbeat", "", [], false);
        }
        catch (ConnectException $e) {
            throw new NISNotAvailableException("Could not establish connection to NIS node \"go.nem.ninja:7890\".");
        }
    }

    /**
     * This test will only check if the RequestHandler
     * instance is correctly handling the GET request
     * and provides with a ResponseInterface.
     *
     * This test is NOT using promises.
     *
     * @return void
     */
    public function testSynchronousGetRequest()
    {
        try {
            $response  = $this->client->get("heartbeat", "", [], false);

            $this->assertTrue($response instanceof ResponseInterface);
        }
        catch (ConnectException $e) {
            throw new NISNotAvailableException("Could not establish connection to NIS node \"go.nem.ninja:7890\".");
        }
    }

    /**
     * This test will only check if the RequestHandler
     * instance is correctly handling the GET request
     * and provides with a ResponseInterface.
     *
     * This test WILL use promises.
     *
     * @return void
     */
    public function testAsynchronousGetRequest()
    {
        try {
            $response = $this->client->get("heartbeat", "", [], true);

            $this->assertTrue($response instanceof ResponseInterface);
        }
        catch (ConnectException $e) {
            throw new NISNotAvailableException("Could not establish connection to NIS node \"go.nem.ninja:7890\".");
        }
    }
}
