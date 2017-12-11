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

class ModelRelationshipTest
    extends TestCase
{
    /**
     * The NEM SDK instance
     *
     * @var \NEM\SDK
     */
    protected $sdk;

    /**
     * The setUp method of this test case will
     * instantiate the API using the bigalice2.nem.ninja
     * NIS testnet node.
     *
     * @see :Execution of this Test Case requires an Internet Connection
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->sdk = new SDK([
            "use_ssl"  => false,
            "protocol" => "http",
            "host" => "bigalice2.nem.ninja", // testing uses online NIS
            "port" => 7890,
            "endpoint" => "/",
        ]);
    }

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
        $model->setAttributes(["relationField" => ["some" => "data"]]);
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
        $this->assertEquals($testAddress, $account->address()->toClean());

        // test simple DTO content
        $this->assertArrayHasKey("account", $accountDTO);
        $this->assertArrayHasKey("meta", $accountDTO);
    }
}
