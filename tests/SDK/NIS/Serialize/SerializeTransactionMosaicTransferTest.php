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
use NEM\Models\Transaction\MosaicTransfer;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Models\Mosaic;

class SerializeTransactionMosaicTransferTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: Transaction\MosaicTransfer*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicTransfer_OneMosaic()
    {
        $xem = new Mosaic([
            "namespaceId" => "nem",
            "name"        => "xem"
        ]);

        $transaction = new MosaicTransfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91825062]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91828662]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => Fee::FEE_FACTOR,
            "amount"    => 1000000,
            "recipient" => "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
            "mosaics"   => (new MosaicAttachments([
                new MosaicAttachment([
                    "mosaicId" => $xem->toDTO(),
                    "quantity" => 10000000,
                ])
            ]))->toDTO(),
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "8de6be08d6b3c28dc19bff79831003900477e34e27c32b73e3ddc46a07e16445"
                          ."c3a25e29716a9069b0bae685531ce619c802c2b0d41a24ffa5f1fe40ae3e720a",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,1,0,0,2,0,0,152,166,35,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,80,195,0,0,0,0,0,0,182,49,121,5,40,0,0,0,84,68,50,80,69,89,50,51,89,54,79,51,76,78,71,65,79,52,89,74,89,78,68,82,81,83,51,73,82,84,69,67,55,80,90,85,73,87,76,84,64,66,15,0,0,0,0,0,1,0,0,0,26,0,0,0,14,0,0,0,3,0,0,0,110,101,109,3,0,0,0,120,101,109,128,150,152,0,0,0,0,0
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "0101000002000098a623790520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d0450c3000000000000b631790528000000544432504559323359364f334c4e47414f34594a594e4452515333495254454337505a5549574c5440420f0000000000010000001a0000000e000000030000006e656d0300000078656d8096980000000000";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicTransfer_MultiMosaic()
    {
        $xem = new Mosaic([
            "namespaceId" => "nem",
            "name"        => "xem"
        ]);
        $dim = new Mosaic([
            "namespaceId" => "dim",
            "name"        => "coin"
        ]);
        $token = new Mosaic([
            "namespaceId" => "dim",
            "name"        => "token"
        ]);

        $transaction = new MosaicTransfer([
            "timeStamp" => (new TimeWindow(["timeStamp" => 91826540]))->toDTO(),
            "deadline"  => (new TimeWindow(["timeStamp" => 91830140]))->toDTO(),
            "version"   => -1744830462,
            "fee"       => 3 * Fee::FEE_FACTOR,
            "amount"    => 1000000,
            "recipient" => "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
            "mosaics"   => (new MosaicAttachments([
                // intentionally unordered (lexicographically)
                // real ordered list will be :
                // dim:coin, dim:token, nem:xem

                new MosaicAttachment([
                    "mosaicId" => $xem->toDTO(),
                    "quantity" => 10000000,
                ]),
                new MosaicAttachment([
                    "mosaicId" => $dim->toDTO(),
                    "quantity" => 10000000,
                ]),
                new MosaicAttachment([
                    "mosaicId" => $token->toDTO(),
                    "quantity" => 10000000,
                ]),
            ]))->toDTO(),
            "signer"    => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "signature" => "654bfbb8ed447c004a2c2356babcaa4280a2fd4f9d3e83fd6a4c347db471ad85"
                          ."84f036e5419702e4c6cb6ad16607a4c149f28c49ab2b6379c8a9cc34fe3e2c0d",
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $transaction->serialize();
        $serialHex  = Buffer::fromUInt8($serialized)->getHex();

        // expected results
        $expectUInt8 = [
            1,1,0,0,2,0,0,152,108,41,121,5,32,0,0,0,217,12,8,207,187,249,24,217,48,77,221,69,246,67,37,100,195,144,165,250,207,243,223,23,237,92,9,108,76,207,13,4,240,73,2,0,0,0,0,0,124,55,121,5,40,0,0,0,84,68,50,80,69,89,50,51,89,54,79,51,76,78,71,65,79,52,89,74,89,78,68,82,81,83,51,73,82,84,69,67,55,80,90,85,73,87,76,84,64,66,15,0,0,0,0,0,3,0,0,0,27,0,0,0,15,0,0,0,3,0,0,0,100,105,109,4,0,0,0,99,111,105,110,128,150,152,0,0,0,0,0,28,0,0,0,16,0,0,0,3,0,0,0,100,105,109,5,0,0,0,116,111,107,101,110,128,150,152,0,0,0,0,0,26,0,0,0,14,0,0,0,3,0,0,0,110,101,109,3,0,0,0,120,101,109,128,150,152,0,0,0,0,0
        ];
        $expectSize = count($expectUInt8);
        $expectHex  = "01010000020000986c29790520000000d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04f0490200000000007c37790528000000544432504559323359364f334c4e47414f34594a594e4452515333495254454337505a5549574c5440420f0000000000030000001b0000000f0000000300000064696d04000000636f696e80969800000000001c000000100000000300000064696d05000000746f6b656e80969800000000001a0000000e000000030000006e656d0300000078656d8096980000000000";

        $this->assertNotEmpty($serialized);
        $this->assertEquals($expectHex, $serialHex);
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }
}
