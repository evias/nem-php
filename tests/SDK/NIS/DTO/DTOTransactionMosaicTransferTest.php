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
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Mosaics\Dim\Coin;
use NEM\Models\TransactionType;
use NEM\Models\Transaction\MosaicTransfer;

class DTOTransactionMosaicTransferTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *empty mosaics field in MosaicTransfer object*.
     * 
     * @return void
     */
    public function testDefaultValuesForMosaicTransfer()
    {
        $txData = [
            "amount"    => 0,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
        ];

        $transaction = new MosaicTransfer($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        // expected *default* values
        $expectAmount  = 0;
        $expectType    = TransactionType::TRANSFER;
        $expectVersion = Transaction::VERSION_2; // MOSAIC TRANSFER
        $expectRecipient = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";

        // base transaction details test
        $this->assertEquals($expectAmount, $content["amount"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVersion, $content["version"]);
        $this->assertEquals($expectRecipient, $content["recipient"]);

        // message field empty but present
        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("payload", $content["message"]);
        $this->assertEmpty($content["message"]["payload"]);

        // mosaics field should always be returned
        $this->assertArrayHasKey("mosaics", $content);
        $this->assertEmpty($content["mosaics"]);
    }

    /**
     * Unit test for *DTO structure of MosaicTransfer instances*
     * 
     * @depends testDefaultValuesForMosaicTransfer
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $attachDim = new MosaicAttachment([
            "mosaicId" => (new Coin())->id()->toDTO(),
            "quantity" => 100,
        ]);

        $txData = [
            "amount"    => 0,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
            "mosaics"   => (new MosaicAttachments([$attachDim]))->toDTO(),
        ];

        $transaction = new MosaicTransfer($txData);
        $transactionNIS = $transaction->toDTO(true);

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        // test attachment structure
        $attachmentNIS = array_shift($content["mosaics"]);

        $this->assertArrayHasKey("mosaicId", $attachmentNIS);
        $this->assertArrayHasKey("quantity", $attachmentNIS);
        $this->assertArrayHasKey("namespaceId", $attachmentNIS["mosaicId"]);
        $this->assertArrayHasKey("name", $attachmentNIS["mosaicId"]);

        $mosaicNIS = $attachmentNIS["mosaicId"];

        // expected *specialized* values (attachment content)
        $expectNamespace = "dim";
        $expectMosaic    = "coin";
        $expectQuantity  = 100;

        $this->assertEquals($expectNamespace, $mosaicNIS["namespaceId"]);
        $this->assertEquals($expectMosaic, $mosaicNIS["name"]);
        $this->assertEquals($expectQuantity, $attachmentNIS["quantity"]);

        // optional fields were not set
        $this->assertEmpty($content["signer"]);
        $this->assertEmpty($content["signature"]);
    }

    /**
     * Unit test for *attachMosaic function should attach a mosaic
     * to the attachments collection*.
     * 
     * @return void
     */
    public function testAttachMosaicPushesToAttachments()
    {
        // test obligatory fields
        $attachDim = new MosaicAttachment([
            "mosaicId" => (new Coin())->id()->toDTO(),
            "quantity" => 100,
        ]);

        // intentionally exclude "mosaics" field data.
        $txData = [
            "amount"    => 0,
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
        ];

        // use attachMosaic to test behaviour
        $transaction = new MosaicTransfer($txData);
        $transaction->attachMosaic($attachDim);

        $this->assertEquals(1, $transaction->mosaics()->count());
    }
}
