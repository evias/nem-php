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
     * This test makes sure that KeyPair signatures are created
     * with Keccak-512 when no different algorithm is specified.
     *
     * @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
     * @return void
     */
    public function testBasicKeyPairSignIsKeccak512()
    {
        // @link https://github.com/trezor/trezor-crypto/blob/master/test_check.c#L3256
        $binary  = hex2bin("8ce03cd60514233b86789729102ea09e867fc6d964dea8c2018ef7d0a2e0e24bf7e348e917116690b9");
        $keypair = KeyPair::create("abf4cf55a2b3f742d7543d9cc17f50447b969e6e06f5ea9195d428ab12b7318d");
        $signed  = $keypair->sign($binary); // leave second arg empty to use default algorithm (keccak-512)

        // check binary representation
        $this->assertEquals(64, strlen($signed->getBinary()));

        // check hexadecimal representation
        $expectHex = "d9cec0cc0e3465fab229f8e1d6db68ab9cc99a18cb0435f70deb6100948576cd5c0aa1feb550bdd8693ef81eb10a556a622db1f9301986827b96716a7134230c";
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($expectHex, $signed->getHex());
    }

    /**
     * Unit test for *NEM Test Vectors* KeyPair signatures.
     * 
     * This uses data that can be found at [NEM Test Vectors](https://raw.githubusercontent.com/NemProject/nem-test-vectors)
     * 
     * @link https://raw.githubusercontent.com/NemProject/nem-test-vectors
     * @depends testBasicKeyPairSignIsKeccak512
     * @dataProvider nemTestVectorsProvider
     * @return void
     */
    public function testNEMVectorsKeyPairSignatures($privateKey, $data, $signatureHex)
    {
        $keypair = KeyPair::create($privateKey);
        $binary  = hex2bin($data);
        $signed  = $keypair->sign($binary);

        // check binary representation
        $this->assertEquals(64, $signed->getInternalSize());

        // check hexadecimal representation
        $this->assertEquals(128, strlen($signed->getHex()));
        $this->assertEquals($signatureHex, $signed->getHex());
    }

    /**
     * Data provider with data from NEM Test Vectors.
     * 
     * Each row of the returned array contains an array with 
     * 3 columns in following strict order:
     * 
     * - Private Key in hexadecimal format
     * - Data in hexadecimal format
     * - Signature in hexadecimal format
     * 
     * return array 
     */
    public function nemTestVectorsProvider()
    {
        return [
            ["6aa6dad25d3acb3385d5643293133936cdddd7f7e11818771db1ff2f9d3f9215",
             "e4a92208a6fc52282b620699191ee6fb9cf04daf48b48fd542c5e43daa9897763a199aaa4b6f10546109f47ac3564fade0",
             "98bca58b075d1748f1c3a7ae18f9341bc18e90d1beb8499e8a654c65d8a0b4fbd2e084661088d1e5069187a2811996ae31f59463668ef0f8cb0ac46a726e7902"],
            ["8e32bc030a4c53de782ec75ba7d5e25e64a2a072a56e5170b77a4924ef3c32a9",
             "13ed795344c4448a3b256f23665336645a853c5c44dbff6db1b9224b5303b6447fbf8240a2249c55",
             "ef257d6e73706bb04878875c58aa385385bf439f7040ea8297f7798a0ea30c1c5eff5ddc05443f801849c68e98111ae65d088e726d1d9b7eeca2eb93b677860c"],
            ["c83ce30fcb5b81a51ba58ff827ccbc0142d61c13e2ed39e78e876605da16d8d7",
             "a2704638434e9f7340f22d08019c4c8e3dbee0df8dd4454a1d70844de11694f4c8ca67fdcb08fed0cec9abb2112b5e5f89",
             "0c684e71b35fed4d92b222fc60561db34e0d8afe44bdd958aaf4ee965911bef5991236f3e1bced59fc44030693bcac37f34d29e5ae946669dc326e706e81b804"],
            ["09ddd185a0a2b62760ca35567b83ead845acef97ad2bca6bfea381ff8e806c1d",
             "55dd33145cb8cbfbd21264c2ff786066b21a2db7fe47f6e1410d20cc9e50fb9d6462188e4602a041",
             "66e519a2ba8f3eb16b63d69bf9d3be564dac0ca516d4ebec9bde5be34caf4fdc20a0a22ea45a01a00bc5f29016e35e128a7d73e4e7b396762c87f5c8f0695209"],
            ["030c0b139705180a44d5a2406fbdb92afb16f93c2d168dd39d2f5f43516295ec",
             "60a3512e600236957f271a5b47ec35ab8991a1f0a0b76564d0549b77357779d0b1502365dd83de78191265",
             "23fafe9934bd84b70ad9e4f453b756bc858627524e55be47c53ff41ae7cc3ed2520282781cf0281c0c00e81ddfad981abdaf0ccdc98a9cea2edf9d262a7c0f0d"],
            ["2900d96d21b7ace794558a432c1f28b806280ad93c31482e5b40a117e1219f21",
             "3b68a2de1c8808566eb969c032f5a10df91ab7ae2d3d57c5a6a3376572876e0d94a759eab785d943ad1da35689",
             "700f9e02632a6c751ca0a06156a55a3c8a1a6d0bc405b3e65f74a0e46fb5cdbbcf20865e0368f939e7482b5032d39f613d1e8b9c69f8e969bbcc2708d46a2207"],
            ["d7da4e94fb9925b6ab01370f26d45d1649a47b8601ec7cb8ab8e410fe8205d21",
             "2dee16a97075fdf715819fa1e88a49f96c7345f414330ab6bd5cb12a81412969920f0483961da4ea6adcbc01fdf5",
             "52199acf799d9defda5d3fcce7c696e5af7ed98c94b46b00dba5ea157f7d4ce78be29e0cea6de2b0e8299bf0110059f90683f12f67fd7733b40ae7ad5a945f0f"],
            ["3c3408d03b2c01fa5547375f73ca9734858da32bc6cfd7c62c184eeb49652f69",
             "51d01978437b34277ef148297e09197f5f03d434e57e1dd61b975bb2ed290b87b3dba4425b0b705be211426236",
             "5f97dd102b02a844ef9d3b4d8bbd3adeed42aba5ea84c02ca46cdd47ab5046b1ebf90f5a58860e8f14cae4dc84ec90bcc52bc1b1c213c998ddb73d5d6d3eb105"],
            ["725a04a1ba670b678b0c9162b53b550e9c0e0768dfea503d33951c1bcd22e6a1",
             "c610347d5fd368c7be675b8b225cc8c8f92444797a0dfad09e222d8d503ebe561e4dbefa5d5e1f6f71229f9955a2692527f6e8",
             "b5d842ac3019faeee1608ad23621fc1d24de87fcc32487e19cfc29d897cb8c8ebccc09fa0e28679732434a490b666cb8ccdb95d880da38ad692f53487664ec07"],
            ["c0fa590d5c7d85865f034e6ca954335104c362409b5bed31dfaceeac905e2bdf",
             "1a3d7ed3e11d9ee3ce2bf3811ba3b8c8f09d921048637998782d942fbd2bcf5bef60546fb6d4d9d06fe7e4",
             "f0e34eba9189be832f2a7284c29a8a9abab172aa0bd3ccf2f7bb16c3192beb5698a80715670c8f55f509f0e69d5cdc8ec01e3b8479a9d2ffe08b956b362dbb0d"],
        ];
    }
}
