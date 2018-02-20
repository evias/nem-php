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
use NEM\Core\Encoder;
use NEM\Tests\TestCase;

class EncoderUtf8Test
    extends TestCase
{
    /**
     * Unit test for *Empty UTF8 string creation*.
     *
     * @return void
     */
    public function testCreateEmptyUtf8String()
    {
        $enc  = new Encoder();
        $utf8 = $enc->hex2bin("");

        $this->assertTrue(is_string($utf8));
        $this->assertEmpty($utf8);
    }

    /**
     * Data provider for test *testCreateUtf8FromHex*
     */
    public function utf8FromHexVectorsProvider()
    {
        return [
            ["1", hex2bin("10"), "10"], // invalid "1" missing 1 character in hexit (auto right pad)
            ["41", "A", "41"],
            ["415", hex2bin("4150"), "4150"], // hexit automatically right zero-padded
            ["416", hex2bin("4160"), "4160"], // hexit automatically right zero-padded
            ["4141", "AA", "4141"],
            ["42", "B", "42"],
        ];
    }

    /**
     * Unit test for *UTF8 string creation from hexadecimal notation*.
     *
     * @depends testCreateEmptyUtf8String
     * @dataProvider utf8FromHexVectorsProvider
     *
     * @param   string  $hex
     * @param   string  $expectedUtf8
     * @param   string  $expectedHex
     * @return void
     */
    public function testCreateUtf8FromHex($hex, $expectedUtf8, $expectedHex)
    {
        $enc   = new Encoder();
        $utf8  = $enc->hex2bin($hex);
        $fromBin = $enc->bin2hex($utf8);
        $toHex = bin2hex($utf8);

        $this->assertTrue(is_string($utf8));
        $this->assertEquals($expectedUtf8, $utf8);
        $this->assertEquals($expectedHex, $toHex);
        $this->assertEquals($fromBin, $expectedHex);
    }
}
