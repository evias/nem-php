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
use NEM\Models\Transaction\Signature;

class SerializeTransactionSignatureTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: Transaction\Signature*.
     * 
     * This processes a multisig account modification.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_Signature()
    {
        $transaction = new Signature([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91843627]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91847227]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::SIGNATURE,
            "otherHash" => "a591489475af9598d3d9be744b71172362be99fa0c41802f7c7f9bee094d54ac",
            "otherAccount" => "TBLCGKI5X6V34WF5PEZSIRX3FMVGPVTTMIOYG5BA",
            "signer"    => "72117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176",
            "signature" => "12f5d56e79cc7d384de32167124684566a8f285462e2822da37ae88b81c99341"
                          ."7d110331dc0bee23e1669a61665d2d80db341871d35b5756749276de14c31000",
        ]);

        // test specialized Signature::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            2,16,0,0,1,0,0,152,43,108,121,5,32,0,0,0,114,17,123,66,84,185,228,156,223,186,166,183,193,130,95,0,44,221,85,200,56,202,120,72,82,145,220,169,131,78,193,118,240,73,2,0,0,0,0,0,59,122,121,5,36,0,0,0,32,0,0,0,165,145,72,148,117,175,149,152,211,217,190,116,75,113,23,35,98,190,153,250,12,65,128,47,124,127,155,238,9,77,84,172,40,0,0,0,84,66,76,67,71,75,73,53,88,54,86,51,52,87,70,53,80,69,90,83,73,82,88,51,70,77,86,71,80,86,84,84,77,73,79,89,71,53,66,65
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "02100000010000982b6c79052000000072117b4254b9e49cdfbaa6b7c1825f002cdd55c838ca78485291dca9834ec176f0490200000000003b7a79052400000020000000a591489475af9598d3d9be744b71172362be99fa0c41802f7c7f9bee094d54ac2800000054424c43474b4935583656333457463550455a5349525833464d5647505654544d494f5947354241";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}