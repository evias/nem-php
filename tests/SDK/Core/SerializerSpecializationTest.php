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

        // test Model::serialize() specialization
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

        // test ModelCollection::serialize() specialization
        $serCollection = $collection->serialize();

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
