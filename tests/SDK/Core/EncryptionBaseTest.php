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
     * Unit test for lower level PHP Hasher implementations.
     * 
     * This test will make sure that the Encryption class delegates
     * to the PHP hasher correctly in both non-incremental and 
     * incremental hashing mode.
     * 
     * @return void
     */
    public function testPHPHasher()
    {
        // test simple hash
        $hash256 = Encryption::hash("sha3-256", "testing");
        $expectHex = "7f5979fb78f082e8b1c676635db8795c4ac6faba03525fb708cb5fd68fd40c5e";

        $this->assertEquals(64, strlen($hash256->getHex()));
        $this->assertEquals($expectHex, $hash256->getHex());

        // test incremental hash
        $incremental = Encryption::hash_init("sha3-256");
        Encryption::hash_update($incremental, "testing");
        $hashInc256  = Encryption::hash_final($incremental);

        $this->assertEquals($expectHex, $hashInc256->getHex());
    }

    /**
     * Unit test for *non-incremental Keccak Hash creation*.
     *
     * @return void
     */
    public function testKeccakHasher()
    {
        // keccak-512
        $hash512 = Encryption::hash("keccak-512", "testing");

        $expectHex = "9558a7ba9ac74b33b347703ffe33f8d561d86d9fcad1cfd63225fb55dfea50a0953a0efafd6072377f4c396e806d5bda0294cba28762740d8446fee45a276e4a";
        $this->assertEquals($expectHex, $hash512->getHex());

        // keccak-256
        $binary = hex2bin("a6151d4904e18ec288243028ceda30556e6c42096af7150d6a7232ca5dba52bd2192e23daa5fa2bea3d4bd95efa2389cd193fcd3376e70a5c097b32c1c62c80af9d710211545f7cdddf63747420281d64529477c61e721273cfd78f8890abb4070e97baa52ac8ff61c26d195fc54c077def7a3f6f79b36e046c1a83ce9674ba1983ec2fb58947de616dd797d6499b0385d5e8a213db9ad5078a8e0c940ff0cb6bf92357ea5609f778c3d1fb1e7e36c35db873361e2be5c125ea7148eff4a035b0cce880a41190b2e22924ad9d1b82433d9c023924f2311315f07b88bfd42850047bf3be785c4ce11c09d7e02065d30f6324365f93c5e7e423a07d754eb314b5fe9db4614275be4be26af017abdc9c338d01368226fe9af1fb1f815e7317bdbb30a0f36dc69");
        $hash256 = Encryption::hash("keccak-256", $binary);

        $expectHex = "4e9e79ab7434f6c7401fb3305d55052ee829b9e46d5d05d43b59fefb32e9a619";
        $this->assertEquals($expectHex, $hash256->getHex());
    }

    /**
     * Unit test for *SHA3-256 hash creation*.
     *
     * @dataProvider sha3_256_vectorsProvider
     * @return void
     */
    public function testSha3_256Hashes($expectHex, $dataHex)
    {
        $binaryData = hex2bin($dataHex);
        $hashed = Encryption::hash("keccak-256", $binaryData, true);

        $this->assertEquals(32, mb_strlen($hashed, "8bit"));
        $this->assertEquals($expectHex, bin2hex($hashed));
    }

    /**
     * Data vector provider for SHA3-256 tests.
     * 
     * @return array
     */
    public function sha3_256_vectorsProvider() 
    {
        return [
            ["4e9e79ab7434f6c7401fb3305d55052ee829b9e46d5d05d43b59fefb32e9a619",
             "a6151d4904e18ec288243028ceda30556e6c42096af7150d6a7232ca5dba52bd"
            ."2192e23daa5fa2bea3d4bd95efa2389cd193fcd3376e70a5c097b32c1c62c80a"
            ."f9d710211545f7cdddf63747420281d64529477c61e721273cfd78f8890abb40"
            ."70e97baa52ac8ff61c26d195fc54c077def7a3f6f79b36e046c1a83ce9674ba1"
            ."983ec2fb58947de616dd797d6499b0385d5e8a213db9ad5078a8e0c940ff0cb6"
            ."bf92357ea5609f778c3d1fb1e7e36c35db873361e2be5c125ea7148eff4a035b"
            ."0cce880a41190b2e22924ad9d1b82433d9c023924f2311315f07b88bfd428500"
            ."47bf3be785c4ce11c09d7e02065d30f6324365f93c5e7e423a07d754eb314b5f"
            ."e9db4614275be4be26af017abdc9c338d01368226fe9af1fb1f815e7317bdbb3"
            ."0a0f36dc69"],
            ["e83b50e8c83cb676a7dd64c055f53e5110d5a4c62245ceb8f683fd87b2b3ec77",
             "c070a957550b7b34113ee6543a1918d96d241f27123425db7f7b9004e047ffbe"
            ."05612e7fa8c54b23c83ea427e625e97b7a28b09a70bf6d91e478eeed01d79079"
            ."31c29ea86e70f2cdcfb243ccf7f24a1619abf4b5b9e6f75cbf63fc02baf4a820"
            ."a9790a6b053e50fd94e0ed57037cfc2bab4d95472b97d3c25f434f1cc0b1ede5"
            ."ba7f15907a42a223933e5e2dfcb518c3531975268c326d60fa911fbb7997eee3"
            ."ba87656c4fe7"],
            ["f7adf3465445938b0dfcaa4eb82c8bf0846571a6be741fe76d97150333f65203",
             "92b8a1d798a2cfbd3cc962573b187def0552cd27c6cdb6f860cb67e7f18ee962"
            ."c59b039d079d70168318e5e7cf6e2fee49af9e9bc46be9d7f6ceb3509ad7d79e"
            ."9298209841902cf0bde890eacfc98411c4ff8187bb064768d8638e51ce7a"]
        ];
    }
}
