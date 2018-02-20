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

class CreateTest
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
        $buffer = new Buffer();
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertEmpty($buffer->getBinary());
    }

    /**
     * Unit test for *Empty Hexadecimal Buffer creation*.
     *
     * This should produce a Buffer with an empty
     * binary representation.
     *
     * @return void
     */
    public function testCreateEmptyHexBuffer()
    {
        $buffer = Buffer::fromHex();
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
        $hex = '80000000';
        $buffer = Buffer::fromHex($hex);
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
        $bytes = 4; // 1-byte short ('11' at the end is too much)
        Buffer::fromHex('4141414111', $bytes);
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
        $hex = '41414141';
        $buffer = Buffer::fromHex($hex);
        $this->assertInstanceOf(Buffer::class, $buffer);
        $this->assertNotEmpty($buffer->getBinary());
    }

    /**
     * Unit test for *Integer Buffer creation*
     *
     * @dataProvider decimalVectorsProvider
     */
    public function testCreateDecimalBuffer($int, $size, $expectedHex, $math)
    {
        $buffer = Buffer::fromInt($int, $size, $math);
        $this->assertEquals($expectedHex, $buffer->getHex());
    }

    /**
     * Data provider for `testDecimalVectors` Unit Test.
     *
     * @return array
     */
    public function decimalVectorsProvider()
    {
        $math = EccFactory::getAdapter();

        return [
            /** $int, $size, $expected, $impl */
            ['1',  1,      '01', $math],
            ['1',  null,   '01', $math],
            ['20', 1,      '14', $math],
            ['16705', 2,   '4141', $math],
        ];
    }
}
