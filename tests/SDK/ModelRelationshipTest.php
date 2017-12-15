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
use NEM\Tests\NIS\NISComplianceTestCase;
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
use NEM\Models\MultisigInfo;

class ModelRelationshipTest
    extends NISComplianceTestCase
{
    /**
     * Test basic relationship creation with the SDK Models.
     *
     * @expectedException \BadMethodCallException
     * @return void
     */
    public function testSDKModelRelationshipCrafting()
    {
        $model = new Model();
        $model->setFields(["firstField", "secondField", "relationField"]);
        $model->setRelations(["relationField"]);

        
        // try to call the relation will throw an error because
        // the relation method relationField() is not implemented.
        $model->resolveRelationship("relationField", []);
    }

    /**
     * Test relationship crafting with basic existing subordinate DTO.
     *
     * @return void
     */
    public function testSDKModelRelationshipCraftingExistingSubDTO()
    {
        $testAddress = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";
        $account = new Account(["address" => $testAddress]);

        $accountDTO = $account->toDTO();

        // test related object
        $this->assertTrue($account->address() instanceof Address);
        $this->assertTrue($account->multisigInfo() instanceof MultisigInfo);
        $this->assertEquals($testAddress, $account->address()->toClean());

        // test simple DTO content
        $this->assertArrayHasKey("account", $accountDTO);
        $this->assertArrayHasKey("meta", $accountDTO);
    }

    /**
     * Test relationship crafting for Model Collections using subordinate DTOs.
     *
     * This test will check initialization of objects with *field aliases*, with
     * DTOs (from NIS) and using the `setAttribute` method directly.
     *
     * This makes sure that *whichever method of initialization is used*, the produced
     * objects and DTOs stay the same.
     *
     * @return void
     */
    public function testSDKModelRelationshipCraftingCollections()
    {
        $account = new Account($this->mockAccounts(1));

        // test simple relationship
        $this->assertTrue($account->address() instanceof Address);
        $this->assertNotEmpty($account->address()->toClean());

        $cosigs = $this->mockAccounts(5, false); // meta=false

        // test initialization with sub-DTO-collection through exact path
        $new = new Account(["meta" => ["cosignatories" => $cosigs]]);

        // strict dependency and deep dependency
        $this->assertTrue($new->cosignatories() instanceof ModelCollection);
        $this->assertFalse($new->cosignatories()->isEmpty());
        $this->assertEquals(5, $new->cosignatories()->count());
        $this->assertTrue($new->cosignatories()->get(0) instanceof Account);

        // test initialization with sub-DTO-collection through alias
        $new = new Account(["cosignatories" => $cosigs]);
        $this->assertTrue($new->cosignatories() instanceof ModelCollection);
        $this->assertFalse($new->cosignatories()->isEmpty());
        $this->assertEquals(5, $new->cosignatories()->count());
        $this->assertTrue($new->cosignatories()->get(0) instanceof Account);

        // test collection mutator (relationship method)
        $collection = $account->cosignatories($cosigs);

        $this->assertTrue($collection instanceof ModelCollection);
        $this->assertFalse($collection->isEmpty());
        $this->assertEquals(5, $collection->count());
        $this->assertTrue($collection->get(0) instanceof Account);

        // test attributes setter for relations
        $account->setAttribute("cosignatories", $cosigs);

        $this->assertFalse($account->cosignatories()->isEmpty());
        $this->assertEquals(5, $account->cosignatories()->count());
    }
}
