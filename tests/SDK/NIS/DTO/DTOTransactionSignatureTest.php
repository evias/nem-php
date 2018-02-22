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
use NEM\Models\Transaction\Signature;

class DTOTransactionSignatureTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *default values in case of empty ImportanceTransfer*.
     * 
     * @return void
     */
    public function testDTODefaultValues()
    {
        $transaction = new Signature();
        $transactionNIS = $transaction->toDTO();

        // Signature transaction directly contain data, not under `meta``
        // and `transaction`.
        $this->assertArrayHasKey("otherHash", $transactionNIS);
        $this->assertArrayHasKey("otherAccount", $transactionNIS);
        $this->assertArrayHasKey("timeStamp", $transactionNIS);
        $this->assertArrayHasKey("fee", $transactionNIS);
        $this->assertArrayHasKey("version", $transactionNIS);
        $this->assertArrayHasKey("type", $transactionNIS);

        // mode has a default value
        $expectType = TransactionType::MULTISIG_SIGNATURE;

        $this->assertEquals($expectType, $transactionNIS["type"]);
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
            "otherAccount" => "TBLCGKI5X6V34WF5PEZSIRX3FMVGPVTTMIOYG5BA",
            "otherHash"   => "6da09aa968a9604384d8ce979a88ba76cb57166bc6a0a76b89fbbb08cc1c851c",
            "signer" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176",
            "signature" => "b76e9de0155b0d242393c8236d3ba5af752fddc470a6e43a6ebe7659aad1c"
                          ."146c61f747da7fa205603ae29d1e03bfbf6262bd93c50d4ecab191e22976983e70c",
        ];

        $transaction = new Signature($txData);
        $transactionNIS = $transaction->toDTO();

        $this->assertArrayHasKey("otherHash", $transactionNIS);
        $this->assertArrayHasKey("otherAccount", $transactionNIS);
        $this->assertArrayHasKey("timeStamp", $transactionNIS);
        $this->assertArrayHasKey("fee", $transactionNIS);
        $this->assertArrayHasKey("version", $transactionNIS);
        $this->assertArrayHasKey("type", $transactionNIS);

        $expectHash = "6da09aa968a9604384d8ce979a88ba76cb57166bc6a0a76b89fbbb08cc1c851c";
        $expectAcct = "TBLCGKI5X6V34WF5PEZSIRX3FMVGPVTTMIOYG5BA";
        $expectSigner = "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176";
        $expectSignature = "b76e9de0155b0d242393c8236d3ba5af752fddc470a6e43a6ebe7659aad1c146"
                          ."c61f747da7fa205603ae29d1e03bfbf6262bd93c50d4ecab191e22976983e70c";

        $this->assertInternalType("array", $transactionNIS["otherHash"]);
        $this->assertArrayHasKey("data", $transactionNIS["otherHash"]);
        $this->assertEquals($expectHash, $transactionNIS["otherHash"]["data"]);
        $this->assertEquals($expectAcct, $transactionNIS["otherAccount"]);
        $this->assertEquals($expectSigner, $transactionNIS["signer"]);
        $this->assertEquals($expectSignature, $transactionNIS["signature"]);
    }

    /**
     * Unit test for *fee change corresponding to the Transaction
     * specialization*, here MosaicTransfer.
     * 
     * @depends testDTOStructure
     * @return void
     */
    public function testSignatureFees()
    {
        $signature = new Signature();
        $signatureNIS = $signature->toDTO();

        // if no data is set, the fee will be the same
        $this->assertEquals(Fee::SIGNATURE, $signatureNIS["fee"]);
    }
}
