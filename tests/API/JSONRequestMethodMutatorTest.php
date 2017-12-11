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
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

use NEM\API;
use NEM\Errors\NISNotAvailableException;

class JSONRequestMethodMutatorTest
    extends PHPUnit_Framework_TestCase
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

        // test hearbeat on NIS to make sure the Internet Connection is up.
        try {
            $response = $this->client->getJSON("heartbeat", "", [], false);
        }
        catch (ConnectException $e) {
            $this->fail("Could not establish connection to NIS node \"bigalice2.nem.ninja:7890\".");
        }
    }

    /**
     * This will test the *JSON Request Method Mutator* in NEM\API.
     *
     * The method mutator is supposed to automatically return a JSON
     * response whenever you call methods `getJSON` and `postJSON`.
     *
     * This test will only make sure that the response is indeed cast
     * to the *string* type.
     *
     * @return void
     */
    public function testGetJSONMethodMutator()
    {
        try {
            $response = $this->client->getJSON("heartbeat", "", [], false);

            $this->assertFalse($response instanceof ResponseInterface);
            $this->assertTrue(is_string($response));
        }
        catch (ConnectException $e) {
            $this->fail("Could not establish connection to NIS node \"bigalice2.nem.ninja:7890\".");
        }
    }
}
