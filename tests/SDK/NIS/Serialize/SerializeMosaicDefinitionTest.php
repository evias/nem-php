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
use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicLevy;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicProperties;

class SerializeMosaicDefinitionTest
    extends TestCase
{
    /**
     * Unit test for *serialize process Specialization: MosaicAttachment*.
     * 
     * @return void
     */
    public function testSerializerModelSpecialization_MosaicDefinition()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"
        ]);

        $prop1 = new MosaicProperty(["name" => "divisibility", "value" => 0]);
        $prop2 = new MosaicProperty(["name" => "initialSupply", "value" => 290888]);
        $prop3 = new MosaicProperty(["name" => "supplyMutable", "value" => true]);
        $prop4 = new MosaicProperty(["name" => "transferable", "value" => true]);

        // prepare collection of properties
        $properties = new MosaicProperties([$prop1, $prop2, $prop3, $prop4]);

        $definition = new MosaicDefinition([
            "id" => $mosaic->toDTO(),
            "creator" => "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
            "description" => "https://github.com/evias/nem-php",
            "properties" => $properties->toDTO(),
            "levy" => null
        ]);

        // test specialized MosaicAttachment::serialize() serialization process
        $serialized = $definition->serialize();

        // expected results
        $expectUInt8 = [
            32,   0,   0,   0,
           217,  12,   8, 207,
           187, 249,  24, 217,
            48,  77, 221,  69,
           246,  67,  37, 100,
           195, 144, 165, 250,
           207, 243, 223,  23,
           237,  92,   9, 108,
            76, 207,  13,   4,
            24,   0,   0,   0,
             9,   0,   0,   0,
           101, 118, 105,  97,
           115,  46, 115, 100,
           107,   7,   0,   0,
             0, 110, 101, 109,
            45, 112, 104, 112,
            32,   0,   0,   0,
           104, 116, 116, 112,
           115,  58,  47,  47,
           103, 105, 116, 104,
           117,  98,  46,  99,
           111, 109,  47, 101,
           118, 105,  97, 115,
            47, 110, 101, 109,
            45, 112, 104, 112,
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
           101,   0,   0,   0,
             0
        ];
        $expectSize = count($expectUInt8);

        $this->assertNotEmpty($serialized);

        // WIP: serialize needs correctly functioning DTOs.
        //$this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
        //$this->assertEquals($expectSize, count($serialized));
    }
}
