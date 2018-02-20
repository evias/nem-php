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
use NEM\Models\Fee;
use NEM\Models\Amount;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Mosaics\Dim\Coin;
use NEM\Models\TransactionType;
use NEM\Models\Transaction\MosaicTransfer;
use NEM\Models\Transaction\Transfer;

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
    }

    /**
     * Unit test for *attachMosaic function should attach a mosaic
     * to the attachments collection*.
     * 
     * @depends testDTOStructure
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

        $attachmentsNIS = $transaction->mosaics()->toDTO();

        $this->assertCount(1, $attachmentsNIS);
        $this->assertArrayHasKey("mosaicId", $attachmentsNIS[0]);
        $this->assertArrayHasKey("quantity", $attachmentsNIS[0]);

        $this->assertEquals(100, $attachmentsNIS[0]["quantity"]);

        $mosaic = $attachmentsNIS[0]["mosaicId"];
        $this->assertArrayHasKey("namespaceId", $mosaic);
        $this->assertArrayHasKey("name", $mosaic);

        $this->assertEquals("dim", $mosaic["namespaceId"]);
        $this->assertEquals("coin", $mosaic["name"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @return void
     */
    public function testMosaicTransferFees()
    {
        $normalTransfer = new Transfer();
        $mosaicTransfer = new MosaicTransfer();

        $normalNIS = $normalTransfer->toDTO();
        $mosaicNIS = $mosaicTransfer->toDTO();

        // if no data is set, the fee will be the same
        $this->assertEquals($normalNIS["transaction"]["fee"], $mosaicNIS["transaction"]["fee"]);

        // add attachments
        $mosaicTransfer->attachMosaic("dim:coin", 29);
        $mosaicTransfer->attachMosaic("nem:xem", 8);

        // now should contain mosaics
        $mosaicNIS = $mosaicTransfer->toDTO();
        $metaTx    = $mosaicNIS["meta"];
        $contentTx = $mosaicNIS["transaction"];

        // NIS fee is `count_attachments * fee_factor`
        $expectFee = 2 * Fee::FEE_FACTOR * Amount::XEM; // 0.10 XEM

        $this->assertArrayHasKey("mosaics", $contentTx);
        $this->assertNotEmpty($contentTx["mosaics"]);

        $this->assertNotEquals($normalNIS["transaction"]["fee"], $contentTx["fee"]);
        $this->assertEquals($expectFee, $contentTx["fee"]);

        // one more mosaic to be sure nothing else triggers the fee change
        $mosaicTransfer_2 = new MosaicTransfer();
        $mosaicTransfer_2->attachMosaic("dim:coin", 29);
        $mosaicTransfer_2->attachMosaic("nem:xem", 8);
        $mosaicTransfer_2->attachMosaic("dim:token", 10);

        // NIS fee is `count_attachments * fee_factor`
        $expectFee_2 = 3 * Fee::FEE_FACTOR * Amount::XEM;

        // get content transcribed
        $mosaicNIS_2 = $mosaicTransfer_2->toDTO();
        $metaTx_2    = $mosaicNIS_2["meta"];
        $contentTx_2 = $mosaicNIS_2["transaction"];

        $this->assertArrayHasKey("mosaics", $contentTx_2);
        $this->assertNotEmpty($contentTx_2["mosaics"]);

        $this->assertNotEquals($contentTx["fee"], $contentTx_2["fee"]);
        $this->assertEquals($expectFee_2, $contentTx_2["fee"]);
    }
}
