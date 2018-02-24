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

// This unit test will test all serializer process Specializations
use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Models\MosaicLevy;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicProperties;

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

    /**
     * Unit test for *serialize process Specialization: MosaicLevy*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicLevy()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"
        ]);

        $levy = new MosaicLevy([
            "type" => MosaicLevy::TYPE_ABSOLUTE, // 1
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
            "mosaicId" => $mosaic->toDTO(),
            "fee"  => 10
        ]);

        // test specialized MosaicLevy::serialize() serialization process
        $serialized = $levy->serialize();

        // expected results
        $expectUInt8 = [
            84,   0,   0,   0,
             1,   0,   0,   0,
            40,   0,   0,   0,
            84,  68,  87,  90,
            53,  53,  82,  53,
            86,  73,  72,  83,
            72,  53,  87,  87,
            75,  54,  67,  69,
            71,  65,  73,  80,
            55,  68,  51,  53,
            88,  86,  70,  90,
            51,  82,  85,  50,
            83,  53,  85,  81,
            24,   0,   0,   0,
             9,   0,   0,   0,
           101, 118, 105,  97,
           115,  46, 115, 100,
           107,   7,   0,   0,
             0, 110, 101, 109,
            45, 112, 104, 112,
            10,   0,   0,   0,
             0,   0,   0,   0
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
    public function testSerializerModelSpecialization_MosaicProperty()
    {
        // Step 1 PROPERTY: divisibility
        $property = new MosaicProperty([
            "name" => "divisibility",
            "value" => 0 // will be auto cast to string (NIS constraint)
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $property->serialize();

        // expected results
        $expectUInt8 = [21,0,0,0,12,0,0,0,100,105,118,105,115,105,98,105,108,105,116,121,1,0,0,0,48];
        $expectSize = count($expectUInt8);

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));

        // Step 2 PROPERTY: initialSupply
        $property = new MosaicProperty([
            "name" => "initialSupply",
            "value" => 290888 // will be auto cast to string (NIS constraint
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $property->serialize();

        // expected results
        $expectUInt8 = [27,0,0,0,13,0,0,0,105,110,105,116,105,97,108,83,117,112,112,108,121,6,0,0,0,50,57,48,56,56,56];
        $expectSize = count($expectUInt8);

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));

        // Step 3 PROPERTY: supplyMutable
        $property = new MosaicProperty([
            "name" => "supplyMutable",
            "value" => true // will be auto cast to string (NIS constraint
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $property->serialize();

        // expected results
        $expectUInt8 = [25,0,0,0,13,0,0,0,115,117,112,112,108,121,77,117,116,97,98,108,101,4,0,0,0,116,114,117,101];
        $expectSize = count($expectUInt8);

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));

        // Step 4 PROPERTY: transferable
        $property = new MosaicProperty([
            "name" => "transferable",
            "value" => false // will be auto cast to string (NIS constraint
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $property->serialize();

        // expected results
        $expectUInt8 = [25,0,0,0,12,0,0,0,116,114,97,110,115,102,101,114,97,98,108,101,5,0,0,0,102,97,108,115,101];
        $expectSize = count($expectUInt8);

        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        $this->assertEquals($expectSize, count($serialized));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicProperties collection*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicPropertiesCollection()
    {
        // create MosaicProperties collection instance
        $collection = new MosaicProperties([
            new MosaicProperty(["name" => "divisibility", "value"  => 0]),
            new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
            new MosaicProperty(["name" => "supplyMutable", "value" => true]),
            new MosaicProperty(["name" => "transferable", "value" => true]),
        ]);

        // test specialized MosaicProperties::serialize() serialization process
        $serialized = $collection->serialize();

        // expected results
        $expectUInt8 = [4,0,0,0,21,0,0,0,12,0,0,0,100,105,118,105,115,105,98,105,108,105,116,121,1,0,0,0,48,27,0,0,0,13,0,0,0,105,110,105,116,105,97,108,83,117,112,112,108,121,6,0,0,0,50,57,48,56,56,56,25,0,0,0,13,0,0,0,115,117,112,112,108,121,77,117,116,97,98,108,101,4,0,0,0,116,114,117,101,24,0,0,0,12,0,0,0,116,114,97,110,115,102,101,114,97,98,108,101,4,0,0,0,116,114,117,101];
        $expectSize = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

}
