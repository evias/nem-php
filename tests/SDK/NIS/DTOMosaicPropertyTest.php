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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Mosaic;
use NEM\Models\MosaicProperty;

class DTOMosaicPropertyTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicProperty class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $property = new MosaicProperty(["name" => "divisibility", "value" => 0]);
        $propertyNIS = $property->toDTO();

        // test types
        $this->assertArrayHasKey("name", $propertyNIS);
        $this->assertArrayHasKey("value", $propertyNIS);

        // test content
        $this->assertEquals("divisibility", $propertyNIS["name"]);
        $this->assertEquals("0", $propertyNIS["value"]);
    }

    /**
     * Data provider for the testNisSpecificBooleanVectors() unit test
     * 
     * @depends testDTOStructure
     * @return array
     */
    public function nisSpecificBooleanVectorsProvider()
    {
        return [
            [1,         "true"],
            [0,         "false"],
            [null,      "false"],
            [true,      "true"],
            [false,     "false"],
            ["true",    "true"],
            ["false",   "false"],
        ];
    }

    /**
     * Unit test for *supplyMutable and transferable return types*.
     * 
     * This return type is specific to NIS because boolean value should
     * be represented as *string values* ("true" or "false").
     * 
     * @dataProvider nisSpecificBooleanVectorsProvider
     * @return void
     */
    public function testNisSpecificBooleanVectors($value, $expectValue)
    {
        $supplyMutable = new MosaicProperty(["name" => "supplyMutable", "value" => $value]);
        $transferable  = new MosaicProperty(["name" => "transferable", "value" => $value]);

        $supplyMutableNIS = $supplyMutable->toDTO();
        $transferableNIS  = $transferable->toDTO();

        $this->assertEquals($expectValue, $supplyMutableNIS["value"]);
        $this->assertEquals($expectValue, $transferableNIS["value"]);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicProperty class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testNisSpecificBooleanVectors
     * @return void
     */
    public function testDTOContentVectors($name, $value, $expectName, $expectValue)
    {
        $property = new MosaicProperty(["name" => $name, "value" => $value]);
        $propertyNIS = $property->toDTO();

        // test content
        $this->assertEquals($expectName, $propertyNIS["name"]);
        $this->assertEquals($expectValue, $propertyNIS["value"]);
    }

    /**
     * Data provider for the testDTOContentVectors() unit test
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        return [
            ["transferable", null,        "transferable", "false",],
            ["supplyMutable", null,       "supplyMutable", "false",],
            ["divisibility", null,        "divisibility", 0,],
            ["initialSupply", null,       "initialSupply", 0],
            ["transferable", true,        "transferable", "true",],
            ["supplyMutable", true,       "supplyMutable", "true",],
            ["divisibility", 0,           "divisibility", 0,],
            ["initialSupply", 0,          "initialSupply", 0],
            ["transferable", -1,          "transferable", "true",],
            ["supplyMutable", -1,         "supplyMutable", "true",],
            ["divisibility", -1,          "divisibility", 0,],
            ["initialSupply", -1,         "initialSupply", 0],
        ];
    }
}
