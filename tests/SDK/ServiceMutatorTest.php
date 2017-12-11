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
namespace NEM\Tests\SDK;

use GuzzleHttp\Exception\ConnectException;
use PHPUnit_Framework_TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Mutators\ModelMutator;
use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\ModelCollection;

class ServiceMutatorTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * The NIS API Client
     *
     * @var \NEM\API
     */
    protected $client;

    /**
     * The NEM SDK instance
     *
     * @var \NEM\SDK
     */
    protected $sdk;

    /**
     * The setUp method of this test case will
     * instantiate the API using the bigalice2.nem.ninja
     * NIS testnet node.
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

        $this->sdk = new SDK();
        $this->sdk->setAPIClient($this->client);
    }

    /**
     * Test basic details of the SDK instance
     *
     * @return void
     */
    public function testSDKBaseMethods()
    {
        $this->assertTrue($this->sdk->getAPIClient() instanceof API);
        $this->assertEquals("bigalice2.nem.ninja", $this->sdk->getAPIClient()->getRequestHandler()->getHost());
        $this->assertTrue($this->sdk->models() instanceof ModelMutator);
        $this->assertTrue($this->sdk->collect("model", []) instanceof ModelCollection);
    }

    /**
     * Test invalid Service name error case.
     *
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Infrastructure class '\NEM\Infrastructure\InvalidServiceName' could not be found in \NEM\Infrastructure namespace.
     */
    public function testSDKServiceMutator()
    {
        $this->sdk->invalidServiceName();
    }

    /**
     * Test base Service instantiation for unimplemented API Endpoints.
     *
     * @return void
     */
    public function testSDKServiceBaseMutation()
    {
        $service = $this->sdk->service();

        $this->assertTrue($service instance \NEM\Infrastructure\Service);
    }
}
