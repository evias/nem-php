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
use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encoder;

class KeyPairBaseTest
    extends TestCase
{
    /**
     * Unit test for *Private Key UInt8 Array* representation in the
     * KeyPair class.
     *
     * This unit test is linked to a Buffer feature but is
     * tested here to make sure hexadecimal data in the form
     * of NIS private key and public keys are handled correctly.
     *
     * @return void
     */
    public function testPrivateKeyUIntArray()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");

        $unsignedCharRepresentation = [
            231, 124, 132, 51,
            30, 219, 250, 61,
            32, 156, 78, 104,
            128, 156, 152, 166,
            52, 173, 110, 136,
            145, 228, 23, 68,
            85, 195, 59, 233,
            221, 37, 252, 229
        ];

        $this->assertEquals($unsignedCharRepresentation, Buffer::fromHex($kp1->getPrivateKey("hex"))->toUInt8());
    }

    /**
     * Unit test for *Secret Key UInt8 Array* representation in the
     * KeyPair class.
     *
     * This unit test is linked to a Buffer feature but is
     * tested here to make sure hexadecimal data in the form
     * of NIS private key and public keys are handled correctly.
     *
     * The Secret Key is the reversed byte-level representation of the
     * private key.
     *
     * @depends testPrivateKeyUIntArray
     * @return void
     */
    public function testSecretKeyUIntArray()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");

        // SECRET KEY is byte-level reversed representation of the private key.
        $unsignedCharRepresentation = [
            229, 252, 37, 221,
            233, 59, 195, 85,
            68, 23, 228, 145,
            136, 110, 173, 52,
            166, 152, 156, 128,
            104, 78, 156, 32,
            61, 250, 219, 30,
            51, 132, 124, 231
        ];

        $this->assertEquals($unsignedCharRepresentation, Buffer::fromHex($kp1->getSecretKey("hex"))->toUInt8());
    }

    /**
     * Unit test for *Secret Key Int32 Array* representation in the
     * KeyPair class.
     *
     * This unit test is linked to a Buffer feature but is
     * tested here to make sure hexadecimal data in the form
     * of NIS private key and public keys are handled correctly.
     *
     * The WordArray representation is currently not used in the 
     * SDK.
     *
     * @return void
     */
    public function testSecretKeyWordArray()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");

        $expected = [
            -436460067, 
            -381959339, 
            1142416529, 
            -2006012620, 
            -1499947904, 
            1749982240, 
            1039850270, 
            864320743
        ];

        $encoder = new Encoder;
        $actual  = $encoder->ua2words(Buffer::fromHex($kp1->getSecretKey("hex"))->toUInt8());
        $this->assertEquals($expected, $actual);
    }

    /**
     * Unit test for *Invalid Private Key SIZE error*.
     *
     * @expectedException \NEM\Errors\NISInvalidPrivateKeySizeException
     * @return void
     */
    public function testInvalidPrivateKeySizeError()
    {
        $kp = KeyPair::create("1234"); // should be 64 characters..
    }

    /**
     * Unit test for *Invalid Private Key CONTENT error*.
     *
     * @expectedException \NEM\Errors\NISInvalidPrivateKeyContentException
     * @return void
     */
    public function testInvalidPrivateKeyContentError()
    {
        // first character 'z' is not a valid hexadecimal character.
        $kp = KeyPair::create("z0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcde");
    }
}
