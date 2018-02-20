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
namespace NEM\Tests\SDK\NIS\DTO;

use NEM\Tests\SDK\NIS\NISComplianceTestCase;
use NEM\Models\MultisigInfo;

class DTOMultisigInfoTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for MultisigInfo class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $multisigInfo = new MultisigInfo([
            "cosignatoriesCount" => 3,
            "minCosignatories" => 2
        ]);

        $multisigInfoNIS = $multisigInfo->toDTO();

        $this->assertArrayHasKey("cosignatoriesCount", $multisigInfoNIS);
        $this->assertArrayHasKey("minCosignatories", $multisigInfoNIS);
        $this->assertInternalType("int", $multisigInfoNIS["cosignatoriesCount"]);
        $this->assertInternalType("int", $multisigInfoNIS["minCosignatories"]);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MultisigInfo class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors($cosigCnt, $minCnt, $expectCosig, $expectMin)
    {
        $multisigInfo = new MultisigInfo([
            "cosignatoriesCount" => $cosigCnt,
            "minCosignatories" => $minCnt,
        ]);

        $multisigInfoNIS = $multisigInfo->toDTO();

        // test content
        $this->assertEquals($expectCosig, $multisigInfoNIS["cosignatoriesCount"]);
        $this->assertEquals($expectMin, $multisigInfoNIS["minCosignatories"]);
    }

    /**
     * Data provider for the testDTOContentVectors() unit test
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        return [
            [0, 0,          0, 0],
            [1, 1,          1, 1],
            [null, null,          0, 0],
            [-1, -1,          0, 0],
        ];
    }
}
