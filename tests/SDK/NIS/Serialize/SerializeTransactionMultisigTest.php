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
use NEM\Models\Transaction\Multisig;
use NEM\Models\Transaction\Transfer;
use NEM\Models\Transaction\MultisigAggregateModification;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Models\MultisigModifications;
use NEM\Models\MultisigModification;
use NEM\Models\Mosaic;

class SerializeTransactionMultisigTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: Transaction\Multisig*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_Multisig_Transfer()
    {
        $otherTrans = new Transfer([
            "recipient" => "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
            "amount"    => 10000000,
            "message"   => (new Message(["plain" => "Hello, Multi-Greg!"]))->toDTO(),
            "timeStamp" => (new TimeWindow(["timeStamp" => 91828160]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91831760]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => 2 * Fee::FEE_FACTOR, // transfer + message < 31 chars
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

        $transaction = new Multisig([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91828160]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91831760]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::MULTISIG,
            "otherTrans" => $otherTrans->toDTO("transaction"),
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "dd49a424b6a8321491ca4ae622799177d3d21c75ca521f78bb85c8c36c224ccd"
                          ."16a6aa4b30eaa4132b8d838d7733815aaf1d327feaa32c9011bc4f736980af09",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            4,16,0,0,1,0,0,152,192,47,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,240,73,2,0,0,0,0,0,208,61,121,5,142,0,0,0,1,1,0,0,1,0,0,152,192,47,121,5,32,0,0,0,72,14,84,195,143,237,208,242,191,45,83,19,43,129,156,53,186,66,112,169,20,74,245,90,115,243,37,104,106,169,60,150,160,134,1,0,0,0,0,0,208,61,121,5,40,0,0,0,84,68,50,80,69,89,50,51,89,54,79,51,76,78,71,65,79,52,89,74,89,78,68,82,81,83,51,73,82,84,69,67,55,80,90,85,73,87,76,84,128,150,152,0,0,0,0,0,26,0,0,0,1,0,0,0,18,0,0,0,72,101,108,108,111,44,32,77,117,108,116,105,45,71,114,101,103,33
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0410000001000098c02f790520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f049020000000000d03d79058e0000000101000001000098c02f790520000000480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96a086010000000000d03d790528000000544432504559323359364f334c4e47414f34594a594e4452515333495254454337505a5549574c5480969800000000001a000000010000001200000048656c6c6f2c204d756c74692d4772656721";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: Transaction\Multisig*.
     * 
     * This processes a multisig account modification.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_Multisig_AggregateModification()
    {
        $otherTrans = new MultisigAggregateModification([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847140]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => Fee::MULTISIG_AGGREGATE_MODIFICATION,
            "minCosignatories" => ["relativeChange" => -1],
            "modifications" => (new MultisigModifications([
                new MultisigModification([
                    "modificationType" => MultisigModification::TYPE_REMOVE,
                    "cosignatoryAccount" => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176"
                ])
            ]))->toDTO(),
            "signer"    => "480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c96",
        ]);

        $transaction = new Multisig([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847140]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::MULTISIG,
            "otherTrans" => $otherTrans->toDTO("transaction"),
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
        ]);

        //XXX test that innerHash is the same as signature.otherHash.data

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            4,16,0,0,1,0,0,152,212,107,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,240,73,2,0,0,0,0,0,228,121,121,5,116,0,0,0,1,16,0,0,2,0,0,152,212,107,121,5,32,0,0,0,72,14,84,195,143,237,208,242,191,45,83,19,43,129,156,53,186,66,112,169,20,74,245,90,115,243,37,104,106,169,60,150,32,161,7,0,0,0,0,0,228,121,121,5,1,0,0,0,40,0,0,0,2,0,0,0,32,0,0,0,114,17,123,66,84,185,228,156,223,186,166,183,193,130,95,0,44,221,85,200,56,202,120,72,82,145,220,169,131,78,193,118,4,0,0,0,255,255,255,255
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0410000001000098d46b790520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f049020000000000e4797905740000000110000002000098d46b790520000000480e54c38fedd0f2bf2d53132b819c35ba4270a9144af55a73f325686aa93c9620a1070000000000e47979050100000028000000020000002000000072117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec17604000000ffffffff";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
