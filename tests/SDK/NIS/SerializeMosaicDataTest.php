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
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Tests\SDK\Core;

use NEM\Tests\TestCase;
use NEM\Core\Serializer;
use NEM\Core\Buffer;
use NEM\Models\Model;

// This unit test will test all serializer process Specializations
use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;

class SerializeMosaicDataTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: Mosaic*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_Mosaic()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"
        ]);

        // test specialized Mosaic::serialize() serialization process
        $serialized = $mosaic->serialize();

        $expectUInt8 = [
            24,  0,   0,   0,
            9,   0,   0,   0, 101, 118, 105,  97,
            115,  46, 115, 100, 107,   7,   0,   0,
            0, 110, 101, 109,  45, 112, 104, 112
        ];
        $expectSize = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicAttachment()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"
        ]);

        $attachment = new MosaicAttachment([
            "mosaicId" => $mosaic->toDTO(),
            "quantity" => 1
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $attachment->serialize();

        // expected results
        $expectUInt8 = [
             36,   0,   0,   0,
             24,   0,   0,   0,   9,   0,   0,   0,
            101, 118, 105,  97, 115,  46, 115, 100,
            107,   7,   0,   0,   0, 110, 101, 109,
             45, 112, 104, 112,   1,   0,   0,   0, 
              0,   0,   0,   0
        ];
        $expectSize = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicAttachments collection*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicAttachmentsCollection()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"
        ]);

        $attachment1 = new MosaicAttachment([
            "mosaicId" => $mosaic->toDTO(),
            "quantity" => 1
        ]);

        $attachment2 = new MosaicAttachment([
            "mosaicId" => $mosaic->toDTO(),
            "quantity" => 2
        ]);

        // create MosaicAttachments collection instance
        $collection = new MosaicAttachments([$attachment1, $attachment2]);

        // test specialized MosaicAttachments::serialize() serialization process
        $serialized = $collection->serialize();

        // expected results
        $expectUInt8 = [
              2,   0,   0,   0,
             36,   0,   0,   0,  24,   0,   0,   0,
              9,   0,   0,   0, 101, 118, 105,  97,
            115,  46, 115, 100, 107,   7,   0,   0,
              0, 110, 101, 109,  45, 112, 104, 112,
              1,   0,   0,   0,   0,   0,   0,   0,
             36,   0,   0,   0,  24,   0,   0,   0,
              9,   0,   0,   0, 101, 118, 105,  97,
            115,  46, 115, 100, 107,   7,   0,   0,
              0, 110, 101, 109,  45, 112, 104, 112,
              2,   0,   0,   0,   0,   0,   0,   0
        ];
        $expectSize = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }
}
