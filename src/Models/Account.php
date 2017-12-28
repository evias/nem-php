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
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models;

use NEM\Models\Mutators\CollectionMutator;

class Account
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        // NIS "meta" sub DTO (AccountMetaData)
        "status"        => "meta.status",
        "remoteStatus"  => "meta.remoteStatus",
        "cosignatoryOf" => "meta.cosignatoryOf",
        "cosignatories" => "meta.cosignatories",
        // NIS "account" sub DTO (AccountInfo)
        "address"       => "account.address",
        "publicKey"     => "account.publicKey",
        "balance"       => "account.balance",
        "label"         => "account.label",
        "vestedBalance" => "account.vestedBalance",
        "importance"    => "account.importance",
        "harvestedBlocks" => "account.harvestedBlocks",
        // NIS "account.multisigInfo" sub DTO (@see \NEM\Models\MultisigInfo)
        "multisigInfo"       => "account.multisigInfo",
        "cosignatoriesCount" => "account.multisigInfo.cosignatoriesCount",
        "minCosignatories"   => "account.multisigInfo.minCosignatories",
    ];

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "harvestedBlocks" => "int", 
    ];

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO($filterByKey = null)
    {
        $toDTO = [
            "account" => [
                "address" => $this->address()->toClean(),
                "balance" => $this->balance()->toMicro(),
                "vestedBalance" => $this->vestedBalance()->toMicro(),
                "importance" => $this->importance/*()->toScientific()*/,
                "publicKey" => $this->publicKey,
                "label" => $this->label,
                "harvestedBlocks" => $this->harvestedBlocks,
                "multisigInfo" => $this->multisigInfo()->toDTO(),
            ],
            "meta" => [
                "status" => $this->status,
                "remoteStatus" => $this->remoteStatus,
                "cosignatoryOf" => $this->cosignatoryOf()->toDTO(),
                "cosignatories" => $this->cosignatories()->toDTO(),
            ]
        ];

        if ($filterByKey && isset($toDTO[$filterByKey]))
            return $toDTO[$filterByKey];

        return $toDTO;
    }

    /**
     * Mutator for the address object.
     *
     * @return \NEM\Models\Address
     */
    public function address($address = null)
    {
        return new Address(["address" => $address ?: $this->getAttribute("address")]);
    }

    /**
     * Mutator for the balance object.
     *
     * @return \NEM\Models\Amount
     */
    public function balance($amount = null)
    {
        return new Amount(["amount" => $amount ?: $this->getAttribute("balance")]);
    }

    /**
     * Mutator for the vestedBalance object.
     *
     * @return \NEM\Models\Amount
     */
    public function vestedBalance($amount = null)
    {
        return new Amount(["amount" => $amount ?: $this->getAttribute("vestedBalance")]);
    }

    /**
     * Mutator for the multisigInfo object.
     *
     * @return \NEM\Models\MultisigInfo
     */
    public function multisigInfo(array $info = null)
    {
        return new MultisigInfo($info ?: $this->getAttribute("multisigInfo"));
    }

    /**
     * Mutator for the cosignatoryOf object collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function cosignatoryOf(array $data = null)
    {
        $multisigs = $data ?: $this->getAttribute("cosignatoryOf") ?: [];
        return (new CollectionMutator())->mutate("account", $multisigs);
    }

    /**
     * Mutator for the cosignatories object collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function cosignatories(array $data = null)
    {
        $cosignatories = $data ?: $this->getAttribute("cosignatories") ?: [];
        return (new CollectionMutator())->mutate("account", $cosignatories);
    }
}
