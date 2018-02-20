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
use NEM\Models\MosaicAttachment;

class DTOMosaicAttachmentTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *Mosaic attachment quantity is always integer*.
     * 
     * @return void
     */
    public function testQuantityTypeIsInteger()
    {
        $mosaic = new Mosaic([
             "namespaceId" => "evias.sdk",
             "name" => "nem-php"]);

        $attachment  = new MosaicAttachment([
            "mosaicId" => $mosaic->toDTO(),
            "quantity" => "1500"]);

        $this->assertInternalType("int", $attachment->quantity);
        $this->assertInternalType("int", $attachment->getAttribute("quantity"));
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicAttachment class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $mosaic = new Mosaic([
            "namespaceId" => "evias.sdk",
            "name" => "nem-php"]);

        $attachment  = new MosaicAttachment([
           "mosaicId" => $mosaic->toDTO(),
           "quantity" => 1000]);
        $attachmentNIS = $attachment->toDTO();

        // test types
        $this->assertArrayHasKey("mosaicId", $attachmentNIS);
        $this->assertArrayHasKey("quantity", $attachmentNIS);
        $this->assertInternalType("int", $attachment->quantity);

        // test content
        $this->assertEquals(1000, $attachment->quantity);
        $this->assertEquals("evias.sdk", $attachment->mosaicId()->namespaceId);
        $this->assertEquals("nem-php", $attachment->mosaicId()->name);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MosaicAttachment class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors($mosaicFQN, $quantity, $expectNS, $expectMos, $expectRegistry)
    {
        $mosaic = Mosaic::create($mosaicFQN);

        $attachment  = new MosaicAttachment([
           "mosaicId" => $mosaic->toDTO(),
           "quantity" => $quantity]);
        $attachmentNIS = $attachment->toDTO();

        // test content
        $this->assertEquals($quantity, $attachment->quantity);
        $this->assertEquals($expectNS, $attachment->mosaicId()->namespaceId);
        $this->assertEquals($expectMos, $attachment->mosaicId()->name);
    }

    /**
     * Data provider for the testDTOContentVectors() unit test
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        return [
            ["pacnem:hall-of-famer",        150, "pacnem", "hall-of-famer"],
            ["nem:xem",                 1500000, "nem", "xem"],
            ["my.awesome.subs:mosaics",    1000, "my.awesome.subs", "mosaics"],
            ["special:mos@ic",                0, "special", "mos@ic"],
            ["grégory.saive:identity",        1, "grégory.saive", "identity"],
        ];
    }
}
