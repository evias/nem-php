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
use NEM\Models\Mosaic;
use NEM\Models\Transaction\MosaicSupplyChange;

class DTOTransactionMosaicSupplyChangeTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty ImportanceTransfer*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new MosaicSupplyChange();
        $transactionNIS = $transaction->toDTO();
        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("mosaicId", $content);
        $this->assertArrayHasKey("supplyType", $content);
        $this->assertArrayHasKey("delta", $content);

        // mode has a default value
        $expectType = TransactionType::MOSAIC_SUPPLY_CHANGE;
        $expectSupplyType = MosaicSupplyChange::TYPE_INCREASE;

        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectSupplyType, $content["supplyType"]);
        $this->assertEquals(0, $content["delta"]);
    }

    /**
     * Unit test for *DTO structure of ImportanceTransfer instances*
     * 
     * @depends testDTODefaultValues
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $txData = [
            "mosaicId" => (new Mosaic([
                "namespaceId" => "evias",
                "name" => "test-levy"
            ]))->toDTO(),
            "supplyType" => MosaicSupplyChange::TYPE_INCREASE,
            "delta"     => 1000, // +1000 coins
        ];

        $transaction = new MosaicSupplyChange($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        // test specialized fields
        $this->assertArrayHasKey("mosaicId", $content);
        $this->assertArrayHasKey("supplyType", $content);
        $this->assertArrayHasKey("delta", $content);

        // test content
        $expectType = MosaicSupplyChange::TYPE_INCREASE;
        $expectDelta = 1000;
        $this->assertEquals($expectType, $content["supplyType"]);
        $this->assertEquals($expectDelta, $content["delta"]);

        // test sub-dto
        $this->assertInternalType("array", $content["mosaicId"]);
        $this->assertArrayHasKey("namespaceId", $content["mosaicId"]);
        $this->assertArrayHasKey("name", $content["mosaicId"]);

        $mosaicNIS = $content["mosaicId"];
        $expectNamespace = "evias";
        $expectMosaic    = "test-levy";

        $this->assertEquals($expectNamespace, $mosaicNIS["namespaceId"]);
        $this->assertEquals($expectMosaic, $mosaicNIS["name"]);

    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testMosaicSupplyChangeFees()
    {
        $supplyChange = new MosaicSupplyChange();
        $supplyChangeNIS = $supplyChange->toDTO();
        $contentTx = $supplyChangeNIS["transaction"];

        // if no data is set, the fee will be the same
        $this->assertEquals(Fee::NAMESPACE_AND_MOSAIC, $contentTx["fee"]);
    }
}
