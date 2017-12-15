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

use NEM\Models\Account;
use NEM\Models\Address;
use NEM\Models\Amount;
use NEM\Models\MultisigInfo;
use NEM\Models\ModelCollection;

class AccountMetaDataPairTest
    extends NISComplianceTestCase
{
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
    public function contentVectorsProvider()
    {
        return [
            [27334285012, "1.5913243112976873E-4", 27334277908,             27334285012, "1.5913243112976873E-4", 27334277908],
            [-1, "1.5913243112976873E-4", -1,                               0, "1.5913243112976873E-4", 0],
            [5, "0", 6,                                                     5, "0", 6],
            // valid BigInteger
            [878273342850120, "1.5913243112976873E-4", 878273342850110,     878273342850120, "1.5913243112976873E-4", 878273342850110],
            // test empty data
            [null, null, null,                                              0, null, 0],
            // integer type limit for PHP is maximum: 9_223_372_036_854_775_807
            [9223372036854775807, "1.5913243112976873E-4", 9223372036854775807,     9223372036854775807, "1.5913243112976873E-4", 9223372036854775807],
            // test integer type overflow!
            [9223372036854775808, null, 9223372036854775808,                        0, null, 0],
        ];
    }

    /**
     * Test content initialization for Account DTO.
     *
     * @depends testNISAccountMetaDataPairDTOStructure
     * @dataProvider contentVectorsProvider
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
        $this->assertTrue($account->balance()->toUnit() >= 0);
        $this->assertTrue($account->vestedBalance()->toMicro() >= 0);
        $this->assertTrue($account->vestedBalance()->toUnit() >= 0);

        // Make sure importance *was not parsed*.
        $this->assertTrue(!is_float($account->importance));

        // More content validation
        $this->assertEquals($expectedBalance, $account->balance()->toMicro());
        $this->assertEquals($expectedVested, $account->vestedBalance()->toMicro());
        $this->assertEquals($expectedImportance, $account->importance);

        // Make sure *aliased* attributes are set correctly
        $this->assertEquals(2, $account->cosignatoriesCount);
        $this->assertEquals(1, $account->minCosignatories);
    }

    /**
     * Data provider for `testNISAccountMetaDataPairDTORelationships` Unit Test.
     *
     * Each row should contain 6 fields in following *strict* order:
     *
     * - address:               NEM Account Address in Base32 format.
     * - cosignatories:         Array of cosignatories Accounts.
     * - cosignatoryOf:         Micro XEM Account representing the Account's VESTED XEM balance.
     * - multisigInfo:       The expected balance given the `balance` input.
     *
     * @see \NEM\Tests\NIS\NISComplianceTestCase
     * @return array
     */
    public function relationshipVectorsProvider()
    {
        return [
            ["TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ", $this->mockAccounts(2, false), $this->mockAccounts(0, false), $this->mockMultisigInfo(2, 3)],
            ["TATKHV5JJTQXCUCXPXH2WPHLAYE73REUMGDOZKUW", $this->mockAccounts(0, false), $this->mockAccounts(2, false), $this->mockMultisigInfo(2, 3)],
            ["TAEPNTY3Z6YJSU3AKM3UE7ZJUOO42OZBOX444H3N", $this->mockAccounts(5, false), $this->mockAccounts(5, false), $this->mockMultisigInfo(2, 3)],
            ["TCVGH6UJ2TOJVRHRRKFJHZYGRJVTYJ3QM4NS4VGM", $this->mockAccounts(1, false), $this->mockAccounts(0, false), $this->mockMultisigInfo(2, 3)],
            ["TDVA6KUEMTBMA5DVFURWCLXPOOOJEUGLGFQPM35Z", $this->mockAccounts(3, false), $this->mockAccounts(-1, false), $this->mockMultisigInfo(2, 3)],
            ["TCYUEV7UGUKIH6ZJLRR2ACNU3FFXBQN7Z4NGW3FM", $this->mockAccounts(-1, false), $this->mockAccounts(null, false), $this->mockMultisigInfo(2, 3)],
            ["TDWMSBBXGN62GCP3WYDGGR5DS353KKMZBNEZENFO", $this->mockAccounts(null, false), $this->mockAccounts(null, false), $this->mockMultisigInfo(null, null)],
            ["TBYOFADTLLVZCTF3B5WCD7GPZGGQ3JRVYD2N76KG", $this->mockAccounts(100, false), $this->mockAccounts(50, false), $this->mockMultisigInfo(50, 100)],
            ["TCZWOCUT4RKDE6KQUJZQLCKW2THNZVJ2I222VJAQ", $this->mockAccounts(4, false), $this->mockAccounts(0, false), $this->mockMultisigInfo(2, 3)],
        ];
    }

    /**
     * Test relationship initialization for Account DTO.
     *
     * @depends testNISAccountMetaDataPairDTOStructure
     * @dataProvider relationshipVectorsProvider
     * @see https://bob.nem.ninja/docs/#accountMetaDataPair
     * @param       string         $address
     * @param       array          $cosignatories
     * @param       array          $cosignatoryOf
     * @param       array          $multisigInfo
     * @return void
     */
    public function testNISAccountMetaDataPairDTORelationships($address, $cosignatories, $cosignatoryOf, $multisigInfo)
    {
        $randomBytes = unpack("H*", random_bytes(32));
        $randomPublicHex = $randomBytes[1];

        $account = new Account([
            "meta" => [
                "cosignatories" => $cosignatories,
                "cosignatoryOf" => $cosignatoryOf,
                "status" => "LOCKED",
                "remoteStatus" => "INACTIVE"
            ],
            "account" => [
                "address" => $address,
                "harvestedBlocks" => 0,
                "balance" => 0,
                "importance" => 0,
                "vestedBalance" => 0,
                "publicKey" => $randomPublicHex,
                "label" => null,
                "multisigInfo" => $multisigInfo->toDTO()
            ]
        ]);

        // test simple aliased field reader
        $this->assertEquals($address, $account->getAttribute("address"));

        // test relationship methods
        $this->assertTrue($account->address() instanceof Address);
        $this->assertTrue($account->multisigInfo() instanceof MultisigInfo);
        $this->assertTrue($account->balance() instanceof Amount);
        $this->assertTrue($account->vestedBalance() instanceof Amount);
        $this->assertTrue($account->cosignatories() instanceof ModelCollection);
        $this->assertTrue($account->cosignatoryOf() instanceof ModelCollection);

        // test subordinate DTOs
        $this->assertEquals(count($cosignatories), $account->cosignatories()->count());
        $this->assertEquals(count($cosignatoryOf), $account->cosignatoryOf()->count());
        $this->assertEquals($multisigInfo->cosignatoriesCount, $account->multisigInfo()->cosignatoriesCount);
        $this->assertEquals($multisigInfo->minCosignatories, $account->multisigInfo()->minCosignatories);
    }
}
