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

use NEM\Models\TimeWindow;

use DateTime;
use DateTimeZone;

class DTOTimeWindowTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for TimeWindow class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $nemTime = new TimeWindow([]);
        $nemTimeNIS = $nemTime->toDTO();

        $this->assertInternalType("int", $nemTimeNIS);
    }

    /**
     * Unit test for *NIS compliance of DTO Structure for TimeWindow class*.
     * 
     * @dataProvider dtoContentVectorsProvider
     * @depends testDTOStructure
     * @return void
     */
    public function testDTOContentVectors(DateTime $date, $expectNIS, $expectUTC)
    {
        $nemTime = new TimeWindow(["timeStamp" => $date->getTimestamp()]);
        $nemTimeNIS = $nemTime->toDTO();

        // test content
        $this->assertEquals($expectNIS, $nemTimeNIS);
        $this->assertEquals($expectNIS, $nemTime->toNIS());
        $this->assertEquals($expectUTC, $nemTime->toUTC());
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
                ($t = new DateTime("2018-01-01 01:01:01")),
                87180876, // NIS
                1516196048585 // UTC
            ],
            [
                ($t1 = new DateTime("2018-01-01 00:01:01")),
                87180876 - 3600, 
                TimeWindow::$nemesis + ($t1->getTimestamp() * 1000)
            ],
            [
                ($t2 = new DateTime("2018-01-02 03:33:33")),
                87180876 + 95552, 
                TimeWindow::$nemesis + ($t2->getTimestamp() * 1000)
            ],
        ];
    }
}
