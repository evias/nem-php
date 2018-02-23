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
use NEM\Models\Transaction\Transfer;

class SerializeTransactionTransferTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_Transfer_EmptyMessage()
    {
        $transaction = new Transfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91793055]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91796655]))->toDTO(),
            "version"   => -1744830463,
            "fee"       => 50000,
            "amount"    => 10000000,
            "recipient" => "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "764da83b32baf3fcdbb482e44e2943b42998c697856a409874699a2a87ac9bd0"
                          ."802d4f6d80a0dc64e808895d5aa2cce40d38edb1b93ed4b0640175816eab210c",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();

        // expected results
        $expectUInt8 = [
            1,
            1,   0,   0,   1,
            0,   0, 152, 159,
          166, 120,   5,  32,
            0,   0,   0, 217,
           12,   8, 207, 187,
          249,  24, 217,  48,
           77, 221,  69, 246,
           67,  37, 100, 195,
          144, 165, 250, 207,
          243, 223,  23, 237,
           92,   9, 108,  76,
          207,  13,   4,  80,
          195,   0,   0,   0,
            0,   0,   0, 175,
          180, 120,   5,  40,
            0,   0,   0,  84,
           68,  50,  80,  69,
           89,  50,  51,  89,
           54,  79,  51,  76,
           78,  71,  65,  79,
           52,  89,  74,  89,
           78,  68,  82,  81,
           83,  51,  73,  82,
           84,  69,  67,  55,
           80,  90,  85,  73,
           87,  76,  84, 128,
          150, 152,   0,   0,
            0,   0,   0
        ];
        $expectSize = count($expectUInt8);

        $this->assertNotEmpty($serialized);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
