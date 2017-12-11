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

class ModelAbstractionTest
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
     * Test basic methods of the SDK's model abstraction layer class `Model`.
     *
     * This unit test will verify that attributes are automatically read when
     * a property is read which corresponds to one of the Model's fillable
     * attributes list
     *
     * @return void
     */
    public function testSDKModelAttributesMutatorGetters()
    {
        $model = new Model([
            "firstField" => null,
            "secondField" => "withValue"]);

        $attributes = $model->getAttributes();
        $dataTransfer = $model->toDTO();

        // test Getters
        $this->assertEquals(2, count($attributes));
        $this->assertTrue(is_array($dataTransfer));
        $this->assertTrue(is_array($attributes));

        $this->assertNull($attributes["firstField"]);
        $this->assertEquals("withValue", $attributes["secondField"]);

        // test Fields mutator
        $this->assertNull($model->firstField);
        $this->assertEquals("withValue", $model->secondField);
    }

    /**
     * Test basic methods of the SDK's model abstraction layer class `Model`.
     *
     * This unit test will verify that attributes are correctly set when the
     * field name is mutated through the model class' __call() method.
     *
     * @return void
     */
    public function testSDKModelAttributesMutatorSetters()
    {
        $model = new Model([
            "firstField" => null,
            "secondField" => null]);

        $model->firstField = 1;
        $model->secondField = 2;

        $attributes = $model->getAttributes();
        $dataTransfer = $model->toDTO();

        $this->assertEquals(1, $attributes["firstField"]);
        $this->assertEquals(2, $attributes["secondField"]);
        $this->assertTrue(isset($model->firstField));
        $this->assertTrue(isset($model->secondField));

        // test unsetting attributes values
        unset($model->firstField);
        unset($model->secondField);

        $this->assertFalse(isset($model->firstField));
        $this->assertFalse(isset($model->secondField));
    }
}
