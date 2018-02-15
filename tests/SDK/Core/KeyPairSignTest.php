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
use NEM\Core\KeyPair;
use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;

use kornrunner\Keccak;
use \desktopd\SHA3\Sponge as Keccak_SHA3;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519;
use \ParagonIE_Sodium_Core_X25519 as Ed25519ref10;

class KeyPairSignTest
    extends TestCase
{
    /**
     * Unit test for *Basic KeyPair Signing*.
     *
     * @return void
     */
    public function testBasicKeyPairSign()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");
        $signed = $kp1->sign("Hello, World!");
        $uint8s = $signed->toUInt8();

        // check uint8 content
        $expectUInt8 = "[150, 138, 237, 214, 135, 191, 84, 26, 42, 125, 101, 203, 211, 22, 246, 141, 228, 123, 96, 140, 255, 163, 25, 4, 243, 113, 75, 68, 133, 34, 20, 244, 71, 178, 13, 242, 238, 54, 184, 250, 47, 17, 244, 145, 170, 61, 225, 62, 39, 222, 249, 105, 186, 223, 172, 109, 111, 122, 2, 1, 185, 15, 173, 13]";
        $this->assertEquals(64, count($uint8s));
        $this->assertEquals($expectUInt8, json_encode($uint8s));

        // check binary representation
        $this->assertEquals(64, strlen($signed->getBinary()));

        // check hexadecimal representation
        $expectHex = "968aedd687bf541a2a7d65cbd316f68de47b608cffa31904f3714b44852214f447b20df2ee36b8fa2f11f491aa3de13e27def969badfac6d6f7a0201b90fad0d";
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($expectHex, $signed->getHex());
    }
}
