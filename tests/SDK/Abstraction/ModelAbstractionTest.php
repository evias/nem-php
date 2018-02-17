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

use GuzzleHttp\Exception\ConnectException;
use NEM\Tests\TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Model;
use NEM\Models\Transaction;
use NEM\Models\Transaction\ImportanceTransfer;

class ModelAbstractionTest
    extends TestCase
{
    public function testSDKDotNotation()
    {
        $model = new Model([
            "firstField" => [
                "nestedField" => null
            ],
            "secondField" => "withValue",
            "thirdField" => [
                "nestedAgain" => [
                    "deeperNest" => [
                        "yetDeeper" => null
                    ],
                ],
            ]
        ]);

        $attributes = $model->getAttributes();
        $dataTransfer = $model->toDTO();
        $dotAttribs = $model->getDotAttributes();

        // test Getters
        $this->assertEquals(3, count($attributes));
        $this->assertTrue(is_array($dataTransfer));
        $this->assertTrue(is_array($attributes));
        $this->assertTrue(is_array($dotAttribs));
    }

    /**
     * Test basic methods of the SDK's model abstraction layer class `Model`.
     *
     * This unit test will verify that attributes are automatically read when
     * a property is read which corresponds to one of the Model's fillable
     * attributes list
     *
     * @depends testSDKDotNotation
     * @return void
     */
    public function testSDKModelAttributesMutatorGetters()
    {
        $model = new Model([
            "firstField" => [
                "nestedField" => null
            ],
            "secondField" => "withValue",
            "thirdField" => [
                "nestedAgain" => [
                    "deeperNest" => [
                        "yetDeeper" => null
                    ],
                ],
            ]
        ]);

        $attributes = $model->getAttributes();
        $dataTransfer = $model->toDTO();
        $dotAttribs = $model->getDotAttributes();

        // test returned structure
        $this->assertArrayHasKey("firstField", $attributes);
        $this->assertArrayHasKey("secondField", $attributes);
        $this->assertArrayHasKey("thirdField", $attributes);

        // test content for validation
        $this->assertTrue(is_array($attributes["firstField"]));
        $this->assertNotEmpty($attributes["firstField"]);
        $this->assertNull($attributes["firstField"]["nestedField"]);
        $this->assertEquals("withValue", $attributes["secondField"]);

        // test Fields mutator
        $this->assertTrue(is_array($model->firstField));
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

    /**
     * Test model attribute `appends` extension.
     *
     * @return void
     */
    public function testSDKModelAppends()
    {
        $tx = new Transaction();
        $itx = new ImportanceTransfer();

        $this->assertNotEquals($tx->getFields(), $itx->getFields());
        $this->assertNotEmpty($itx->getAppends());
    }

    /**
     * Unit test for *prevention of fields order change*.
     * 
     * @return void
     */
    public function testSDKModelFieldsOrderChangePrevention()
    {
        $model = new Model([
            "attribute_one"   => "value_pos1",
            "attribute_two"   => "value_pos2",
            "attribute_three" => "value_pos3"
        ]);

        $attribs = $model->toDTO();
        $fields  = array_keys($attribs);

        $this->assertEquals(3, count($fields));
        $this->assertEquals("attribute_one", $fields[0]);
        $this->assertEquals("attribute_two", $fields[1]);
        $this->assertEquals("attribute_three", $fields[2]);
    }
}
