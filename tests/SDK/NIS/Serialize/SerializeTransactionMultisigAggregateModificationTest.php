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
namespace NEM\Tests\SDK\NIS\Serialize;

use NEM\Tests\TestCase;
use NEM\Core\Serializer;
use NEM\Core\Buffer;
use NEM\Models\Model;
use NEM\Models\TimeWindow;
use NEM\Models\Message;
use NEM\Models\Fee;

// This unit test will test all serializer process Specializations
use NEM\Models\Transaction\MultisigAggregateModification;
use NEM\Models\MultisigModifications;
use NEM\Models\MultisigModification;

class SerializeTransactionMultisigAggregateModificationTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: Transaction\MultisigAggregateModification*.
     * 
     * This processes a multisig account modification.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MultisigAggregateModification_NegativeRelativeChange()
    {
        $relativeChange_1 = -1;

        $transaction = new MultisigAggregateModification([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847140]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => Fee::MULTISIG_AGGREGATE_MODIFICATION,
            "minCosignatories" => ["relativeChange" => $relativeChange_1],
            "modifications" => (new MultisigModifications([
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_REMOVE,
                    "cosignatoryAccount" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"
                ])
            ]))->toDTO(),
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

        // test specialized MultisigAggregateModification::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,16,0,0,2,0,0,152,212,107,121,5,32,0,0,0,72,14,84,195,143,237,208,242,191,45,83,19,43,129,156,53,186,66,112,169,20,74,245,90,115,243,37,104,106,169,60,150,32,161,7,0,0,0,0,0,228,121,121,5,1,0,0,0,40,0,0,0,2,0,0,0,32,0,0,0,114,17,123,66,84,185,228,156,223,186,166,183,193,130,95,0,44,221,85,200,56,202,120,72,82,145,220,169,131,78,193,118,4,0,0,0,255,255,255,255
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0110000002000098d46b790520000000480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c9620a1070000000000e47979050100000028000000020000002000000072117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec17604000000ffffffff";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));

        // test second value
        $relativeChange_2 = -2;
        $transaction->setAttribute("minCosignatories", ["relativeChange" => $relativeChange_2]);
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,16,0,0,2,0,0,152,212,107,121,5,32,0,0,0,72,14,84,195,143,237,208,242,191,45,83,19,43,129,156,53,186,66,112,169,20,74,245,90,115,243,37,104,106,169,60,150,32,161,7,0,0,0,0,0,228,121,121,5,1,0,0,0,40,0,0,0,2,0,0,0,32,0,0,0,114,17,123,66,84,185,228,156,223,186,166,183,193,130,95,0,44,221,85,200,56,202,120,72,82,145,220,169,131,78,193,118,4,0,0,0,254,255,255,255
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0110000002000098d46b790520000000480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c9620a1070000000000e47979050100000028000000020000002000000072117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec17604000000feffffff";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}