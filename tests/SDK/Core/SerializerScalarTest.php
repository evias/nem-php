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

class SerializerScalarTest
    extends TestCase
{
    /**
     * Unit test for *Null-String Serialization*.
     * 
     * @return void
     */
    public function testSerializeNullString()
    {
        $serializer = Serializer::getInstance();

        // test null value
        $serialized = $serializer->serializeString(null);

        $expectUInt8 = [255,255,255,255];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));

        // test empty string
        $expectUInt8 = [0, 0, 0, 0];
        $serialized = $serializer->serializeString("");
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *testing String Serialization*.
     * 
     * @depends testSerializeNullString
     * @return void
     */
    public function testSerializeString()
    {
        $input = "testing";
        $originSize = strlen($input);
        $serializer = Serializer::getInstance();
        $serialized = $serializer->serializeString($input);

        $expectUInt8 = [7, 0, 0, 0, 116, 101, 115, 116, 105, 110, 103];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4 + $originSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *Null-UInt8 Serialization*.
     * 
     * @return void
     */
    public function testSerializeNullUInt8Array()
    {
        $serializer = Serializer::getInstance();

        // test null value
        $serialized = $serializer->serializeUInt8(null);

        $expectUInt8 = [255,255,255,255];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));

        // test empty array
        $expectUInt8 = [0,0,0,0];
        $serialized = $serializer->serializeUInt8([]);
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *testing UInt8 Serialization*.
     * 
     * @depends testSerializeNullUInt8Array
     * @return void
     */
    public function testSerializeUInt8Array()
    {
        // prepare
        $input  = "testing";
        $originSize = strlen($input);
        $buffer = new Buffer($input, $originSize);
        $uint8  = $buffer->toUInt8();

        // act
        $serializer = Serializer::getInstance();
        $serialized = $serializer->serializeUInt8($uint8);

        $expectSize = 4 + $originSize;
        $expectUInt8 = [7, 0, 0, 0, 116, 101, 115, 116, 105, 110, 103];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals($expectSize, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *Null-Long Serialization*.
     * 
     * @return void
     */
    public function testSerializeNullLong()
    {
        $serializer = Serializer::getInstance();

        // test null value
        $serialized = $serializer->serializeLong(null);

        $expectUInt8 = [255, 255, 255, 255, 0, 0, 0, 0];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(8, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));

        // test empty array
        $expectUInt8 = [0, 0, 0, 0, 0, 0, 0, 0];
        $serialized = $serializer->serializeLong(0);
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(8, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *testing Long Serialization*.
     * 
     * @return void
     */
    public function testSerializeLongNumber()
    {
        $serializer = Serializer::getInstance();
        $one = $serializer->serializeLong(1);
        $two = $serializer->serializeLong(2);
        $hundred = $serializer->serializeLong(100);
        $twoHundred = $serializer->serializeLong(256);
        $thousand = $serializer->serializeLong(1000);
        $tenThousand = $serializer->serializeLong(10000);
        $maxMosaic = $serializer->serializeLong(9000000000);

        $expectOne = [1, 0, 0, 0, 0, 0, 0, 0];
        $expectTwo = [2, 0, 0, 0, 0, 0, 0, 0];
        $expectHundred = [100, 0, 0, 0, 0, 0, 0, 0];
        $expectTwoHundred = [0, 1, 0, 0, 0, 0, 0, 0];
        $expectThousand = [232, 3, 0, 0, 0, 0, 0, 0];
        $expectTenThousand = [16, 39, 0, 0, 0, 0, 0, 0];
        $expectMaxMosaic = [0, 26, 113, 24, 2, 0, 0, 0];

        $this->assertEquals(json_encode($expectOne), json_encode($one));
        $this->assertEquals(json_encode($expectTwo), json_encode($two));
        $this->assertEquals(json_encode($expectHundred), json_encode($hundred));
        $this->assertEquals(json_encode($expectTwoHundred), json_encode($twoHundred));
        $this->assertEquals(json_encode($expectThousand), json_encode($thousand));
        $this->assertEquals(json_encode($expectTenThousand), json_encode($tenThousand));
        $this->assertEquals(json_encode($expectMaxMosaic), json_encode($maxMosaic));
    }
}
