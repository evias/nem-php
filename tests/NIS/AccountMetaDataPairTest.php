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
namespace NEM\Tests\NIS;

use GuzzleHttp\Exception\ConnectException;
use NEM\Tests\TestCase;

use NEM\API;
use NEM\SDK;
use NEM\Models\Account;
use NEM\Models\Amount;
use NEM\Models\MultisigInfo;

class AccountMetaDataPairTest
    extends TestCase
{
    /**
     * The NEM SDK instance
     *
     * @var \NEM\SDK
     */
    protected $sdk;

    /**
     * The setUp method of this test case will
     * instantiate the API using the bigalice2.nem.ninja
     * NIS testnet node.
     *
     * @see :Execution of this Test Case requires an Internet Connection
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->sdk = new SDK([
            "use_ssl"  => false,
            "protocol" => "http",
            "host" => "bigalice2.nem.ninja", // testing uses online NIS
            "port" => 7890,
            "endpoint" => "/",
        ]);
    }

    /**
     * Test *NIS Compliance* of class \NEM\Models\Account.
     *
     * This class should return the required fields as defined in
     * [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @return void
     */
    public function testNISAccountMetaDataPairDTOStructure()
    {
        // empty account should still always produce NIS compliant
        // objects when converted to Data Transfer Object.
        $account = new Account([]);
        $accountNIS = $account->toDTO();

        // test AccountMetaDataPair DTO
        $this->assertArrayHasKey("meta", $accountNIS);
        $this->assertArrayHasKey("account", $accountNIS);

        // test AccountMetaData sub DTO
        $this->assertArrayHasKey("cosignatories", $accountNIS["meta"]);
        $this->assertArrayHasKey("cosignatoryOf", $accountNIS["meta"]);
        $this->assertArrayHasKey("status", $accountNIS["meta"]);
        $this->assertArrayHasKey("remoteStatus", $accountNIS["meta"]);

        // test AccountInfo sub DTO
        $this->assertArrayHasKey("address", $accountNIS["account"]);
        $this->assertArrayHasKey("harvestedBlocks", $accountNIS["account"]);
        $this->assertArrayHasKey("balance", $accountNIS["account"]);
        $this->assertArrayHasKey("vestedBalance", $accountNIS["account"]);
        $this->assertArrayHasKey("importance", $accountNIS["account"]);
        $this->assertArrayHasKey("publicKey", $accountNIS["account"]);
        $this->assertArrayHasKey("label", $accountNIS["account"]);
        $this->assertArrayHasKey("multisigInfo", $accountNIS["account"]);

        // test MultisigInfo sub DTO level 2
        $this->assertArrayHasKey("cosignatoriesCount", $accountNIS["account"]["multisigInfo"]);
        $this->assertArrayHasKey("minCosignatories", $accountNIS["account"]["multisigInfo"]);
    }

    /**
     * Data provider for `testNISAccountMetaDataPairDTOContent` Unit Test.
     * 
     * Each row should contain 6 fields in following *strict* order:
     * 
     * - balance:               Micro XEM Amount representing the Account's XEM balance.
     * - importance:            String containing scientific notation of Account Importance.
     * - vestedBalance:         Micro XEM Account representing the Account's VESTED XEM balance.
     * - expectedBalance:       The expected balance given the `balance` input.
     * - expectedImportance:    The expected importance given the `importance` input.
     * - expectedVested:        The expected vested balance given the `vestedBalance` input.
     */
    public function accountVectorsProvider()
    {
        return [
            [27334285012, "1.5913243112976873E-4", 27334277908,         27334285012, "1.5913243112976873E-4", 27334277908],
            [-1, "1.5913243112976873E-4", -1,                           0, "1.5913243112976873E-4", 0],
            [5, "0", 6,                                                 5, "0", 6],
            [878273342850120, "1.5913243112976873E-4", 878273342850110,         878273342850120, "1.5913243112976873E-4", 878273342850110],
        ];
    }

    /**
     * Test content initialization for Account DTO.
     *
     * @depends testNISAccountMetaDataPairDTOStructure
     * @dataProvider accountVectorsProvider
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @param       integer         $balance
     * @param       string          $importance
     * @param       integer         $vestedBalance
     * @param       integer         $expectedBalance
     * @param       string          $expectedImportance
     * @param       integer         $expectedVested
     * @return void
     */
    public function testNISAccountMetaDataPairDTOContent($balance, $importance, $vestedBalance, $expectedBalance, $expectedImportance, $expectedVested)
    {
        $randomBytes = unpack("H*", random_bytes(32));
        $randomPublicHex = $randomBytes[1];
        $testAddress = 'TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ';

        $account = new Account([
            "meta" => [
                "cosignatories" => [],
                "cosignatoryOf" => [],
                "status" => "LOCKED",
                "remoteStatus" => "INACTIVE"
            ],
            "account" => [
                "address" => $testAddress,
                "harvestedBlocks" => 0,
                "balance" => $balance,
                "importance" => $importance,
                "vestedBalance" => $vestedBalance,
                "publicKey" => $randomPublicHex,
                "label" => null,
                "multisigInfo" => [
                    "cosignatoriesCount" => 2,
                    "minCosignatories" => "1",
                ]
            ]
        ]);

        $accountNIS = $account->toDTO();

        // test valid subordinate DTOs instance types
        // this will test both the sub DTO relationship method and the
        // Model::__get() overload for loaded relationships.
        $this->assertTrue($account->balance() instanceof Amount);
        $this->assertTrue($account->balance instanceof Amount);
        $this->assertTrue($account->vestedBalance() instanceof Amount);
        $this->assertTrue($account->vestedBalance instanceof Amount);
        $this->assertTrue($account->multisigInfo() instanceof MultisigInfo);
        $this->assertTrue($account->multisigInfo instanceof MultisigInfo);

        // Amounts can never be negative
        $this->assertTrue($account->balance()->toMicro() >= 0);
        $this->assertTrue($account->balance()->toXEM() >= 0);
        $this->assertTrue($account->vestedBalance()->toMicro() >= 0);
        $this->assertTrue($account->vestedBalance()->toXEM() >= 0);

        // Make sure importance *was not parsed*.
        $this->assertTrue(is_string($account->importance));

        // More content validation
        $this->assertEquals($expectedBalance, $account->balance()->toMicro());
        $this->assertEquals($expectedVested, $account->vestedBalance()->toMicro());
        $this->assertEquals($expectedImportance, $account->importance);

        // Make sure *aliased* attributes are set correctly
        $this->assertEquals(2, $account->cosignatoriesCount);
        $this->assertEquals(1, $account->minCosignatories);
    }
}
