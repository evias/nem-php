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
namespace NEM\Tests\SDK;

use GuzzleHttp\Exception\ConnectException;
use NEM\Tests\TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Mutators\ModelMutator;
use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\ModelCollection;
use NEM\Contracts\DataTransferObject;
use NEM\Models\Model;
use NEM\Models\Account;
use NEM\Models\Transaction;
use NEM\Models\Address;

class ServiceMutatorTest
    extends TestCase
{
    /**
     * Test basic details of the SDK instance
     *
     * @return void
     */
    public function testSDKBaseMethods()
    {
        $this->assertTrue($this->sdk->getAPIClient() instanceof API);
        $this->assertEquals("bigalice2.nem.ninja", $this->sdk->getAPIClient()->getRequestHandler()->getHost());
        $this->assertTrue($this->sdk->models() instanceof ModelMutator);
        $this->assertTrue($this->sdk->collect("model", []) instanceof ModelCollection);
    }

    /**
     * Test invalid Service name error case.
     *
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Infrastructure class '\NEM\Infrastructure\InvalidServiceName' could not be found in \NEM\Infrastructure namespace.
     */
    public function testSDKServiceMutator()
    {
        $this->sdk->invalidServiceName();
    }

    /**
     * Test base Service instantiation for unimplemented API Endpoints.
     *
     * @return void
     */
    public function testSDKServiceBaseMutation()
    {
        $service = $this->sdk->service();
        $this->assertTrue($service instanceof \NEM\Infrastructure\Service);
    }

    /**
     * Test model mutator in service base mutation
     *
     * @return void
     */
    public function testSDKServiceMutationModelMutatorWithoutData()
    {
        $service = $this->sdk->service();
        $newAccount = $service->createAccountModel();
        $newTransaction = $service->createTransactionModel();

        // test extendability
        $this->assertTrue($newAccount instanceof DataTransferObject);
        $this->assertTrue($newAccount instanceof Model);

        // test model extension
        $this->assertTrue($newAccount instanceof Account);
        $this->assertTrue($newTransaction instanceof Transaction);
    }

    /**
     * Test collection mutator in service base mutation
     *
     * @return void
     */
    public function testSDKServiceMutationCollectionMutatorWithoutData()
    {
        $service = $this->sdk->service();
        $newAccounts = $service->createAccountCollection();
        $newTransactions = $service->createTransactionCollection();

        $this->assertTrue($newAccounts instanceof ModelCollection);
        $this->assertTrue($newTransactions instanceof ModelCollection);
    }

    /**
     * Test model mutator in service base mutation with data.
     *
     * @return void
     */
    public function testSDKServiceMutationModelMutatorWithData()
    {
        $testAddress = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";

        $service = $this->sdk->service();
        $newAccount = $service->createAccountModel(["address" => $testAddress]);

        $this->assertTrue($newAccount instanceof Account);
        $this->assertTrue($newAccount->address() instanceof Address);
        $this->assertEquals($testAddress, $newAccount->address()->toClean());
    }

    /**
     * Test collection mutator in service base mutation with data.
     *
     * @return void
     */
    public function testSDKServiceMutationCollectionMutatorWithData()
    {
        $testAddress = "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ";

        $service = $this->sdk->service();
        $newAccounts = $service->createAccountCollection([
            ["address" => $testAddress], 
            ["address" => $testAddress]
        ]);

        $this->assertTrue($newAccounts instanceof ModelCollection);
        $this->assertEquals(2, $newAccounts->count());

        foreach ($newAccounts as $testAccount) :
            $this->assertTrue($testAccount->address() instanceof Address);
            $this->assertEquals($testAddress, $testAccount->address()->toClean());
        endforeach ;
    }
}
