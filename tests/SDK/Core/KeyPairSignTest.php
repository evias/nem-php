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
     * @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
     * @return void
     */
    public function testBasicKeyPairSign()
    {
        // @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
        $binary = hex2bin("8ce03cd60514233b86789729102ea09e867fc6d964dea8c2018ef7d0a2e0e24bf7e348e917116690b9");
        $kp1 = KeyPair::create("abf4cf55a2b3f742d7543d9cc17f50447b969e6e06f5ea9195d428ab12b7318d");
        $signed = $kp1->sign($binary, "keccak-512");

        // check binary representation
        $this->assertEquals(64, strlen($signed->getBinary()));

        // check hexadecimal representation
        $expectHex = "d9cec0cc0e3465fab229f8e1d6db68ab9cc99a18cb0435f70deb6100948576cd5c0aa1feb550bdd8693ef81eb10a556a622db1f9301986827b96716a7134230c";
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($expectHex, $signed->getHex());
    }
}
