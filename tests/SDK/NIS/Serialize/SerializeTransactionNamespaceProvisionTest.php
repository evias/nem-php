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

// This unit test will test all serializer process Specializations
use NEM\Models\Transaction\NamespaceProvision;

class SerializeTransactionNamespaceProvisionTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_NamespaceProvision()
    {
        $transaction = new NamespaceProvision([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91298537]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91302137]))->toDTO(),
            "rentalFeeSink" => "TAMESPACEWH4MKFMBCVFERDPOOP4FK7MTDJEYP35",
            "rentalFee" => 10000000,
            "version"   => -1744830463,
            "fee"       => 150000,
            "parent"    => "evias",
            "newPart"   => "sdk",
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "3356f5543474d0b0bd10c74092a34ddd67e106c696361bb49f937321720e7a7c"
                          ."46099ea9eaf73022b17aaf41715c53f114b14aac918ef609b2b8ebfe2057c401",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();

        // expected results
        $expectUInt8 = [
            1,
           32,   0,   0,   1,
            0,   0, 152, 233,
           26, 113,   5,  32,
            0,   0,   0, 217,
           12,   8, 207, 187,
          249,  24, 217,  48,
           77, 221,  69, 246,
           67,  37, 100, 195,
          144, 165, 250, 207,
          243, 223,  23, 237,
           92,   9, 108,  76,
          207,  13,   4, 240,
           73,   2,   0,   0,
            0,   0,   0, 249,
           40, 113,   5,  40,
            0,   0,   0,  84,
           65,  77,  69,  83,
           80,  65,  67,  69,
           87,  72,  52,  77,
           75,  70,  77,  66,
           67,  86,  70,  69,
           82,  68,  80,  79,
           79,  80,  52,  70,
           75,  55,  77,  84,
           68,  74,  69,  89,
           80,  51,  53, 128,
          150, 152,   0,   0,
            0,   0,   0,   3,
            0,   0,   0, 115,
          100, 107,   5,   0,
            0,   0, 101, 118,
          105,  97, 115
        ];
        $expectSize = count($expectUInt8);

        $this->assertNotEmpty($serialized);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
