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
namespace NEM\Tests\SDK\Core;

use NEM\Tests\TestCase;
use NEM\Core\Serializer;
use NEM\Core\Buffer;
use NEM\Models\Model;

// This unit test will test all serializer process Specializations
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicProperties;

class SerializeMosaicPropertiesTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicProperty*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicProperty()
    {
        $prop1 = new MosaicProperty([
            "name" => "divisibility",
            "value" => 0
        ]);

        $prop2 = new MosaicProperty([
            "name" => "initialSupply",
            "value" => 290888
        ]);

        // test specialized MosaicProperty::serialize() serialization process
        $serialized1 = $prop1->serialize();
        $serialized2 = $prop2->serialize();

        // expected results
        $expectUInt8_1 = [
            21,   0,   0,   0, 
            12,   0,   0,   0, 
           100, 105, 118, 105, 
           115, 105,  98, 105, 
           108, 105, 116, 121, 
             1,   0,   0,   0, 
            48
        ];

        $expectUInt8_2 = [
            27,   0,   0,   0, 
            13,   0,   0,   0, 
           105, 110, 105, 116, 
           105,  97, 108,  83, 
           117, 112, 112, 108, 
           121,   6,   0,   0, 
             0,  50,  57,  48, 
            56,  56,  56
        ];
        $expectSize_1 = count($expectUInt8_1);
        $expectSize_2 = count($expectUInt8_2);

        $this->assertEquals($expectSize_1, count($serialized1));
        $this->assertEquals(json_encode($expectUInt8_1), json_encode($serialized1));

        $this->assertEquals($expectSize_2, count($serialized2));
        $this->assertEquals(json_encode($expectUInt8_2), json_encode($serialized2));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicProperties collection*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicPropertiesCollection()
    {
        $prop1 = new MosaicProperty([
            "name" => "divisibility",
            "value" => 0
        ]);

        $prop2 = new MosaicProperty([
            "name" => "initialSupply",
            "value" => 290888
        ]);

        // create MosaicProperties collection instance
        // intentionally set initialSupply first, should be sorted
        // internally to reflect lexicographically sorting.
        $collection = new MosaicProperties([$prop2, $prop1]);

        // test specialized MosaicAttachments::serialize() serialization process
        $serialized = $collection->serialize();

        // expected results
        $expectUInt8 = [
            2,   0,   0,   0, 
           21,   0,   0,   0, 
           12,   0,   0,   0, 
          100, 105, 118, 105, 
          115, 105,  98, 105, 
          108, 105, 116, 121, 
            1,   0,   0,   0, 
           48,  27,   0,   0, 
            0,  13,   0,   0, 
            0, 105, 110, 105, 
          116, 105,  97, 108, 
           83, 117, 112, 112, 
          108, 121,   6,   0, 
            0,   0,  50,  57, 
           48,  56,  56,  56
        ];
        $expectSize = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));

        // test complete data set for MosaicProperties
        $prop3 = new MosaicProperty([
            "name" => "supplyMutable",
            "value" => true
        ]);

        $prop4 = new MosaicProperty([
            "name" => "transferable",
            "value" => true
        ]);

        // create a new collection instance with mixed properties
        // intentionally unordered properties list
        $collection2 = new MosaicProperties([$prop3, $prop1, $prop4, $prop2]);
        $serialized2 = $collection2->serialize();

        // expected results
        $expectUInt8 = [
            4,   0,   0,   0,
           21,   0,   0,   0,
           12,   0,   0,   0,
          100, 105, 118, 105,
          115, 105,  98, 105,
          108, 105, 116, 121,
            1,   0,   0,   0,
           48,  27,   0,   0,
            0,  13,   0,   0,
            0, 105, 110, 105,
          116, 105,  97, 108,
           83, 117, 112, 112,
          108, 121,   6,   0,
            0,   0,  50,  57,
           48,  56,  56,  56,
           25,   0,   0,   0,
           13,   0,   0,   0,
          115, 117, 112, 112,
          108, 121,  77, 117,
          116,  97,  98, 108,
          101,   4,   0,   0,
            0, 116, 114, 117,
          101,  24,   0,   0,
            0,  12,   0,   0,
            0, 116, 114,  97,
          110, 115, 102, 101,
          114,  97,  98, 108,
          101,   4,   0,   0,
            0, 116, 114, 117,
          101
        ];
        $expectSize  = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized2));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized2));
    }

    /**
     * Unit test for *serialize process Specialization: MosaicProperties collection*.
     * 
     * This test processes different input than the test before but the functionality
     * tested is the same.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicPropertiesAdvanced()
    {
        // prepare properties data
        $prop1 = new MosaicProperty(["name" => "divisibility", "value" => 4]);
        $prop2 = new MosaicProperty(["name" => "initialSupply", "value" => 10000000]);
        $prop3 = new MosaicProperty(["name" => "supplyMutable", "value" => false]);
        $prop4 = new MosaicProperty(["name" => "transferable", "value" => true]);

        // prepare collection of properties
        $collection = new MosaicProperties([$prop1, $prop2, $prop3, $prop4]);

        // test specialized MosaicAttachments::serialize() serialization process
        $serialized = $collection->serialize();

        // expected results
        $expectUInt8 = [
            4,   0,   0,   0,
           21,   0,   0,   0,
           12,   0,   0,   0,
          100, 105, 118, 105,
          115, 105,  98, 105,
          108, 105, 116, 121,
            1,   0,   0,   0,
           52,  29,   0,   0,
            0,  13,   0,   0,
            0, 105, 110, 105,
          116, 105,  97, 108,
           83, 117, 112, 112,
          108, 121,   8,   0,
            0,   0,  49,  48,
           48,  48,  48,  48,
           48,  48,  26,   0,
            0,   0,  13,   0,
            0,   0, 115, 117,
          112, 112, 108, 121,
           77, 117, 116,  97,
           98, 108, 101,   5,
            0,   0,   0, 102,
           97, 108, 115, 101,
           24,   0,   0,   0,
           12,   0,   0,   0,
          116, 114,  97, 110,
          115, 102, 101, 114,
           97,  98, 108, 101,
            4,   0,   0,   0,
          116, 114, 117, 101
        ];
        $expectSize  = count($expectUInt8);

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }
}
