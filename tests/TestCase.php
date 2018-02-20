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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Tests;

use PHPUnit\Framework\TestCase as BaseTest;
use Mockery;
use NEM\API;
use NEM\SDK;

abstract class TestCase 
    extends BaseTest
{
    /**
     * The evias NEM Blockchain API Client
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
     * Setup unit test cases
     *
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

        // each test should have its own SDK instance
        $this->sdk = new SDK([], $this->client);
    }

    /**
     * Close the mockery operator.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }
}
