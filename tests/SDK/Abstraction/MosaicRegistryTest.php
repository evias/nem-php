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
namespace NEM\Tests\SDK\Abstraction;

use NEM\Tests\TestCase;
use NEM\Mosaics\Registry;
use NEM\Models\MosaicDefinition;

class MosaicRegistryTest
    extends TestCase
{
    /**
     * Unit test for *invalid mosaic parameter* for registry searches.
     * 
     * @expectedException   \InvalidArgumentException
     * @return void
     */
    public function testMorphClassSearchTypeErrorThrowsError()
    {
        $invalidParam = Registry::getDefinition(null);
    }

    /**
     * Unit test for *unknown mosaic* for registry searches.
     * 
     * @return void
     */
    public function testMorphClassSearchUnknownMosaicReturnsFalse()
    {
        $invalidFQN = "evias.sdk.preconfigured:nem-php";
        $definition = Registry::getDefinition($invalidFQN);

        $this->assertFalse($definition);
    }

    /**
     * Unit test for *morphClass multiple inputs*.
     *
     * @dataProvider morphClassVectorsProvider
     * @return void
     */
    public function testMorphClassVectorsClassInheritance($fqn, $expectClass)
    {
        $expectClass = "\\" . $expectClass;
        $actualClass = Registry::morphClass($fqn);

        // test class inheritance and instance type
        $this->assertEquals($expectClass, $actualClass);
    }

    /**
     * Data provider for the testMorphClassVectorsClassInheritance() unit test
     * 
     * @return array
     */
    public function morphClassVectorsProvider()
    {
        return [
            ["dim:coin",                    \NEM\Mosaics\Dim\Coin::class],
            ["dim:token",                   \NEM\Mosaics\Dim\Token::class],
            ["dim:eur",                     \NEM\Mosaics\Dim\Eur::class],
            ["nemether:nemether",           \NEM\Mosaics\Nemether\Nemether::class],
            ["pacnem:cheese",               \NEM\Mosaics\Pacnem\Cheese::class],
            ["pacnem:hall-of-famer",        \NEM\Mosaics\Pacnem\HallOfFamer::class],
            ["pacnem:heart",                \NEM\Mosaics\Pacnem\Heart::class],
            ["pacnem:personal-token",       \NEM\Mosaics\Pacnem\PersonalToken::class],
        ];
    }

    /**
     * Unit test for *valid mosaic parameter* for registry searches.
     *
     * @depends testMorphClassVectorsClassInheritance
     * @return void
     */
    public function testGetDefinitionXemDTOStructure()
    {
        $definition = Registry::getDefinition("nem:xem");
        $definitionNIS = $definition->toDTO();

        // do not include class to test class_exists() behaviour
        // inside morphClass
        $this->assertTrue($definition instanceof \NEM\Mosaics\Nem\Xem);

        // test XEM preconfigured mosaic content
        $actualDivisibility  = $definition->getProperty("divisibility");
        $actualInitialSupply = $definition->getProperty("initialSupply");
        $actualSupplyMutable = $definition->getProperty("supplyMutable");
        $actualTransferable  = $definition->getProperty("transferable");

        $this->assertEquals(6, $actualDivisibility);
        $this->assertEquals(8999999999, $actualInitialSupply);
        $this->assertEquals(false, $actualSupplyMutable);
        $this->assertEquals(true, $actualTransferable);
    }

    /**
     * Unit test for *valid mosaic parameter* for registry searches.
     *
     * @depends testGetDefinitionXemDTOStructure
     * @dataProvider getDefinitionVectorsProvider
     * @return void
     */
    public function testGetDefinitionVectorsDTOStructure($fqn, $expectClass)
    {
        $definition = Registry::getDefinition($fqn);

        // test class inheritance
        $this->assertTrue($definition instanceof MosaicDefinition);

        // test class specialization
        $expectClass = "\\" . $expectClass;
        $actualClass = "\\" . get_class($definition);
        $this->assertEquals($expectClass, $actualClass);

        // assert instance content
        $definitionNIS = $definition->toDTO();
        $this->assertArrayHasKey("id", $definitionNIS);
        $this->assertArrayHasKey("creator", $definitionNIS);
        $this->assertArrayHasKey("properties", $definitionNIS);
        $this->assertArrayHasKey("levy", $definitionNIS);
        $this->assertArrayHasKey("description", $definitionNIS);
    }

    /**
     * Data provider for the testMorphClassVectors() unit test
     * 
     * @return array
     */
    public function getDefinitionVectorsProvider()
    {
        return [
            ["dim:coin",                    \NEM\Mosaics\Dim\Coin::class],
            ["dim:token",                   \NEM\Mosaics\Dim\Token::class],
            ["dim:eur",                     \NEM\Mosaics\Dim\Eur::class],
            ["nemether:nemether",           \NEM\Mosaics\Nemether\Nemether::class],
            ["pacnem:cheese",               \NEM\Mosaics\Pacnem\Cheese::class],
            ["pacnem:hall-of-famer",        \NEM\Mosaics\Pacnem\HallOfFamer::class],
            ["pacnem:heart",                \NEM\Mosaics\Pacnem\Heart::class],
            ["pacnem:personal-token",       \NEM\Mosaics\Pacnem\PersonalToken::class],
        ];
    }
}
