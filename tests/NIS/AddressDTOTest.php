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
namespace NEM\Tests\NIS;

use GuzzleHttp\Exception\ConnectException;
use NEM\Tests\TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Address;

class AddressDTOTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Address.
     *
     * This class should allow formatting Address in the NIS compliant
     * format (base32 encoding).
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

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("address", $accountNIS);
    }
}
