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

use NEM\Models\Transaction;
use NEM\Models\TransactionType;
use NEM\Models\TimeWindow;
use NEM\Models\Amount;
use NEM\Models\Message;

class DTOTransactionTest
    extends NISComplianceTestCase
{
    /**
     * Unit test for *NIS compliance of DTO Structure for TimeWindow class*.
     * 
     * @return void
     */
    public function testDTOStructure()
    {
        $transaction = new Transaction([
            "timeStamp" => (new TimeWindow(["utc" => 1516196048585]))->toDTO(),
            "amount" => (new Amount(["amount" => 15]))->toDTO(),
            "recipient" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ",
            "type"      => TransactionType::TRANSFER,
            "message"   => (new Message(["plain" => "Hello, Greg!"]))->toDTO(),
        ]);
        $transactionNIS = $transaction->toDTO();

        $this->assertArrayHasKey("transaction", $transactionNIS);
        $this->assertArrayHasKey("meta", $transactionNIS);

        $meta = $transactionNIS["meta"];
        $content = $transactionNIS["transaction"];

        $this->assertArrayHasKey("timeStamp", $content);
        $this->assertArrayHasKey("amount", $content);
        $this->assertArrayHasKey("recipient", $content);
        $this->assertArrayHasKey("type", $content);
        $this->assertArrayHasKey("message", $content);
    }
}
