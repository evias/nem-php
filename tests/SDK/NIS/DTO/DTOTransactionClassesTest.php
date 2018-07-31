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
use NEM\Models\Transaction;
use NEM\Models\TransactionType;
use NEM\Models\Message;
use NEM\Models\TimeWindow;
use NEM\Models\Transaction\Transfer;

class DTOTransactionClassesTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *DTO content of Transaction instances*
     * 
     * This tests the non-specialized \NEM\Models\Transaction
     * class.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $txData = [
            "amount"    => 290888,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
        ];

        $transaction = new Transfer($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        // expected values
        $expectAmount  = 290888;
        $expectType    = TransactionType::TRANSFER;
        $expectVersion = Transaction::VERSION_1;
        $expectRecipient = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";

        $this->assertEquals($expectAmount, $content["amount"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
        $this->assertEquals($expectRecipient, $content["recipient"]);

        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("payload", $content["message"]);
        $this->assertEmpty($content["message"]["payload"]);

        // optional fields were not set
        $this->assertFalse(isset($content["signer"]));
        $this->assertFalse(isset($content["signature"]));
    }

    /**
     * Unit test for *optional fields of Transfer instances*.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOOptionalFields()
    {
        // test optional fields

        $expectSigner    = "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04";
        $expectSignature = "772db34f606969831fb477b3faf6d25fc530e6ea99f0e839085f3313a3279a87"
                         . "62773605114e13f949c3fd48fd058ffb6f02c258434539cb6ccc6285cea2580d";
        $expectAmount    = 0;
        $expectRecipient = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";
        $expectType      = TransactionType::TRANSFER;
        $expectVersion   = Transaction::VERSION_1;

        $txData = [
            "amount"    => 0,
            "recipient" => $expectRecipient,
            "signer"    => $expectSigner,
            "signature" => $expectSignature,
        ];
        $transaction = new Transfer($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertEquals($expectAmount, $content["amount"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
        $this->assertEquals($expectRecipient, $content["recipient"]);
        $this->assertEquals($expectSigner, $content["signer"]);
        $this->assertEquals($expectSignature, $content["signature"]);
    }

    /**
     * Unit test for *DTO content of Transaction instances*
     * 
     * This tests the specialized \NEM\Models\Transaction\Transfer
     * class.
     * 
     * @return void
     */
    public function testDTOContent_Transfer()
    {
        // test obligatory fields
        $txData = [
            "amount"    => 290888,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
        ];

        $transfer = new Transfer($txData);
        $transferNIS = $transfer->toDTO();

        $meta = $transferNIS["meta"];
        $content = $transferNIS["transaction"];

        // expected values
        $expectAmount  = 290888;
        $expectType    = TransactionType::TRANSFER;
        $expectVersion = Transaction::VERSION_1;
        $expectRecipient = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";

        $this->assertEquals($expectAmount, $content["amount"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
        $this->assertEquals($expectRecipient, $content["recipient"]);

        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("payload", $content["message"]);
        $this->assertEmpty($content["message"]["payload"]);

        // test empty fields
        $transfer = new Transfer();
        $transferNIS = $transfer->toDTO();

        $meta = $transferNIS["meta"];
        $content = $transferNIS["transaction"];

        // expected values
        $expectAmount  = 0;
        $expectType    = TransactionType::TRANSFER;
        $expectVersion = Transaction::VERSION_1;
        $expectRecipient = "";

        $this->assertEquals($expectAmount, $content["amount"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
        $this->assertEquals($expectRecipient, $content["recipient"]);
    }


    /**
     * Unit test for *DTO content of Transaction instances*
     * 
     * @dataProvider dtoContentVectorsProvider
     * @return void
     */
    public function testDTOContentVectors(
        $ts, $amt, $recv, $type, $version, $message, $signer, $signature,
        $expectAmt, $expectRecv, $expectType, $expectVer, $expectMsgHex
    )
    {
        $txData = [
            "timeStamp" => $ts,
            "amount" => $amt,
            "recipient" => $recv,
            "type"      => $type,
            "version"   => $version,
            "message"   => $message,
        ];

        if ($signer !== null)
            $txData["signer"] = $signer;

        if ($signature !== null)
            $txData["signature"] = $signature;

        $transaction = Transaction::create($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertEquals($expectAmt, $content["amount"]);
        $this->assertEquals($expectRecv, $content["recipient"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVer, $content["version"]);

        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("payload", $content["message"]);
        $this->assertEquals($expectMsgHex, $content["message"]["payload"]);
    }

    /**
     * Data provider for the testDTOContentVectors unit test.
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        //XXX change Transaction test to allow non-amount+recipient transactions
        return [
            [
                // act
                null, 10.0, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                "257", null,
                new Message(["plain" => "https://github.com/evias/nem-php"]), 
                null, null,
                // expect
                10000000, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::TRANSFER, Transaction::VERSION_1,
                bin2hex("https://github.com/evias/nem-php")
            ],
            [
                // act
                null, 10, "TDWZ55-R5VIHS-H5WWK6-CEGAIP-7D35XV-FZ3RU2-S5UQ",
                "257", 2,
                new Message(["plain" => "https://github.com/evias/pacNEM"]), 
                null, null,
                // expect
                10, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::TRANSFER, Transaction::VERSION_1,
                bin2hex("https://github.com/evias/pacNEM")
            ],
            [
                // act
                (new TimeWindow())->toNIS(), 100000.0, "TDWZ55-R5VIHS-H5WWK6-CEGAIP-7D35XV-FZ3RU2-S5UQ",
                TransactionType::TRANSFER, 1,
                new Message(["plain" => "https://github.com/evias/nem-nodejs-bot"]), 
                null, null,
                // expect
                100000000000, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::TRANSFER, Transaction::VERSION_1,
                bin2hex("https://github.com/evias/nem-nodejs-bot")
            ],
            [
                // act
                (new TimeWindow())->toNIS(), 0, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::TRANSFER, 1,
                null, null, null,
                // expect
                0, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::TRANSFER, Transaction::VERSION_1,
                ""
            ],
        ];
    }
}
