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
namespace NEM\Tests\SDK\Buffer;

use NEM\Core\Buffer;
use NEM\Tests\TestCase;
use Mdanter\Ecc\EccFactory;

class BufferizeTest
    extends TestCase
{
    /**
     * Unit test for *Empty Buffer creation*.
     *
     * This should produce a Buffer with an empty
     * binary representation.
     *
     * @return void
     */
    public function testCreateEmptyBuffer()
    {
        $buffer = Buffer::bufferize("");
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertEmpty($buffer->getBinary());
    }

    /**
     * Unit test for *Buffer creation*.
     *
     * This should also populate the binary representation
     * of the Buffer.
     *
     * @return void
     */
    public function testCreateBuffer()
    {
        $data = '1234';
        $buffer = Buffer::bufferize($data);
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertNotEmpty($buffer->getBinary());
    }

    /**
     * Unit test for *Invalid byteSize length* on Buffer
     * creation.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Byte string exceeds maximum size
     */
    public function testCreateMaxBufferExceeded()
    {
        $bytes = 4;
        $hex = "41414141414141414141414141414141"; // 32-bytes
        Buffer::bufferize($hex, $bytes);
    }

    /**
     * Unit test for *Hexadecimal Buffer creation*.
     *
     * This should also populate the binary representation
     * of the Buffer.
     *
     * @return void
     */
    public function testCreateHexBuffer()
    {
        // only 32-bytes multiple supported by Buffer::bufferize()
        $hex = '41414141414141414141414141414141';
        $buffer = Buffer::bufferize($hex);
        $hbuf   = Buffer::fromHex($hex);

        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertNotEmpty($buffer->getBinary());
        $this->assertEquals($hbuf->getBinary(), $buffer->getBinary());
        $this->assertEquals($hex, $buffer->getHex());
    }

    /**
     * Data provider for `testDecimalVectors` Unit Test.
     *
     * @return array
     */
    public function decimalVectorsProvider()
    {
        return [
            /** $int, $size, $expected */
            [1,  1,      '01'],
            [1,  null,   '01'],
            [20, 1,      '14'],
            [16705, 2,   '4141'],
        ];
    }

    /**
     * Unit test for *Integer Buffer creation*
     *
     * @dataProvider decimalVectorsProvider
     */
    public function testCreateDecimalBuffer($int, $size, $expectedHex)
    {
        $buffer = Buffer::bufferize($int, $size);
        $this->assertEquals($expectedHex, $buffer->getHex());
    }
}
