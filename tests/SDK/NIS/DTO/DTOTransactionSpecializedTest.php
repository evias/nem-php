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
use NEM\Models\Transaction\Transfer;

class DTOTransactionSpecializedTest
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
    public function testDTOContent_Base()
    {
        // test obligatory fields
        $txData = [
            "amount"    => 290888,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
        ];

        $transaction = new Transaction($txData);
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
        $transaction = new Transaction($txData);
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
}
