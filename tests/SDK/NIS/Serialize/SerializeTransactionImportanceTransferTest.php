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
use NEM\Models\Transaction\ImportanceTransfer;

class SerializeTransactionImportanceTransferTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_ImportanceTransfer()
    {
        $transaction = new ImportanceTransfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91797993]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91801593]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => Fee::IMPORTANCE_TRANSFER,
            "mode"      => ImportanceTransfer::MODE_ACTIVATE,
            "remoteAccount" => "9dc1d7549c714775c4accfd62e0cf750ee370ed47e158d2bebff46f3c5631804",
            "signer"        => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature"     => "5dca9024ba82ecd6ddfe53b6b8731235dab13a0702be2cb62ce024421cc4c965"
                              ."d8c84b2f7ad9ed7b7dc7736c4624ae4dd10d0fe3ecb87ff46c3f2ea3c76a2604",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,   8,   0,   0,
            1,   0,   0, 152,
          233, 185, 120,   5,
           32,   0,   0,   0,
          217,  12,   8, 207,
          187, 249,  24, 217,
           48,  77, 221,  69,
          246,  67,  37, 100,
          195, 144, 165, 250,
          207, 243, 223,  23,
          237,  92,   9, 108,
           76, 207,  13,   4,
          240,  73,   2,   0,
            0,   0,   0,   0,
          249, 199, 120,   5,
            1,   0,   0,   0,
           32,   0,   0,   0,
          157, 193, 215,  84,
          156, 113,  71, 117,
          196, 172, 207, 214,
           46,  12, 247,  80,
          238,  55,  14, 212,
          126,  21, 141,  43,
          235, 255,  70, 243,
          197,  99,  24,   4
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0108000001000098e9b9780520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f049020000000000f9c7780501000000200000009dc1d7549c714775c4accfd62e0cf750ee370ed47e158d2bebff46f3c5631804";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
