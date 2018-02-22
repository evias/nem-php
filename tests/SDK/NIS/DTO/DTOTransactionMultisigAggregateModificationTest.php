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
use NEM\Models\MultisigModification;
use NEM\Models\MultisigModifications;
use NEM\Models\Transaction\MultisigAggregateModification;

class DTOTransactionMultisigAggregateModificationTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty MultisigAggregateModification*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new MultisigAggregateModification();
        $transactionNIS = $transaction->toDTO();
        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("modifications", $content);
        $this->assertArrayHasKey("minCosignatories", $content);
        $this->assertInternalType("array", $content["modifications"]);
        $this->assertInternalType("array", $content["minCosignatories"]);
        $this->assertArrayHasKey("relativeChange", $content["minCosignatories"]);

        // mode has a default value
        $expectType = TransactionType::MULTISIG_MODIFICATION;

        $this->assertEquals($expectType, $content["type"]);
    }

    /**
     * Unit test for *DTO structure of MultisigAggregateModification instances*
     * 
     * @depends testDTODefaultValues
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $txData = [
            "modifications" => (new MultisigModifications([
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_ADD,
                    "cosignatoryAccount" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04"
                ])
            ]))->toDTO(),
            "minCosignatories" => 1,
        ];

        $transaction = new MultisigAggregateModification($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("modifications", $content);
        $this->assertArrayHasKey("minCosignatories", $content);
        $this->assertInternalType("array", $content["modifications"]);
        $this->assertInternalType("array", $content["minCosignatories"]);
        $this->assertArrayHasKey("relativeChange", $content["minCosignatories"]);
        $this->assertCount(1, $content["modifications"]);

        $modification  = $content["modifications"][0];
        $cosigChange   = $content["minCosignatories"];
        $expectAccount = "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04";
        $expectType    = MultisigModification::TYPE_ADD;
        $expectRelative = 1;

        $this->assertEquals($expectRelative, $cosigChange["relativeChange"]);
        $this->assertEquals($expectAccount, $modification["cosignatoryAccount"]);
        $this->assertEquals($expectType, $modification["modificationType"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testMultisigAggregateModificationFees()
    {
        $aggregateTx = new MultisigAggregateModification();
        $aggregateNIS = $aggregateTx->toDTO();
        $contentTx = $aggregateNIS["transaction"];

        // if no data is set, the fee will be the same
        $this->assertEquals(Fee::MULTISIG_AGGREGATE_MODIFICATION, $contentTx["fee"]);
    }
}
