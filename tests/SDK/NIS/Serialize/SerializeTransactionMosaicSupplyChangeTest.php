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
use NEM\Models\Fee;
use NEM\Models\TimeWindow;
use NEM\Models\Message;

// This unit test will test all serializer process Specializations
use NEM\Models\Transaction\MosaicSupplyChange;
use NEM\Models\Mosaic;

class SerializeTransactionMosaicSupplyChangeTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicSupplyChange()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias",
            "name" => "test-mosaicdef"
        ]);

        $transaction = new MosaicSupplyChange([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91820520]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91824120]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::NAMESPACE_AND_MOSAIC,
            "mosaicId"      => $mosaic->toDTO(),
            "supplyType"    => MosaicSupplyChange::TYPE_INCREASE,
            "delta"         => 1000000,
            "signer"        => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature"     => "f0c1ef315f24b40d88f9655f7f859fe4384ecd9ecd907da6967158706862b590"
                              ."c2dce55a973ee3ad6a56ba465b583bc257900bcde65e7a7575996e624f46030c",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();

        // expected results
        $expectUInt8 = [
            2,64,0,0,1,0,0,152,232,17,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,240,73,2,0,0,0,0,0,248,31,121,5,27,0,0,0,5,0,0,0,101,118,105,97,115,14,0,0,0,116,101,115,116,45,109,111,115,97,105,99,100,101,102,1,0,0,0,64,66,15,0,0,0,0,0
        ];
        $expectSize = count($expectUInt8);

        $this->assertNotEmpty($serialized);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
