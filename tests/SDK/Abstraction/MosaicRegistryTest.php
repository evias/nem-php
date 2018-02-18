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
    public function testDynamicClassSearchError()
    {
        $invalidParam = Registry::getDefinition(null);
    }

    /**
     * Unit test for *valid mosaic parameter* for registry searches.
     *
     * @return void
     */
    public function testMorphClassXem()
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
        $this->assertEquals("false", $actualSupplyMutable);
        $this->assertEquals("true", $actualTransferable);
    }

    /**
     * Unit test for *morphClass multiple inputs*.
     *
     * @dataProvider morphClassVectorsProvider
     * @return void
     */
    public function testMorphClassVectors($fqn, $expectObject)
    {
        $definition = Registry::getDefinition($fqn);

        // test class inheritance
        $this->assertTrue($definition instanceof \NEM\Models\MosaicDefinition);

        $definitionNIS = $definition->toDTO();

        // test class inheritance and instance type
        $this->assertEquals(get_class($expectObject), get_class($definition));
    }

    /**
     * Data provider for the testMorphClassVectors() unit test
     * 
     * @return array
     */
    public function morphClassVectorsProvider()
    {
        return [
            ["dim:coin",                    new \NEM\Mosaics\Dim\Coin],
            ["dim:token",                   new \NEM\Mosaics\Dim\Token],
            ["dim:eur",                     new \NEM\Mosaics\Dim\Eur],
            ["nemether:nemether",           new \NEM\Mosaics\Nemether\Nemether],
            ["pacnem:cheese",               new \NEM\Mosaics\Pacnem\Cheese],
            ["pacnem:hall-of-famer",        new \NEM\Mosaics\Pacnem\HallOfFamer],
            ["pacnem:heart",                new \NEM\Mosaics\Pacnem\Heart],
            ["pacnem:personal-token",       new \NEM\Mosaics\Pacnem\PersonalToken],
        ];
    }
}
