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
namespace NEM\Tests\SDK;

use GuzzleHttp\Exception\ConnectException;
use NEM\Tests\TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Mutators\ModelMutator;
use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\ModelCollection;
use NEM\Contracts\DataTransferObject;
use NEM\Models\Model;
use NEM\Models\Account;
use NEM\Models\Transaction;
use NEM\Models\Address;

class ModelCollectionTest
    extends TestCase
{
    /**
     * Test creation of empty collection with the SDK's collection abstraction
     * layer class `ModelCollection`.
     *
     * @return void
     */
    public function testSDKEmptyCollectionCreation()
    {
        $collection = $this->sdk->collect("model", []);

        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
    }

    /**
     * Test basic methods of the SDK's collection abstraction layer class `ModelCollection`.
     *
     * @return void
     */
    public function testSDKCollectionMutatorBasics()
    {
        $model1 = new Model(["firstField" => null, "secondField" => "withValue"]);
        $model2 = new Model(["thirdField" => "alsoValued","fourthField" => null]);

        $collection = $this->sdk->collect("model", [$model1, $model2]);

        $this->assertFalse($collection->isEmpty());
        $this->assertEquals(2, $collection->count());
    }

    /**
     * Test multi type collections of the SDK's abstraction layer class `ModelCollection`.
     *
     * @return void
     */
    public function testSDKMultiTypeCollectionMutator()
    {
        $model1 = new Model(["firstField" => null, "secondField" => "withValue"]);
        $model2 = new Model(["thirdField" => "alsoValued","fourthField" => null]);
        $model3 = new Account(["address" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"]);

        $collection = new ModelCollection;
        $collection->push($model1);
        $collection->push($model2);
        $collection->push($model3);

        $this->assertFalse($collection->isEmpty());
        $this->assertEquals(3, $collection->count());
        $this->assertTrue($collection->last() instanceof Account);
    }

    /**
     * Test collections to Data Transfer Object feature of the SDK's abstraction 
     * layer class `ModelCollection`.
     *
     * @return void
     */
    public function testSDKCollectionDTOBuilder()
    {
        $model1 = new Model(["firstField" => null, "secondField" => "withValue"]);
        $model2 = new Model(["thirdField" => "alsoValued","fourthField" => null]);
        $model3 = new Account(["address" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"]);

        $collection = new ModelCollection;
        $collection->push($model1);
        $collection->push($model2);
        $collection->push($model3);

        $dtos = $collection->toDTO();

        $this->assertEquals(3, count($dtos));

        // test order of DTOs
        $this->assertArrayHasKey("firstField", $dtos[0]);
        $this->assertArrayHasKey("secondField", $dtos[0]);

        $this->assertArrayHasKey("thirdField", $dtos[1]);
        $this->assertArrayHasKey("fourthField", $dtos[1]);

        $this->assertArrayHasKey("account", $dtos[2]);
        $this->assertArrayHasKey("meta", $dtos[2]);
    }

}
