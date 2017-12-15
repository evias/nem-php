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
namespace NEM\Tests\SDK\Compliance;

use NEM\Models\Amount;

class AmountDTOTest
    extends NISComplianceTestCase
{
    /**
     * Test *NIS Compliance* of class \NEM\Models\Amount.
     *
     * Test basic DTO creation containing amounts.
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @return void
     */
    public function testNISAmountDTOStructure()
    {
        // empty amount should give 0 results
        $amount = new Amount([]);
        $amountNIS = $amount->toDTO();
        $amountOnly = $amount->toDTO("amount");

        // test Amounts
        $this->assertArrayHasKey("amount", $amountNIS);
        $this->assertTrue(is_integer($amountOnly));
        $this->assertEmpty($amountOnly);

        $testInteger = 123456;
        $testFloat   = 123456.789012;
        $testMicroFloat = 123456789012;

        $integer = new Amount(["amount" => $testInteger]);
        $float   = new Amount(["amount" => $testFloat]);

        $this->assertEquals($testInteger, $integer->toMicro());
        $this->assertEquals($testFloat, $float->toUnit());

        // make sure toMicro() does not affect inner content
        $this->assertEquals($testMicroFloat, $float->toMicro());
        $this->assertEquals($testFloat, $float->toUnit());

        $testInteger2 = 123456;
        $testFloat2   = 123456.78;
        $testMicroFloat2 = 12345678;

        $integer2 = new Amount(["amount" => $testInteger2]);
        $integer2->setDivisibility(2);

        $float2 = new Amount(["amount" => $testFloat2]);
        $float2->setDivisibility(2);

        $this->assertEquals($testInteger2, $integer2->toMicro());
        $this->assertEquals($testFloat2, $float2->toUnit());

        // make sure toMicro() does not affect inner content
        $this->assertEquals($testMicroFloat2, $float2->toMicro());
        $this->assertEquals($testFloat2, $float2->toUnit());
    }

    /**
     * Data provider for `testNISAmountDTOContent` Unit Test.
     *
     * Each row should contain 2 fields in following *strict* order:
     *
     * - amount:               Micro XEM amounts to be represented in a DTO.
     * - divisibility:         The number of decimals for the amount.
     * - expectedAmount:       Expected amount returned by the Amount model.
     *
     * @return array
     */
    public function contentVectorsProvider()
    {
        return [
            [12345678, 6,                  12345678],
            [-10, 6,                       0],
            [null, null,                   0],
            [false, false,                 0],
            [878273342850120, 6,           878273342850120],
            // integer type limit
            [9223372036854775807, 6,       9223372036854775807],
            [9223372036854775808, 6,       0], // PHP_INT_MAX overflow
            [-(-150), 0,                   150],
            [true, false,                  1],
            [0.00000013, 6,                0],
            [0.000001, 6,                  1],
            [1000000, 0,                   1000000],
            ["10", 1,                      10],
            ["10", 2,                      10],
            ["10.10", 2,                   1010],
            ["10.10", 4,                   101000],
            ["10.10000000", 6,             10100000],
            ["10000000", 6,                10000000],
            [[0x10], 6,                    16],
            [[0x10, 0x20], 6,              16],
            [[null], 6,                    0],
        ];
    }


    /**
     * Test content initialization for Amount DTO.
     *
     * @depends testNISAmountDTOStructure
     * @dataProvider contentVectorsProvider
     *
     * @param       integer         $amount
     * @param       integer         $expectedAmount
     * @return void
     */
    public function testNISAmountDTOContent($amount, $divisibility, $expectedAmount)
    {
        $amountA = new Amount(["amount" => $amount]);
        $amountA->setDivisibility($divisibility);

        $this->assertEquals($expectedAmount, $amountA->toMicro());
    }
}
