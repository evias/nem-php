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

use NEM\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

use NEM\API;
use NEM\Errors\NISNotAvailableException;

class GuzzleRequestHandlersRequestsTest
    extends TestCase
{
    /**
     * The evias NEM Blockchain API Client
     * @var \NEM\API
     */
    protected $client;

    /**
     * The setUp method of this test case will
     * instantiate the API using the bigalice2.nem.ninja
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
            "host" => "bigalice2.nem.ninja", // testing uses online NIS
            "port" => 7890,
            "endpoint" => "/",
        ];

        // each test should have its own API configured
        $this->client = new API();
        $this->client->setOptions($config);
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
            $response = $this->client->get("heartbeat", "", [], false);

            $this->assertTrue($response instanceof ResponseInterface);
        }
        catch (ConnectException $e) {
            // HOST DOWN, not feature!
            $this->assertTrue(false !== strpos(strtolower($e->getMessage()), "failed to connect"));
            $this->fail("Could not establish connection to remote node 'bigalice2.nem.ninja:7890'.");
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
            // HOST DOWN, not feature!
            $this->assertTrue(false !== strpos(strtolower($e->getMessage()), "failed to connect"));
            $this->fail("Could not establish connection to remote node 'bigalice2.nem.ninja:7890'.");
        }
    }
}
