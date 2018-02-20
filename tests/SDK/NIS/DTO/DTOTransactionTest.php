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
use NEM\Models\TimeWindow;
use NEM\Models\Amount;
use NEM\Models\Message;

use DateTime;

class DTOTransactionTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for Transaction class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $transaction = new Transaction([
            "timeStamp" => (new TimeWindow(["utc" => 1516196048585]))->toDTO(),
            "amount" => (new Amount(["amount" => 15]))->toDTO(),
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
            "type"      => TransactionType::TRANSFER,
            "message"   => (new Message(["plain" => "Hello, Greg!"]))->toDTO(),
        ]);
        $transactionNIS = $transaction->toDTO();

        $this->assertArrayHasKey("transaction", $transactionNIS);
        $this->assertArrayHasKey("meta", $transactionNIS);

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("timeStamp", $content);
        $this->assertArrayHasKey("amount", $content);
        $this->assertArrayHasKey("recipient", $content);
        $this->assertArrayHasKey("type", $content);
        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("version", $content);
    }

    /**
     * Unit test for *invalid data DTO creation*.
     * 
     * @dataProvider dtoContentInvalidDataVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentInvalidDataVectors(
        $ts, $amt, $recv, $type, $version,
        $expectAmt, $expectRecv, $expectType, $expectVersion
    )
    {
        $transaction = new Transaction([
            "timeStamp" => $ts,
            "amount" => $amt,
            "recipient" => $recv,
            "type"      => $type,
            "version"   => $version,
        ]);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertEquals($expectAmt, $content["amount"]);
        $this->assertEquals($expectRecv, $content["recipient"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
    }

    /**
     * Data provider for the testDTOContentInvalidDataVectors()
     * unit test.
     * 
     * @return array
     */
    public function dtoContentInvalidDataVectorsProvider()
    {
        return [
            [
                // act
                null, -1, null, null, -1,
                // expect 
                0,
                "", 
                TransactionType::TRANSFER, 
                Transaction::VERSION_1
            ],
        ];
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

        $transaction = new Transaction($txData);
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
        return [
            [
                // act
                null, 10.0, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                "4100", null,
                new Message(["plain" => "https://github.com/evias/nem-php"]), 
                null, null,
                // expect
                10000000, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                TransactionType::MULTISIG, Transaction::VERSION_1,
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
