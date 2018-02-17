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

class SerializerSpecializationTest
    extends TestCase
{
    /**
     * Unit test for *base Model serialization* without specialization
     * and using internal Model::serialize() method.
     * 
     * This will test the actual content 
     * 
     * @return void
     */
    public function testSerializerModelBase()
    {
        $model = new Model([
            "attribute_one" => "value_pos1",
            "attribute_two" => "value_pos2",
            "attribute_three" => "value_pos3",
        ]);

        // test basic serialization
        $serialized = $model->serialize();

        $expectJSON = '{"attribute_one":"value_pos1","attribute_two":"value_pos2","attribute_three":"value_pos3"}';
        $expectSize = 4 + strlen($expectJSON);
        $expectUInt8 = [
            90,    0,   0,   0,
            123,  34,  97, 116, 116, 114, 105,  98,
            117, 116, 101,  95, 111, 110, 101,  34,
            58,   34, 118,  97, 108, 117, 101,  95,
            112, 111, 115,  49,  34,  44,  34,  97,
            116, 116, 114, 105,  98, 117, 116, 101,
            95,  116, 119, 111,  34,  58,  34, 118,
            97,  108, 117, 101,  95, 112, 111, 115,
            50,   34,  44,  34,  97, 116, 116, 114,
            105,  98, 117, 116, 101,  95, 116, 104,
            114, 101, 101,  34,  58,  34, 118,  97,
            108, 117, 101,  95, 112, 111, 115, 51, 
            34,  125
        ];

        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

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
}
