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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Address;

class AddressDTOTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Address.
     *
     * Test basic DTO creation containing addresses.
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @return void
     */
    public function testNISAddressDTOStructure()
    {
        // empty account should still always produce NIS compliant
        // objects when converted to Data Transfer Object.
        $address = new Address([]);
        $accountNIS = $address->toDTO();
        $addressOnly = $address->toDTO("address");

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("address", $accountNIS);
        $this->assertTrue(is_string($addressOnly));
        $this->assertEmpty($addressOnly);

        // test filled structure
        $testAddress = "TC72N5Y5WFA7KMI2VG3B7T67EQXGB4ATDXCTJGKE";
        $testPublic  = "bc5b32dfe973b89d4f5c246042c9021a1b8bf5d402f747114ed436eb9c914e6a";
        $testPrivate = "don't think I would even put a testnet Private Key ;)";

        $keyPair = new Address([
            "address" => $testAddress,
            "publicKey" => $testPublic,
            "privateKey" => $testPrivate,
        ]);

        $keyPairDTO = $keyPair->toDTO();

        $this->assertArrayHasKey("address", $keyPairDTO);
        $this->assertArrayHasKey("publicKey", $keyPairDTO);
        $this->assertArrayHasKey("privateKey", $keyPairDTO);

        $this->assertEquals($testAddress, $keyPair->address);
        $this->assertEquals($testAddress, $keyPairDTO["address"]);

        $this->assertEquals($testPublic, $keyPair->publicKey);
        $this->assertEquals($testPublic, $keyPairDTO["publicKey"]);

        $this->assertEquals($testPrivate, $keyPair->privateKey);
        $this->assertEquals($testPrivate, $keyPairDTO["privateKey"]);
    }

    /**
     * Test *NIS Compliance* of class \NEM\Models\Address.
     *
     * Test addresses formatting and content creation using
     * the \NEM\Models\Address class.
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     *
     * @depends testNISAddressDTOStructure
     * @return void
     */
    public function testNISAddressDTOFormatting()
    {
        // empty account should still always produce NIS compliant
        // objects when converted to Data Transfer Object.
        $address = new Address(["address"]);
        $accountNIS = $address->toDTO();
        $addressOnly = $address->toDTO("address");

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("address", $accountNIS);
        $this->assertTrue(is_string($addressOnly));
        $this->assertEmpty($addressOnly);

        $testClean  = "TC72N5Y5WFA7KMI2VG3B7T67EQXGB4ATDXCTJGKE";
        $testPretty = "TC72N5-Y5WFA7-KMI2VG-3B7T67-EQXGB4-ATDXCT-JGKE";

        $clean  = new Address(["address" => $testClean]);
        $pretty = new Address(["address" => $testPretty]);

        // Address' `address` attribute should always return clean
        // version of the input address.
        $this->assertEquals($testClean, $clean->address);
        $this->assertEquals($testClean, $pretty->address);

        // test toClean() and toPretty() formatting
        $this->assertEquals($clean->toClean(), $pretty->toClean());
        $this->assertEquals($pretty->toPretty(), $clean->toPretty());
    }

    /**
     * Data provider for `testNISAddressDTOLoadFromPublicKey` Unit Test.
     *
     * Each row should contain 4 fields in following *strict* order:
     *
     * - publicKey:             String|Buffer|KeyPair containing a hexadecimal representation of the public key.
     * - expectedMainnet:       Base32 string representation of the address on Mainnet.
     * - expectedTestnet:       Base32 string representation of the address on Testnet.
     * - expectedMijin:         Base32 string representation of the address on Mijin.
     *
     * @return array
     */
    public function publicKeyVectorsProvider()
    {
        return [
            ["c5f54ba980fcbb657dbaaa42700539b207873e134d2375efeab5f1ab52f87844", "NDD2CT6LQLIYQ56KIXI3ENTM6EK3D44P5JFXJ4R4", "", ""],
            ["96eb2a145211b1b7ab5f0d4b14f8abc8d695c7aee31a3cfc2d4881313c68eea3", "NABHFGE5ORQD3LE4O6B7JUFN47ECOFBFASC3SCAC", "", ""],
            ["2d8425e4ca2d8926346c7a7ca39826acd881a8639e81bd68820409c6e30d142a", "NAVOZX4HDVOAR4W6K4WJHWPD3MOFU27DFHC7KZOZ", "", ""],
            ["4feed486777ed38e44c489c7c4e93a830e4c4a907fa19a174e630ef0f6ed0409", "NBZ6JK5YOCU6UPSSZ5D3G27UHAPHTY5HDQMGE6TT", "", ""],
            ["83ee32e4e145024d29bca54f71fa335a98b3e68283f1a3099c4d4ae113b53e54", "NCQW2P5DNZ5BBXQVGS367DQ4AHC3RXOEVGRCLY6V", "", ""],
            ["6d34c04f3a0e42f0c3c6f50e475ae018cfa2f56df58c481ad4300424a6270cbb", "NA5IG3XFXZHIPJ5QLKX2FBJPEZYPMBPPK2ZRC3EH", "", ""],
            ["a8fefd72a3b833dc7c7ed7d57ed86906dac22f88f1f4331873eb2da3152a3e77", "NAABHVFJDBM74XMJJ52R7QN2MTTG2ZUXPQS62QZ7", "", ""],
            ["c92f761e6d83d20068fd46fe4bd5b97f4c6ba05d23180679b718d1f3e4fb066e", "NCLK3OLMHR3F2E3KSBUIZ4K5PNWUDN37MLSJBJZP", "", ""],
            ["eaf16a4833e59370a04ccd5c63395058de34877b48c17174c71db5ed37b537ed", "ND3AHW4VTI5R5QE5V44KIGPRU5FBJ5AFUCJXOY5H", "", ""],
        ];
    }

    /**
     * Test for *generating Address from Public Key* feature.
     *
     * @depends testNISAddressDTOFormatting
     * @dataProvider publicKeyVectorsProvider
     * 
     * @param   mixed   $publicKey
     * @param   string  $expectedMainnet
     * @param   string  $expectedTestnet
     * @param   string  $expectedMijin
     * @return  void
     */
    public function testNISAddressDTOLoadFromPublicKey($publicKey, $expectedMainnet, $expectedTestnet, $expectedMijin)
    {
        $mainnet = Address::fromPublicKey($publicKey, 0x68);

        $this->assertEquals($expectedMainnet, $mainnet->toClean());
    }

    //XXX fromPublicKey Errors tests
}
