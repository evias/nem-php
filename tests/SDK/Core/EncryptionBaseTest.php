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
        // @see https://github.com/trezor/trezor-crypto/commit/dc397ff0ed4014baecef85b40d82828e117819ca
        $binary = hex2bin("a6151d4904e18ec288243028ceda30556e6c42096af7150d6a7232ca5dba52bd2192e23daa5fa2bea3d4bd95efa2389cd193fcd3376e70a5c097b32c1c62c80af9d710211545f7cdddf63747420281d64529477c61e721273cfd78f8890abb4070e97baa52ac8ff61c26d195fc54c077def7a3f6f79b36e046c1a83ce9674ba1983ec2fb58947de616dd797d6499b0385d5e8a213db9ad5078a8e0c940ff0cb6bf92357ea5609f778c3d1fb1e7e36c35db873361e2be5c125ea7148eff4a035b0cce880a41190b2e22924ad9d1b82433d9c023924f2311315f07b88bfd42850047bf3be785c4ce11c09d7e02065d30f6324365f93c5e7e423a07d754eb314b5fe9db4614275be4be26af017abdc9c338d01368226fe9af1fb1f815e7317bdbb30a0f36dc69");

        // create incremental keccak sponge
        $sponge = Encryption::hash_init("keccak-256");
        Encryption::hash_update($sponge, $binary);

        // squeeze sponge
        $hash = Encryption::hash_final($sponge, false);

        $expectHex = "4e9e79ab7434f6c7401fb3305d55052ee829b9e46d5d05d43b59fefb32e9a619";
        $this->assertEquals($expectHex, $hash->getHex());
    }
}
