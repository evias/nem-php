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

use NEM\Tests\TestCase;
use NEM\Models\Account;
use NEM\Models\MultisigInfo;

class NISComplianceTestCase
    extends TestCase
{
    /**
     * This method is a helper to create a `count` count of 
     * accounts with random content.
     *
     * @param   integer     $count      The number of accounts to create
     * @return  array
     */
    protected function mockAccounts($count, $meta = true)
    {
        $addressContent = "DWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";
        $accounts = [];
        for ($i = 0; $i < $count; $i++) {
            $account = new Account([
                "address" => "T" . str_shuffle($addressContent),
                "status" => random_int(0, 1) ? "LOCKED" : "UNLOCKED",
                "remoteStatus" => random_int(0, 1) ? "ACTIVE" : "INACTIVE",
            ]);

            // only return the AccountInfo DTO
            $dto = $meta === true ? $account->toDTO() : $account->toDTO("account");
            array_push($accounts, $dto);
        }

        if ($count === 1)
            return $accounts[0]; // direct model return

        return $accounts;
    }

    /**
     * This method is a helper to create a MultisigInfo object
     * with `min` minCosignatories and `count` cosignatoriesCount.
     *
     * @param   integer     $min      The minimum cosignatories needed for transactions.
     * @param   integer     $count    The number of cosignatories available.
     * @return  array
     */
    protected function mockMultisigInfo($min, $count)
    {
        $info = new MultisigInfo([
            "minCosignatories" => $min,
            "cosignatoriesCount" => $count,
        ]);

        return $info;
    }
}
