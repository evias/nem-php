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
use NEM\Core\Buffer;
use NEM\Models\Transaction as TxModel;
use NEM\Models\Transaction\Transfer;
use NEM\Models\TimeWindow;
use NEM\Models\Fee;

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

    /**
     * Unit test for *transaction signature* on the NEM Network with
     * a valid KeyPair.
     * 
     * @link http://bigalice2.nem.ninja:7890/transaction/get?hash=0e7c7b6cbbb5489132ec28f2bcf409dc0235983b6d33a8fe4a7b2fe58aeab8f2
     * @return void
     */
    public function testTransactionSignatureWithValidKeyPair_First()
    {
        $instance = new Transaction();
        $keypair  = new KeyPair("dd19f3f3178c0867771eed180310a484e1b76527f7a271e3c8b5264e4a5aa414");
        $trx = new Transfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91987914]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91991514]))->toDTO(),
            "amount"    => 100000,
            "recipient" => "TA3SH7QUTG6OS4EGHSES426552FAJYZR2PHOBLNA",
            "fee"       => 50000,
            "version"   => -1744830463,
        ]);

        $signature  = $instance->signTransaction($trx, $keypair);
        $serialized = $trx->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected values
        $expectUInt8 = [1,1,0,0,1,0,0,152,202,159,123,5,32,0,0,0,86,69,234,91,107,252,155,206,110,105,234,182,0,34,129,208,233,197,47,192,64,90,185,149,51,210,142,73,123,150,237,129,80,195,0,0,0,0,0,0,218,173,123,5,40,0,0,0,84,65,51,83,72,55,81,85,84,71,54,79,83,52,69,71,72,83,69,83,52,50,54,53,53,50,70,65,74,89,90,82,50,80,72,79,66,76,78,65,160,134,1,0,0,0,0,0,0,0,0,0];
        $expectSerialHex = "0101000001000098ca9f7b05200000005645ea5b6bfc9bce6e69eab6002281d0e9c52fc0405ab99533d28e497b96ed8150c3000000000000daad7b052800000054413353483751555447364f533445474853455334323635353246414a595a523250484f424c4e41a08601000000000000000000";

        $expectSize = 64;
        $expectHex  = "a988066f237e47a887366c6d736a957c5411e30d809a3f3214926951fee2189329b0f99c270aaa9c47011abf36c0d9e873edd9405f6317486cb0996425c9f207";

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSerialHex, $serialHex);
        $this->assertEquals($expectSize, $signature->getSize());
        $this->assertEquals($expectHex, $signature->getHex());
    }

    /**
     * Unit test for a past transaction on the NEM network, this is to verify
     * the transaction signature process.
     * 
     * @link http://bigalice2.nem.ninja:7890/transaction/get?hash=f85e1a99f893f98f554b14f2a2cd92adf0895baff378de2f0f49235595d729a3
     * @return void
     */
    public function testTransactionSignatureWithValidKeyPair_Second()
    {
        $instance = new Transaction();
        $keypair  = new KeyPair("dd19f3f3178c0867771eed180310a484e1b76527f7a271e3c8b5264e4a5aa414");
        $trx = new Transfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 92011454]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 92015054]))->toDTO(),
            "amount"    => 100000,
            "recipient" => "TA3SH7QUTG6OS4EGHSES426552FAJYZR2PHOBLNA",
            "fee"       => 50000,
            "version"   => -1744830463,
        ]);

        $signature  = $instance->signTransaction($trx, $keypair);
        $serialized = $trx->serialize(true);
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected values
        $expectUInt8 = [1,1,0,0,1,0,0,152,190,251,123,5,32,0,0,0,86,69,234,91,107,252,155,206,110,105,234,182,0,34,129,208,233,197,47,192,64,90,185,149,51,210,142,73,123,150,237,129,80,195,0,0,0,0,0,0,206,9,124,5,40,0,0,0,84,65,51,83,72,55,81,85,84,71,54,79,83,52,69,71,72,83,69,83,52,50,54,53,53,50,70,65,74,89,90,82,50,80,72,79,66,76,78,65,160,134,1,0,0,0,0,0,0,0,0,0];
        $expectSerialHex = "0101000001000098befb7b05200000005645ea5b6bfc9bce6e69eab6002281d0e9c52fc0405ab99533d28e497b96ed8150c3000000000000ce097c052800000054413353483751555447364f533445474853455334323635353246414a595a523250484f424c4e41a08601000000000000000000";

        $expectSize = 64;
        $expectSign = "7c1131070a222f16674bbc4edc08aa9485eeac74a08a82519e12e4d150a78d7c63d6e12e0bef3ba9ee0c946e44ade696d8969662e8838325e8ad86d2a178de0f";

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSerialHex, $serialHex);
        $this->assertEquals($expectSize, $signature->getSize());
        $this->assertEquals($expectSign, $signature->getHex());
    }
}
