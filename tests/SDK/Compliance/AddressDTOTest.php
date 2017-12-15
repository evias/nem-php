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
namespace NEM\Tests\SDK\Compliance;

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
}
