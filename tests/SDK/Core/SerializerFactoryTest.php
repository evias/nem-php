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
}
