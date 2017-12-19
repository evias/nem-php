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

class JSONRequestMethodMutatorTest
    extends TestCase
{
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
            // HOST DOWN, not feature!
            $this->assertTrue(false !== strpos(strtolower($e->getMessage()), "failed to connect"));
            //$this->fail("Could not establish connection to remote node 'bigalice2.nem.ninja:7890'.");
        }
    }
}
