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
use NEM\Models\Transaction\Transfer;
use NEM\Models\TransactionType;
use NEM\Models\TimeWindow;
use NEM\Models\Amount;
use NEM\Models\Message;

use DateTime;

class DTOTransactionBaseTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for Transaction class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $txData = [
            "timeStamp" => (new TimeWindow(["utc" => 1516196048585]))->toDTO(),
            "amount" => (new Amount(["amount" => 15]))->toDTO(),
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
            "type"      => TransactionType::TRANSFER,
            "message"   => (new Message(["plain" => "Hello, Greg!"]))->toDTO(),
        ];

        $transaction = new Transaction($txData);
        $transfer    = new Transfer($txData);
        $transactionNIS = $transaction->toDTO();
        $transferNIS    = $transfer->toDTO();

        $this->assertArrayHasKey("transaction", $transactionNIS);
        $this->assertArrayHasKey("meta", $transactionNIS);

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        // test only *mandatory* fields
        $this->assertArrayHasKey("timeStamp", $content);
        $this->assertArrayHasKey("type", $content);
        $this->assertArrayHasKey("version", $content);
        $this->assertArrayHasKey("fee", $content);

        // now test transfer fields (full basic v1 transaction)
        $transferContent = $transferNIS["transaction"];
        $this->assertArrayHasKey("recipient", $transferContent);
        $this->assertArrayHasKey("amount", $transferContent);
        $this->assertArrayHasKey("message", $transferContent);
    }

    /**
     * Unit test for *invalid data DTO creation*.
     * 
     * @dataProvider dtoContentInvalidDataVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentInvalidDataVectors(
        $ts, $type, $version,
        $expectType, $expectVersion
    )
    {
        $transaction = new Transaction([
            "timeStamp" => $ts,
            "type"      => $type,
            "version"   => $version,
        ]);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

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
                null, null, -1,
                // expect 
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
        $ts, $type, $version, $signer, $signature,
        $expectType, $expectVer
    )
    {
        $txData = [
            "timeStamp" => $ts,
            "type"      => $type,
            "version"   => $version,
        ];

        if ($signer !== null)
            $txData["signer"] = $signer;

        if ($signature !== null)
            $txData["signature"] = $signature;

        $transaction = new Transaction($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVer, $content["version"]);
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
                null, "4100", null,
                null, null,
                // expect
                TransactionType::MULTISIG, Transaction::VERSION_1,
            ],
            [
                // act
                null, "257", 2,
                null, null,
                // expect
                TransactionType::TRANSFER, Transaction::VERSION_1,
            ],
            [
                // act
                (new TimeWindow())->toNIS(),
                TransactionType::TRANSFER, 1,
                null, null,
                // expect
                TransactionType::TRANSFER, Transaction::VERSION_1,
            ],
            [
                // act
                (new TimeWindow())->toNIS(),
                TransactionType::TRANSFER, 1,
                null, null,
                // expect
                TransactionType::TRANSFER, Transaction::VERSION_1,
            ],
        ];
    }
}
