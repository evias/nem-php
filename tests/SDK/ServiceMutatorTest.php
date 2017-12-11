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
namespace NEM\Tests\SDK;

use GuzzleHttp\Exception\ConnectException;
use PHPUnit_Framework_TestCase;

use NEM\API;
use NEM\SDK;

class ServiceMutatorTest
    extends PHPUnit_Framework_TestCase
{
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
    }

    /**
     * Test the SDK instantiation
     *
     * @return void
     */
    public function testSDKInstantiation()
    {
    }
}
