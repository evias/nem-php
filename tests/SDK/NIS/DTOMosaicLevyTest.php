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
namespace NEM\Tests\SDK\NIS;

use NEM\Models\Mosaic;
use NEM\Models\MosaicLevy;

class DTOMosaicLevyTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *automatic cast for `type` and `fee` integers*.
     * 
     * @return void
     */
    public function testAutomaticCastsForIntegers()
    {
        $levy = new MosaicLevy([
            "type" => (string) MosaicLevy::TYPE_ABSOLUTE,
            "fee" => "15000"
        ]);

        $this->assertInternalType("int", $levy->type);
        $this->assertInternalType("int", $levy->fee);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicLevy class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"]);

        $levy  = new MosaicLevy([
            "type" => MosaicLevy::TYPE_ABSOLUTE,
            "fee" => 10,
            "mosaicId" => $mosaic->toDTO(),
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"]);
        $levyNIS = $levy->toDTO();

        // test types
        $this->assertArrayHasKey("type", $levyNIS);
        $this->assertArrayHasKey("fee", $levyNIS);
        $this->assertArrayHasKey("mosaicId", $levyNIS);
        $this->assertArrayHasKey("recipient", $levyNIS);

        // test content
        $this->assertEquals(MosaicLevy::TYPE_ABSOLUTE, $levy->type);
        $this->assertEquals(10, $levy->fee);
        $this->assertEquals("evias.sdk", $levy->mosaicId()->namespaceId);
        $this->assertEquals("nem-php", $levy->mosaicId()->name);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicAttachment class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors($mosaicFQN, $fee, $type, $recipient, $expectType, $expectFee)
    {
        $mosaic = Mosaic::create($mosaicFQN);

        $levy  = new MosaicLevy([
            "type" => $type,
            "fee" => $fee,
            "mosaicId" => $mosaic->toDTO(),
            "recipient" => $recipient]);
        $levyNIS = $levy->toDTO();

        // test content
        $this->assertEquals($expectType, $levyNIS["type"]);
        $this->assertEquals($expectFee, $levyNIS["fee"]);
        $this->assertEquals($mosaicFQN, $levy->mosaicId()->getFQN());
        $this->assertEquals($recipient, $levyNIS["recipient"]);
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
                "pacnem:hall-of-famer",
                "200", "2",
                "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
                MosaicLevy::TYPE_PERCENTILE,
                200
            ],
            [
                "nem:xem",
                "100", "2",
                "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
                MosaicLevy::TYPE_PERCENTILE,
                100
            ],
            [
                "fun:coin",
                "10", "1",
                "TD2PEY23Y6O3LNGAO4YJYNDRQS3IRTEC7PZUIWLT",
                MosaicLevy::TYPE_ABSOLUTE,
                10
            ],
        ];
    }
}
