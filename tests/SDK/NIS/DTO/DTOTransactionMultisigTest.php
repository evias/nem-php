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
namespace NEM\Tests\SDK\NIS\DTO;

use NEM\Tests\SDK\NIS\NISComplianceTestCase;
use NEM\Models\TransactionType;
use NEM\Models\Transaction;
use NEM\Models\Fee;
use NEM\Models\Amount;
use NEM\Models\Message;
use NEM\Models\Transaction\Multisig;
use NEM\Models\Signatures;
use NEM\Models\TimeWindow;
use NEM\Models\Transaction\Signature;
use NEM\Models\Transaction\Transfer;

class DTOTransactionMultisigTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty Multisig*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new Multisig();
        $transactionNIS = $transaction->toDTO();
        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("otherTrans", $content);
        $this->assertArrayHasKey("signatures", $content);

        // mode has a default value
        $expectType = TransactionType::MULTISIG;

        $this->assertEquals($expectType, $content["type"]);
    }

    /**
     * Unit test for *DTO structure of ImportanceTransfer instances*
     * 
     * @depends testDTODefaultValues
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testNestingMultisigNotPossible()
    {
        $otherTrans = new Multisig();
        $txData = [
            "otherTrans" => $otherTrans->toDTO("transaction"),
            "signatures" => (new Signatures([
                new Signature(["signer" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"])
            ]))->toDTO(),
        ];

        // @throws InvalidArgumentException
        $transaction = new Multisig($txData);
    }

    /**
     * Unit test for *DTO structure of ImportanceTransfer instances*
     * 
     * @depends testDTODefaultValues
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testNestingSignatureNotPossible()
    {
        $otherTrans = new Signature();
        $txData = [
            "otherTrans" => $otherTrans->toDTO("transaction"),
            "signatures" => (new Signatures([
                new Signature(["signer" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"])
            ]))->toDTO(),
        ];

        // @throws InvalidArgumentException
        $transaction = new Multisig($txData);
    }

    /**
     * Unit test for *DTO structure of Multisig instances*
     * 
     * @depends testDTODefaultValues
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $otherTrans = new Transfer([
            "recipient" => "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
            "amount"    => 10000000,
            "message"   => (new Message(["plain" => "Hello, Multi-Greg!"]))->toDTO(),
            "timeStamp" => (new TimeWindow(["timeStamp" => 91828160]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91831760]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => 2 * Fee::FEE_FACTOR, // transfer + message < 31 chars
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

        $txData = [
            "timeStamp" => (new TimeWindow(["timeStamp" => 91828160]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91831760]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::MULTISIG,
            "otherTrans" => $otherTrans->toDTO("transaction"),
            "signatures" => (new Signatures([
                new Signature(["signer" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"])
            ]))->toDTO(),
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
        ];
        $transaction = new Multisig($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("otherTrans", $content);
        $this->assertArrayHasKey("signatures", $content);

        // test inner transaction structure
        $this->assertInternalType("array", $content["otherTrans"]);
        $this->assertArrayHasKey("recipient", $content["otherTrans"]);
        $this->assertArrayHasKey("amount", $content["otherTrans"]);
        $this->assertArrayHasKey("message", $content["otherTrans"]);
        $this->assertArrayHasKey("type", $content["otherTrans"]);
        $this->assertArrayHasKey("timeStamp", $content["otherTrans"]);
        $this->assertArrayHasKey("deadline", $content["otherTrans"]);
        $this->assertArrayHasKey("fee", $content["otherTrans"]);
        $this->assertArrayHasKey("version", $content["otherTrans"]);

        // expected values in otherTrans
        $otherTransNIS = $content["otherTrans"];
        $expectOtherType = TransactionType::TRANSFER;
        $expectOtherAcct = "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT";
        $expectOtherMsg  = "48656c6c6f2c204d756c74692d4772656721";
        $expectTime = 91828160;
        $expectDead = $expectTime + 3600;
        $expectAmt  = 10000000;
        $expectVer  = -1744830463;
        $expectFee  = 2 * Fee::FEE_FACTOR;

        // test inner transaction content
        $this->assertEquals($expectOtherType, $otherTransNIS["type"]);
        $this->assertEquals($expectOtherAcct, $otherTransNIS["recipient"]);
        $this->assertEquals($expectAmt, $otherTransNIS["amount"]);
        $this->assertEquals($expectTime, $otherTransNIS["timeStamp"]);
        $this->assertEquals($expectDead, $otherTransNIS["deadline"]);
        $this->assertEquals($expectVer, $otherTransNIS["version"]);
        $this->assertEquals($expectFee, $otherTransNIS["fee"]);

        // test 2nd-level sub-dto (message in transfer)
        $this->assertInternalType("array", $otherTransNIS["message"]);
        $this->assertArrayHasKey("payload", $otherTransNIS["message"]);
        $this->assertArrayHasKey("type", $otherTransNIS["message"]);

        $message = $otherTransNIS["message"];
        $this->assertEquals($expectOtherMsg, $message["payload"]);

        // test signatures in root transaction
        $this->assertInternalType("array", $content["signatures"]);
        $this->assertCount(1, $content["signatures"]);

        $signature = $content["signatures"][0];

        // signature structure validation
        $this->assertArrayHasKey("timeStamp", $signature);
        $this->assertArrayHasKey("fee", $signature);
        $this->assertArrayHasKey("type", $signature);
        $this->assertArrayHasKey("version", $signature);
        $this->assertArrayHasKey("deadline", $signature);
        $this->assertArrayHasKey("signer", $signature);

        // if no data is set, the fee will be the same
        $this->assertEquals(Fee::MULTISIG, $content["fee"]);

        // each signature costs a `fee` too
        $this->assertEquals(Fee::SIGNATURE, $signature["fee"]);
    }
}
