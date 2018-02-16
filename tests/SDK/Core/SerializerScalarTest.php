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
        $serialized = $serializer->serializeString(null);

        $expectUInt8 = [255,255,255,255];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4, count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }

    /**
     * Unit test for *testing String Serialization*.
     * 
     * @return void
     */
    public function testSerializeString()
    {
        $input = "testing";
        $serializer = Serializer::getInstance();
        $serialized = $serializer->serializeString($input);

        $expectUInt8 = [7, 0, 0, 0, 116, 101, 115, 116, 105, 110, 103];
        $this->assertTrue(is_array($serialized));
        $this->assertEquals(4 + strlen($input), count($serialized));
        $this->assertEquals(json_encode($expectUInt8), json_encode($serialized));
    }
}
