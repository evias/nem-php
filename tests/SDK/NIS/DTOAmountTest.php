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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Amount;

class DTOAmountTest
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
     * Test Amount boundaries features.
     *
     * @depends testNISAmountDTOStructure
     * @expectedException \NEM\Errors\NISAmountOverflowException
     * @return void
     */
    public function testNISAmountOverflowError()
    {
        $amount = new Amount(["amount" => Amount::MAX_AMOUNT + 1]);
    }

    /**
     * Test Negative amounts parsing.
     *
     * Negative amounts are not allowed and should be parsed as ZERO.
     *
     * @depends testNISAmountDTOStructure
     * @return void
     */
    public function testNISAmountNegativeValuesToZero()
    {
        $amount = new Amount(["amount" => -1]);

        $this->assertEquals(0, $amount->toMicro());
        $this->assertEquals(0, $amount->toUnit());
    }

    /**
     * Data provider for `testNISAmountDTOContent` Unit Test.
     *
     * Each row should contain 2 fields in following *strict* order:
     *
     * - amount:               Micro XEM amounts to be represented in a DTO.
     * - divisibility:         The number of decimals for the amount.
     * - expectedAmount:       Expected amount returned by the Amount model in MICRO XEM.
     * - expectedUnit:         Expected amount returned by the Amount model in UNIT (1 XEM = 1 mio. MICRO XEM).
     *
     * @return array
     */
    public function contentVectorsProvider()
    {
        return [
            [12345678, 6,                  12345678, 12.345678],
            [-10, 6,                       0, 0],
            [null, null,                   0, 0],
            [false, false,                 0, 0],
            [878273342850120, 6,           878273342850120, 878273342.850120],
            [-(-150), 0,                   150, 150],
            [true, false,                  1, 0.000001],
            [0.00000013, 6,                0, 0],
            [0.000001, 6,                  1, 0.000001],
            [1000000, 0,                   1000000, 1000000],
            ["10", 1,                      10, 1],
            ["10", 2,                      10, 0.1],
            ["10.10", 2,                   1010, 10.1],
            ["10.10", 4,                   101000, 10.1],
            ["10.10000000", 6,             10100000, 10.1],
            ["10000000", 6,                10000000, 10.000000],
            [[0x10], 6,                    16, 0.000016],
            [[0x10, 0x20], 6,              16, 0.000016],
            [[null], 6,                    0, 0],
            [10.18, 1,                     101, 10.1],
            [10.18, 0,                     10, 10],
            [10.9999, 0,                   10, 10],
            ["10.99", 1,                   109, 10.9],
            ["10.99", 0,                   10, 10],
        ];
    }

    /**
     * Test content initialization for Amount DTO.
     *
     * @depends testNISAmountDTOStructure
     * @dataProvider contentVectorsProvider
     *
     * @param       integer         $amount
     * @param       integer         $divisibility
     * @param       integer         $expectedAmount
     * @param       integer         $expectedUnit
     * @return void
     */
    public function testNISAmountDTOContent($amount, $divisibility, $expectedAmount, $expectedUnit)
    {
        $amountA = new Amount(["amount" => $amount]);
        $amountA->setDivisibility($divisibility);

        $this->assertEquals($expectedAmount, $amountA->toMicro());
        $this->assertEquals($expectedUnit, $amountA->toUnit());
    }

    /**
     * Data provider for `testMosaicAmountXEMRepresentation` Unit Test.
     *
     * Each row should contain 5 fields in following *strict* order:
     *
     * - multiplier:           Micro XEM attachment multiplier.
     * - quantity:        Fractional mosaic amount (smallest unit of the mosaic)
     * - supply:               Total Supply of the Mosaic.
     * - divisibility:         The mosaic divisibility.
     * - expectedAmount:       Expected amount returned by the Amount model in MICRO XEM.
     *
     * @return array
     */
    public function mosaicAmountVectorsProvider()
    {
        return [
            [1000000, 1, 9000000000, 6, 9.99999999888889e-7],
            [1000000, 1000, 9000000000, 6, 0.000999999999888889],
            [1000000, 1000000, 9000000000, 6, 0.9999999998888889],
            [1000000, 1, 9000000000, 0, 0.9999999998888889],
            [1000000, 1000, 9000000000, 0, 999.999999888889],
            [1000000, 1000000, 9000000000, 0, 999999.9998888889],
            [1000000, 100, 10, 0, 89999999990],
            [1000000, 5, 10, 0, 4499999999.5],
            [10000000, 5, 10, 0, 44999999995],
        ];
    }

    /**
     * Test mosaic amount XEM equivalency.
     *
     * @depends testNISAmountDTOStructure
     * @dataProvider mosaicAmountVectorsProvider
     *
     * @param       integer         $multiplier
     * @param       integer         $quantity
     * @param       integer         $supply
     * @param       integer         $divisibility
     * @param       integer         $expectedAmount
     * @return void
     */
    public function testMosaicAmountXEMRepresentation($multiplier, $quantity, $supply, $divisibility, $expectedAmount)
    {
        $toXEM = Amount::mosaicQuantityToXEM($divisibility, $supply, $quantity, $multiplier);

        $this->assertEquals($expectedAmount, $toXEM);
    }
}
