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

use NEM\Models\Mosaic;

class DTOMosaicTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Message.
     *
     * Test basic DTO creation containing messages.
     *
     * @see https://nemproject.github.io/#transaction-data-with-decoded-messages
     * @return void
     */
    public function testEmptyParametersReturnEmptyFQN()
    {
        // empty namespaceId *or* name should return empty FQN
        $emptyNS  = new Mosaic(["name" => "nem-php"]);
        $emptyMos = new Mosaic(["namespaceId" => "evias.sdk"]);

        $actualFQN_1 = $emptyNS->getFQN();
        $actualFQN_2 = $emptyMos->getFQN();

        $this->assertEmpty($assertFQN_1);
        $this->assertEmpty($assertFQN_2);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for Mosaic class*.
     * 
     * @return void
     */
    public function testMosaicDTOStructure()
    {
        $mosaic = new Mosaic(["namespaceId" => "evias.sdk", "name" => "nem-php"]);
        $actualDTO = $mosaic->toDTO();

        $expectNS  = "evias.sdk";
        $expectMos = "nem-php";

        $this->arrayHasKey("namespaceId", $actualDTO);
        $this->arrayHasKey("name", $actualDTO);
        $this->assertEquals($expectNS, $actualDTO["namespaceId"]);
        $this->assertEquals($expectMos, $actualDTO["name"]);
    }
}
