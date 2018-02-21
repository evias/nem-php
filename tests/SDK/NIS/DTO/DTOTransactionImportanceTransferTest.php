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
use NEM\Models\Transaction\Transfer;
use NEM\Models\Transaction\ImportanceTransfer;

class DTOTransactionImportanceTransferTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty ImportanceTransfer*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new ImportanceTransfer($txData);
        $transactionNIS = $transaction->toDTO();
        $meta    = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("remoteAccount", $content);
        $this->assertArrayHasKey("mode", $content);

        // mode has a default value
        $this->assertEquals(ImportanceTransfer::MODE_ACTIVATE, $content["mode"]);
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
            "remoteAccount" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "mode"   => ImportanceTransfer::MODE_ACTIVATE,
        ];

        $transaction = new ImportanceTransfer($txData);
        $transactionNIS = $transaction->toDTO();

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("remoteAccount", $content);
        $this->assertArrayHasKey("mode", $content);

        $expectPubKey = "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04";
        $expectMode   = ImportanceTransfer::MODE_ACTIVATE;
        $this->assertEquals($expectPubKey, $content["remoteAccount"]);
        $this->assertEquals($expectMode, $content["mode"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testImportanceTransferFees()
    {
        $normalTransfer = new Transfer();
        $importanceTransfer = new ImportanceTransfer();

        $normalNIS = $normalTransfer->toDTO();
        $importanceNIS = $importanceTransfer->toDTO();

        // if no data is set, the fee will be the same
        $this->assertEquals(Fee::IMPORTANCE_TRANSFER, $importanceNIS["transaction"]["fee"]);
    }
}
