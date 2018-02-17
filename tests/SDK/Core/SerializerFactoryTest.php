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
use NEM\Models\ModelCollection;

class SerializerFactoryTest
    extends TestCase
{
    /**
     * Unit test for *dynamic serialization of data types*.
     * 
     * @return void
     */
    public function testSerializerFactory()
    {
        $int32 = 290888;
        $uint8 = [72, 114, 4, 0, 0, 0, 0, 0];
        $char  = "290888";

        // test factory method serialize()
        $serializer = Serializer::getInstance();
        $serNull  = $serializer->serialize(null);
        $serInt   = $serializer->serialize($int32);
        $serChar  = $serializer->serialize($char);
        $serUInt8 = $serializer->serialize($uint8);

        $expectUInt8_Null = [255, 255, 255, 255];
        $expectUInt8_Int  = [72, 112, 4, 0, 0, 0, 0, 0];
        $expectUInt8_Char = [6, 0, 0, 0, 50, 57, 48, 56, 56, 56];

        $expectSize = 4 + count($uint8);
        $uint8Result = array_merge([$expectSize - 4, 0, 0, 0], $uint8);
        $expectUInt8_UInt8= $uint8Result;

        $this->assertEquals(count($expectUInt8_Null), count($serNull));
        $this->assertEquals(count($expectUInt8_Int), count($serInt));
        $this->assertEquals(count($expectUInt8_Char), count($serChar));
        $this->assertEquals($expectSize, count($serUInt8));

        $this->assertEquals(json_encode($expectUInt8_Null), json_encode($serNull));
        $this->assertEquals(json_encode($expectUInt8_Int), json_encode($serInt));
        $this->assertEquals(json_encode($expectUInt8_Char), json_encode($serChar));
        $this->assertEquals(json_encode($expectUInt8_UInt8), json_encode($serUInt8));
    }

    /**
     * Unit test for *base Model serialization* without specialization
     * and using the factory method serialize().
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

        // test serialization process specialization in Serializer
        $serializer = Serializer::getInstance();
        $serModel   = $serializer->serialize($model);

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

        $this->assertEquals($expectSize, count($serModel));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serModel));
    }

    /**
     * Unit test for *base Model serialization* without specialization
     * and using the factory method serialize().
     * 
     * @return void
     */
    public function testSerializerCollectionBase()
    {
        $model1 = new Model(["attribute_one" => "value_pos1_1"]);
        $model2 = new Model(["attribute_one" => "value_pos1_2"]);
        $collection = new ModelCollection([$model1, $model2]);

        // test serialization process specialization in Serializer
        $serializer = Serializer::getInstance();
        $serCollection = $serializer->serialize($collection);

        // expected results
        $expectJSON = '[{"attribute_one":"value_pos1_1"},'
                      .'{"attribute_one":"value_pos1_2"}]';
        $expectSize = 4 + strlen($expectJSON);
        $expectUInt8 = [
             67,   0,   0,   0, 
             91, 123,  34,  97, 116, 116, 114, 105,
             98, 117, 116, 101,  95, 111, 110, 101,
             34,  58,  34, 118,  97, 108, 117, 101,
             95, 112, 111, 115,  49,  95,  49,  34,
            125,  44, 123,  34,  97, 116, 116, 114,
            105,  98, 117, 116, 101,  95, 111, 110,
            101,  34,  58,  34, 118,  97, 108, 117,
            101,  95, 112, 111, 115,  49,  95,  50,
             34, 125,  93
        ];

        // assert
        $this->assertEquals($expectSize, count($serCollection));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serCollection));
    }
}
