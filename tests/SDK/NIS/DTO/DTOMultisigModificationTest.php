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
use NEM\Models\MultisigModification;

class DTOMultisigModificationTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for MultisigInfo class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $modification = new MultisigModification([
            "modificationType" => MultisigModification::TYPE_ADD,
            "cosignatoryAccount" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"
        ]);

        $modificationNIS = $modification->toDTO();

        $this->assertArrayHasKey("modificationType", $modificationNIS);
        $this->assertArrayHasKey("cosignatoryAccount", $modificationNIS);
        $this->assertInternalType("int", $modificationNIS["modificationType"]);
        $this->assertInternalType("string", $modificationNIS["cosignatoryAccount"]);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for MultisigInfo class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors($modType, $modAcct, $expectType, $expectAccount)
    {
        $modification = new MultisigModification([
            "modificationType" => $modType,
            "cosignatoryAccount" => $modAcct
        ]);

        $modificationNIS = $modification->toDTO();

        // test content
        $this->assertEquals($expectType, $modificationNIS["modificationType"]);
        $this->assertEquals($expectAccount, $modificationNIS["cosignatoryAccount"]);
    }

    /**
     * Data provider for the testDTOContentVectors() unit test
     * 
     * @return array
     */
    public function dtoContentVectorsProvider()
    {
        return [
            ["1", "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",       1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            ["2", "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",       2, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [-1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",        1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",         1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [2, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",         2, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [null, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",      1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [false, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",     1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
            [0, "TDWZ55-R5VIHS-H5WWK6-CEGAIP-7D35XV-FZ3RU2-S5UQ",   1, "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"],
        ];
    }
}
