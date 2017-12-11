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
        "address",
        "publicKey",
        "privateKey",
        "balance",
        "vestedBalance",
        "importance",
        "label",
        "harvestedBlocks",
        "status",
        "remoteStatus",
        "cosignatoryOf",
        "cosignatories",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "cosignatoryOf",
        "cosignatories",
    ];

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO()
    {
        return [
            "account" => [
                "address" => $this->address()->toClean(),
                "balance" => (int) $this->balance,
                "vestedBalance" => (int) $this->balance,
                "importance" => (float) $this->importance,
                "publicKey" => $this->publicKey,
                "label" => $this->label,
                "harvestedBlocks" => (int) $this->harvestedBlocks,
            ],
            "meta" => [
                "status" => $this->status,
                "remoteStatus" => $this->remoteStatus,
                "cosignatoryOf" => $this->cosignatoryOf()->toDTO(),
                "cosignatories" => $this->cosignatories()->toDTO(),
            ]
        ];
    }

    /**
     * Mutator for the address object.
     *
     * @return \NEM\Models\Address
     */
    public function address($address = null)
    {
        return new Address(["address" => $address ?: $this->attributes["address"]]);
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
