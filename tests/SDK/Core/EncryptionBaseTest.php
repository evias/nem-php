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
use NEM\Core\Encryption;

class EncryptionBaseTest
    extends TestCase
{
    /**
     * Unit test for *non-incremental Keccak Hash creation*.
     *
     * @return void
     */
    public function testSimpleKeccak()
    {
        $hash = Encryption::hash("keccak-512", "testing");

        $expectHex = "9558a7ba9ac74b33b347703ffe33f8d561d86d9fcad1cfd63225fb55dfea50a0953a0efafd6072377f4c396e806d5bda0294cba28762740d8446fee45a276e4a";
        $this->assertEquals($expectHex, $hash->getHex());
    }

    /**
     * Unit test for *incremental Keccak Hash creation*.
     *
     * @return void
     */
    public function testIncrementalKeccak()
    {
        $hash = Encryption::hash_init("sha3-512");
        Encryption::hash_update($hash, "testing");
        $hash = Encryption::hash_final($hash);

        $expectHex = "881c7d6ba98678bcd96e253086c4048c3ea15306d0d13ff48341c6285ee71102a47b6f16e20e4d65c0c3d677be689dfda6d326695609cbadfafa1800e9eb7fc1";
        $this->assertEquals($expectHex, $hash->getHex());
    }
}
