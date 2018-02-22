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
use NEM\Models\MosaicProperties;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicLevy;
use NEM\Models\MosaicDefinition as DefinitionModel;
use NEM\Models\Transaction\MosaicDefinition as DefinitionTx;

class DTOTransactionMosaicDefinitionTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty MosaicDefinition*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new DefinitionTx();
        $transactionNIS = $transaction->toDTO();

        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("creationFeeSink", $content);
        $this->assertArrayHasKey("creationFee", $content);
        $this->assertArrayHasKey("mosaicDefinition", $content);
        $this->assertInternalType("string", $content["creationFeeSink"]);
        $this->assertInternalType("int", $content["creationFee"]);
        $this->assertInternalType("array", $content["mosaicDefinition"]);
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
        $mosaic = new Mosaic([
            "namespaceId" => "evias",
            "name" => "test-levy",
        ]);
        $xem = new Mosaic([
            "namespaceId" => "nem",
            "name" => "xem",
        ]);
        $txData = [
            "creationFeeSink" => "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC",
            "creationFee" => 10000000,
            "mosaicDefinition" => (new DefinitionModel([
                "id" => $mosaic->toDTO(),
                "creator" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
                "description" => "levy should be absolute 10000 XEM",
                "properties" => (new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 0]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => true]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]))->toDTO(),
                "levy" => (new MosaicLevy([
                    "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                    "mosaicId" => $xem->toDTO(),
                    "type" => MosaicLevy::TYPE_ABSOLUTE,
                    "fee" => 10000
                ]))->toDTO()
            ]))->toDTO(),
        ];

        $transaction = new DefinitionTx($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("creationFeeSink", $content);
        $this->assertArrayHasKey("creationFee", $content);
        $this->assertArrayHasKey("mosaicDefinition", $content);

        $expectType = TransactionType::MOSAIC_DEFINITION;
        $expectSink = "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC";
        $expectCreationFee = Fee::MOSAIC_DEFINITION;

        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectSink, $content["creationFeeSink"]);
        $this->assertEquals($expectCreationFee, $content["creationFee"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testMosaicDefinitionFees()
    {
        $mosaicDefinition = new DefinitionTx();

        $definitionNIS = $mosaicDefinition->toDTO();
        $content = $definitionNIS["transaction"];

        $this->assertEquals(Fee::MOSAIC_DEFINITION, $content["creationFee"]);
        $this->assertEquals(Fee::NAMESPACE_AND_MOSAIC, $content["fee"]);
    }
}
