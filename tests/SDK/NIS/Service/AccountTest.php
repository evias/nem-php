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
namespace NEM\Tests\API;

use NEM\Tests\TestCase;
use Mockery;

class AccountTest
    extends TestCase
{
    /**
     * Unit Test for *available method on the Account Service*.
     *
     * @return  void
     */
    public function testAvailableMethods()
    {
        $fakeAddr = "TA3SH7QUTG6OS4EGHSES426552FAJYZR2PHOBLNA";
        $fakePub  = "5645ea5b6bfc9bce6e69eab6002281d0e9c52fc0405ab99533d28e497b96ed81";
        $fakePriv = "dd19f3f3178c0867771eed180310a484e1b76527f7a271e3c8b5264e4a5aa414";

        $instance = Mockery::mock("NEM\Infrastructure\Account");
        $account  = Mockery::mock("NEM\Models\Account");
        $model    = Mockery::mock("NEM\Models\Model");
        $collection = Mockery::mock("NEM\Models\ModelCollection");

        // Account::generateAccount()
        $instance->shouldReceive("generateAccount")
                 ->andReturn($account);

        $result = $instance->generateAccount();
        $this->assertInstanceOf(\NEM\Models\Account::class, $result);

        // Account::getFromAddress()
        $instance->shouldReceive("getFromAddress")
                 ->with($fakeAddr)
                 ->andReturn($account);

        $result = $instance->getFromAddress($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\Account::class, $result);

        // Account::getFromPublicKey()
        $instance->shouldReceive("getFromPublicKey")
                 ->with($fakePub)
                 ->andReturn($account);

        $result = $instance->getFromPublicKey($fakePub);
        $this->assertInstanceOf(\NEM\Models\Account::class, $result);

        // Account::getFromDelegated()
        $instance->shouldReceive("getFromDelegatedAddress")
                 ->with($fakeAddr)
                 ->andReturn($account);

        $result = $instance->getFromDelegatedAddress($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\Account::class, $result);

        // Account::getFromDelegatedPublicKey()
        $instance->shouldReceive("getFromDelegatedPublicKey")
                 ->with($fakePub)
                 ->andReturn($account);

        $result = $instance->getFromDelegatedPublicKey($fakePub);
        $this->assertInstanceOf(\NEM\Models\Account::class, $result);

        // Account::status()
        $instance->shouldReceive("status")
                 ->with($fakeAddr)
                 ->andReturn($model);

        $result = $instance->status($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\Model::class, $result);

        // Account::incomingTransactions()
        $instance->shouldReceive("incomingTransactions")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->incomingTransactions($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::outgoingTransactions()
        $instance->shouldReceive("outgoingTransactions")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->outgoingTransactions($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::allTransactions()
        $instance->shouldReceive("allTransactions")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->allTransactions($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::unconfirmedTransactions()
        $instance->shouldReceive("unconfirmedTransactions")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->unconfirmedTransactions($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getHarvestInfo()
        $instance->shouldReceive("getHarvestInfo")
                 ->with($fakeAddr)
                 ->andReturn($model);

        $result = $instance->getHarvestInfo($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\Model::class, $result);

        // Account::getAccountImportances()
        $instance->shouldReceive("getAccountImportances")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->getAccountImportances($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getOwnedNamespaces()
        $instance->shouldReceive("getOwnedNamespaces")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->getOwnedNamespaces($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getCreatedMosaics()
        $instance->shouldReceive("getCreatedMosaics")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->getCreatedMosaics($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getOwnedMosaics()
        $instance->shouldReceive("getOwnedMosaics")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->getOwnedMosaics($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getHistoricalAccountData()
        $instance->shouldReceive("getHistoricalAccountData")
                 ->with($fakeAddr)
                 ->andReturn($collection);

        $result = $instance->getHistoricalAccountData($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\ModelCollection::class, $result);

        // Account::getUnlockInfo()
        $instance->shouldReceive("getUnlockInfo")
                 ->with($fakeAddr)
                 ->andReturn($model);

        $result = $instance->getUnlockInfo($fakeAddr);
        $this->assertInstanceOf(\NEM\Models\Model::class, $result);

        // Account::startHarvesting()
        $instance->shouldReceive("startHarvesting")
                 ->with($fakePriv)
                 ->andReturn($model);

        $result = $instance->startHarvesting($fakePriv);
        $this->assertInstanceOf(\NEM\Models\Model::class, $result);

        // Account::stopHarvesting()
        $instance->shouldReceive("stopHarvesting")
                 ->with($fakePriv)
                 ->andReturn($model);

        $result = $instance->stopHarvesting($fakePriv);
        $this->assertInstanceOf(\NEM\Models\Model::class, $result);
    }

}
