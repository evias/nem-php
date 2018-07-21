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

        $transaction = new MultisigAggregateModification([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847140]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => Fee::MULTISIG_AGGREGATE_MODIFICATION,
            "minCosignatories" => ["relativeChange" => $relativeChange_2],
            "modifications" => (new MultisigModifications([
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_REMOVE,
                    "cosignatoryAccount" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"
                ])
            ]))->toDTO(),
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

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
    /**
     * Unit test for *serialize process Specialization: Transaction\MultisigAggregateModification*.
     * 
     * This processes a multisig account modification.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MultisigAggregateModification_PositiveRelativeChange()
    {
        $relativeChange_1 = 3;

        // This will test sorting modifications under the hood. 
        // When the transaction is serialized, the multisig modifications
        // are sorted by type and lexicographically by address.
        // Following is the resulting sort order for the below defined
        // transaction:

        // A = bc5761bb3a903d136910fca661c6b1af4d819df4c270f5241143408384322c58
        // B = 7cbc80a218acba575305e7ff951a336ec66bd122519b12dc26eace26a1354962
        // C = d4301b99c4a79ef071f9a161d65cd95cba0ca3003cb0138d8b62ff770487a8c4

        // A = bc57.. = TD5MITTMM2XDQVJSHEKSPJTCGLFAYFGYDFHPGBEC
        // B = 7cbc.. = TBYCP5ZYZ4BLCD2TOHXXM6I6ZFK2JQF57SE5QVTK
        // C = d430.. = TBJ3RBTPA5LSPBPINJY622WTENAF7KGZI53D6DGO

        // Resulting Order: C, B, A

        $transaction = new MultisigAggregateModification([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847140]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => Fee::MULTISIG_AGGREGATE_MODIFICATION,
            "minCosignatories" => ["relativeChange" => $relativeChange_1],
            "modifications" => (new MultisigModifications([
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_ADD,
                    "cosignatoryAccount" => "bc5761bb3a903d136910fca661c6b1af4d819df4c270f5241143408384322c58",
                ]),
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_ADD,
                    "cosignatoryAccount" => "7cbc80a218acba575305e7ff951a336ec66bd122519b12dc26eace26a1354962",
                ]),
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_ADD,
                    "cosignatoryAccount" => "d4301b99c4a79ef071f9a161d65cd95cba0ca3003cb0138d8b62ff770487a8c4",
                ]),
            ]))->toDTO(),
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

        // test specialized MultisigAggregateModification::serialize() serialization process
        $serialized = $transaction->serialize(-104);
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,16,0,0,2,0,0,152,212,107,121,5,32,0,0,0,72,14,84,195,143,237,208,242,191,45,83,19,43,129,156,53,186,66,112,169,20,74,245,90,115,243,37,104,106,169,60,150,32,161,7,0,0,0,0,0,228,121,121,5,3,0,0,0,40,0,0,0,1,0,0,0,32,0,0,0,212,48,27,153,196,167,158,240,113,249,161,97,214,92,217,92,186,12,163,0,60,176,19,141,139,98,255,119,4,135,168,196,40,0,0,0,1,0,0,0,32,0,0,0,124,188,128,162,24,172,186,87,83,5,231,255,149,26,51,110,198,107,209,34,81,155,18,220,38,234,206,38,161,53,73,98,40,0,0,0,1,0,0,0,32,0,0,0,188,87,97,187,58,144,61,19,105,16,252,166,97,198,177,175,77,129,157,244,194,112,245,36,17,67,64,131,132,50,44,88,4,0,0,0,3,0,0,0
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0110000002000098d46b790520000000480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c9620a1070000000000e479790503000000280000000100000020000000d4301b99c4a79ef071f9a161d65cd95cba0ca3003cb0138d8b62ff770487a8c42800000001000000200000007cbc80a218acba575305e7ff951a336ec66bd122519b12dc26eace26a1354962280000000100000020000000bc5761bb3a903d136910fca661c6b1af4d819df4c270f5241143408384322c580400000003000000";

        $this->assertNotEmpty($serialized);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals($expectSize, count($serialized));
    }
}
