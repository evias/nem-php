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
use NEM\Models\Transaction\NamespaceProvision;

class DTOTransactionNamespaceProvisionTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty NamespaceProvision*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new NamespaceProvision();
        $transactionNIS = $transaction->toDTO();
        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("rentalFeeSink", $content);
        $this->assertArrayHasKey("rentalFee", $content);
        $this->assertArrayHasKey("parent", $content);
        $this->assertArrayHasKey("newPart", $content);

        // mode has a default value
        $expectType = TransactionType::PROVISION_NAMESPACE;

        $this->assertEquals($expectType, $content["type"]);
    }

    /**
     * Unit test for *DTO structure of NamespaceProvision instances*
     * 
     * @depends testDTODefaultValues
     * @return void
     */
    public function testDTOStructure()
    {
        // test obligatory fields
        $txData = [
            "rentalFeeSink" => "TAMESPACEWH4MKFMBCVFERDPOOP4FK7MTDJEYP35",
            "rentalFee" => 10000000,
            "parent"    => "evias",
            "newPart"   => "sdk",
        ];

        $transaction = new NamespaceProvision($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("rentalFeeSink", $content);
        $this->assertArrayHasKey("rentalFee", $content);
        $this->assertArrayHasKey("parent", $content);
        $this->assertArrayHasKey("newPart", $content);

        $expectAccount = "TAMESPACEWH4MKFMBCVFERDPOOP4FK7MTDJEYP35";
        $expectParent  = "evias";
        $expectNewPart = "sdk";
        $expectFee     = 10000000; // 10 XEM

        $this->assertEquals($expectAccount, $content["rentalFeeSink"]);
        $this->assertEquals($expectParent, $content["parent"]);
        $this->assertEquals($expectNewPart, $content["newPart"]);
        $this->assertEquals($expectFee, $content["rentalFee"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testNamespaceProvisionFees()
    {
        $aggregateTx = new NamespaceProvision();
        $aggregateNIS = $aggregateTx->toDTO();
        $contentTx = $aggregateNIS["transaction"];

        // if no data is set, the fee will be for root namespace
        $this->assertEquals(Fee::ROOT_PROVISION_NAMESPACE, $contentTx["fee"]);

        $aggregateTx = new NamespaceProvision(["parent" => "test"]);
        $aggregateNIS = $aggregateTx->toDTO();
        $contentTx = $aggregateNIS["transaction"];

        // if parent namespace is set, the fee will be for sub namespace
        $this->assertEquals(Fee::SUB_PROVISION_NAMESPACE, $contentTx["fee"]);
    }
}
