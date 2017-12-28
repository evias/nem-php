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
namespace NEM\Tests\SDK\Buffer;

use NEM\Core\Buffer;
use NEM\Tests\TestCase;

class OperationsTest
    extends TestCase
{
    /**
     * Unit test for *Strict Buffer Sizes*.
     *
     * Test several Buffer creation with size parameter
     * to make sure the Buffer class implements a stricly
     * sized Buffer content.
     *
     * @return void
     */
    public function testGetSize()
    {
        $this->assertEquals(1, Buffer::fromHex('41')->getSize());
        $this->assertEquals(4, Buffer::fromHex('41414141')->getSize());

        // strictly-sized + padded Buffer
        $this->assertEquals(4, Buffer::fromHex('41', 4)->getSize());
    }

    /**
     * Unit test for *Buffer comparison operator*.
     *
     * This should implicitely provide unmatching Buffers to
     * test the comparison operator correctly.
     *
     * @return void
     */
    public function testEquals()
    {
        $first  = Buffer::fromHex('ab');
        $second = Buffer::fromHex('ab');
        $third  = Buffer::fromHex('ac');

        $firstExtraLong = Buffer::fromHex('ab', 10);
        $firstShort = new Buffer('', 0);

        // matching sizes
        $this->assertTrue($first->equals($second));
        $this->assertFalse($first->equals($third));
        $this->assertFalse($second->equals($third));

        // non-matching sizes
        $this->assertFalse($first->equals($firstExtraLong));
        $this->assertFalse($first->equals($firstExtraLong));
        $this->assertFalse($first->equals($firstShort));
    }

    /**
     * Unit test for *Buffer Padding*.
     *
     * Padding is automatic in case the Buffer size provided
     * is *bigger* than the Buffer Internal Size.
     *
     * @return void
     */
    public function testPadding()
    {
        // 2-bytes short Buffer Content
        $buffer = Buffer::fromHex('41414141', 6);

        // sizes checks
        $this->assertEquals(4, $buffer->getInternalSize());
        $this->assertEquals(6, $buffer->getSize());

        // striclty sized buffer padding
        $this->assertEquals("000041414141", $buffer->getHex());
    }

    /**
     * Unit test for *Buffer slicing*.
     *
     * Check simple 
     * 
     * @return void
     */
    public function testSlice()
    {
        $a = Buffer::fromHex("11000011");
        $this->assertEquals("1100", $a->slice(0, 2)->getHex());
        $this->assertEquals("0011", $a->slice(2, 4)->getHex());

        $b = Buffer::fromHex("00111100");
        $this->assertEquals("0011", $b->slice(0, 2)->getHex());
        $this->assertEquals("1100", $b->slice(2, 4)->getHex());

        // automatic left padding (1-byte short content)
        $c = Buffer::fromHex("111100", 4);
        $this->assertEquals("0011", $c->slice(0, 2)->getHex());
        $this->assertEquals("1100", $c->slice(2, 4)->getHex());
    }
}
