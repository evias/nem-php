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
namespace NEM\Tests\API;

use NEM\Tests\TestCase;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

use Mockery;
use NEM\API;
use NEM\Errors\NISNotAvailableException;

class GuzzleRequestHandlersRequestsTest
    extends TestCase
{

    /**
     * Unit Test for *return type of synchronous STATUS request*.
     *
     * @return  void
     */
    public function testStatusRequestReturnType_Synchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("status")
                   ->with([], false)
                   ->andReturn($response);

        $result = $instance->status([], false);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }

    /**
     * Unit Test for *return type of asynchronous STATUS request*.
     *
     * @return  void
     */
    public function testStatusRequestReturnType_Asynchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("status")
                   ->with([], true) // async
                   ->andReturn($response);

        $result = $instance->status([], true); // async
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }
    /**
     * Unit Test for *return type of synchronous GET request*.
     *
     * @return  void
     */
    public function testGetRequestReturnType_Synchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("get")
                   ->with("heartbeat", "", [], false)
                   ->andReturn($response);

        $result = $instance->get("heartbeat", "", [], false);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }

    /**
     * Unit Test for *return type of asynchronous GET request*.
     *
     * @return  void
     */
    public function testGetRequestReturnType_Asynchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("get")
                   ->with("heartbeat", "", [], true) // async
                   ->andReturn($response);

        $result = $instance->get("heartbeat", "", [], true); // async
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }

    /**
     * Unit Test for *return type of synchronous POST request*.
     *
     * @return  void
     */
    public function testPostRequestReturnType_Synchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("post")
                   ->with("heartbeat", "", [], false)
                   ->andReturn($response);

        $result = $instance->post("heartbeat", "", [], false);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }

    /**
     * Unit Test for *return type of asynchronous POST request*.
     *
     * @return  void
     */
    public function testPostRequestReturnType_Asynchronous()
    {
        $instance = Mockery::mock("NEM\API");
        $response = Mockery::mock("Psr\Http\Message\ResponseInterface");

        $instance->shouldReceive("post")
                   ->with("heartbeat", "", [], true) // async
                   ->andReturn($response);

        $result = $instance->post("heartbeat", "", [], true); // async
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
    }
}
