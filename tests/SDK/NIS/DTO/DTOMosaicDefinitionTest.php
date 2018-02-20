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
namespace NEM\Tests\SDK\NIS\DTO;

use NEM\Tests\SDK\NIS\NISComplianceTestCase;
use NEM\Models\Mosaic;
use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicProperties;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicLevy;

 class DTOMosaicDefinitionTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicDefinition class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $definition = new MosaicDefinition();
        $definitionNIS = $definition->toDTO();

        $this->assertArrayHasKey("creator", $definitionNIS);
        $this->assertArrayHasKey("id", $definitionNIS);
        $this->assertArrayHasKey("description", $definitionNIS);
        $this->assertArrayHasKey("properties", $definitionNIS);
        $this->assertArrayHasKey("levy", $definitionNIS);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicDefinition class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors($creator, $mosaicFQN, $description, $properties, $levy)
    {
        $publicKey = $creator;
        $mosaic    = new Mosaic($mosaicFQN);
        $definition = new MosaicDefinition([
            "creator" => $publicKey,
            "id" => $mosaic->toDTO(),
            "properties" => $properties->toDTO(),
            "levy"  => $levy->toDTO(),
            "description" => $description,
        ]);

        $definitionNIS = $definition->toDTO();
        $propertiesNIS = $properties->toDTO();
        $levyNIS = $levy->toDTO();

        // expected results
        $expectCreator = $creator;

        // Mosaic model tested in different test *before*.
        $expectProps = $propertiesNIS;
        $expectDesc = $description;
        $expectLevy = $levyNIS;

        $this->assertEquals($expectCreator, $definitionNIS["creator"]);
        $this->assertEquals($expectDesc, $definitionNIS["description"]);
        $this->assertEquals(json_encode($mosaic->toDTO()), json_encode($definitionNIS["id"]));
        $this->assertEquals(json_encode($expectLevy), json_encode($definitionNIS["levy"]));
        $this->assertEquals(json_encode($expectProps), json_encode($definitionNIS["properties"]));
    }

    /**
     * Data provider for the testDTOContentVectors() unit test
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        return [
            [
                "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04",
                "evias.sdk:nem-php",
                "https://github.com/evias/nem-php",
                new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 6]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => true]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]),
                new MosaicLevy()
            ],
            [
                "a1df5306355766bd2f9a64efdc089eb294be265987b3359093ae474c051d7d5a",
                "dim:coin",
                "DIM COIN",
                new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 6]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 9000000000]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => false]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]),
                new MosaicLevy([
                    "type" => MosaicLevy::TYPE_PERCENTILE,
                    "fee"  => 10,
                    "recipient" => "NCGGLVO2G3CUACVI5GNX2KRBJSQCN4RDL2ZWJ4DP",
                    "mosaicId" => (new Mosaic([
                        "namespaceId" => "dim",
                        "name" => "coin"
                    ]))->toDTO(),
                ])
            ],
            [
                "a1df5306355766bd2f9a64efdc089eb294be265987b3359093ae474c051d7d5a",
                "dim:token",
                "DIM TOKEN",
                new MosaicProperties([
                    new MosaicProperty(["name" => "divisibility", "value" => 6]),
                    new MosaicProperty(["name" => "initialSupply", "value" => 10000000]),
                    new MosaicProperty(["name" => "supplyMutable", "value" => false]),
                    new MosaicProperty(["name" => "transferable", "value" => true]),
                ]),
                new MosaicLevy()
            ],
        ];
    }
}
