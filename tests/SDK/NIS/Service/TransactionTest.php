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
use Mockery;

use NEM\Infrastructure\Transaction;
use NEM\Core\KeyPair;
use NEM\Models\Transaction as TxModel;

class TransactionTest
    extends TestCase
{
    /**
     * Unit Test for *base URL of transaction endpoint*.
     *
     * @return  void
     */
    public function testBaseUrl()
    {
        $mock   = Mockery::mock("NEM\Infrastructure\Transaction");
        $expect = "/transaction";
        $instance = new Transaction();

        // first test mockery
        $mock->shouldReceive("getBaseUrl")
                   ->andReturn($expect);

        $result = $mock->getBaseUrl();
        $this->assertEquals($expect, $result);

        // then test value
        $value = $instance->getBaseUrl();
        $this->assertEquals($expect, $value);
    }

    /**
     * Unit Test for getAnnouncePath() method.
     *
     * @return  void
     */
    public function testGetAnnouncePathDetectsKeyPair()
    {
        $instance = new Transaction();
        $keypair  = new KeyPair();
        $trx = new TxModel();

        // whenever a keypair is passed, this keypair will be used
        // for signing the passed transaction
        $endpointRecommended = $instance->getAnnouncePath($trx, $keypair);
        $endpointWarning = $instance->getAnnouncePath($trx, null);

        // expected values
        $expectReco = "announce";
        $expectWarn = "prepare-announce";

        $this->assertEquals($expectReco, $endpointRecommended);
        $this->assertEquals($expectWarn, $endpointWarning);
    }

    /**
     * Unit Test for signTransaction() method.
     *
     * @return  void
     */
    public function testSignTransactionDetectsKeyPair()
    {
        $instance = new Transaction();
        $keypair  = new KeyPair();
        $trx = new TxModel();

        $signature = $instance->signTransaction($trx, $keypair);
        $nullSig   = $instance->signTransaction($trx, null);

        // test LOCAL SIGNATURE with KeyPair
        $this->assertInstanceOf(\NEM\Core\Buffer::class, $signature);
        $this->assertEquals(64, $signature->getSize());

        // test EMPTY SIGNATURE without KeyPair
        $this->assertEquals(null, $nullSig);
    }
}
