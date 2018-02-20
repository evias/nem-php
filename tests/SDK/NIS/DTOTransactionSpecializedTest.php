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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Transaction;
use NEM\Models\Transaction\Transfer;
use NEM\Models\Transaction\Multisig;
use NEM\Models\Transaction\Signature;
use NEM\Models\Transaction\MosaicTransfer;
use NEM\Models\Transaction\ImportanceTransfer;
use NEM\Models\Transaction\MosaicDefinition;
use NEM\Models\Transaction\MosaicSupplyChange;
use NEM\Models\Transaction\MultisigAggregateModification;
use NEM\Models\Transaction\NamespaceProvision;

class DTOTransactionSpecializedTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *DTO content of Transaction instances*
     * 
     * @depends testDTOStructure_Base
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

        $this->assertEquals($expectAmt, $content["amount"]);
        $this->assertEquals($expectRecv, $content["recipient"]);
        $this->assertEquals($expectType, $content["type"]);
        $this->assertEquals($expectVer, $content["version"]);

        $this->assertArrayHasKey("message", $content);
        $this->assertArrayHasKey("payload", $content["message"]);
        $this->assertEmpty($content["message"]["payload"]);
    }
}
