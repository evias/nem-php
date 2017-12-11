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

class Transaction
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "timeStamp", // NIS is fault for the "S" not me. :P
        "amount",
        "fee",
        "recipient",
        "type",
        "deadline",
        "message",
        "version",
        "signer",
        "id",
        "height",
        "hash",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "timeStamp",
        "amount",
        "fee",
        "recipient",
        "message",
        "signatures"
    ];

    /**
     * The extend() method must be overloaded by any Transaction Type
     * which needs to extend the base DTO structure.
     *
     * @return array
     */
    public function extend() 
    {
        // default transaction type does not extend transaction DTO.
        return [];
    }

    /**
     * The extendMeta() method must be overloaded by any Transaction Type
     * which needs to extend the base META structure.
     *
     * @return array
     */
    public function extendMeta()
    {
        // default transaction type does not extend transaction DTO.
        return [];
    }

    /**
     * The meta() method must be overloaded by any Transaction Type
     * which needs to extend the base META structure.
     *
     * @return array
     */
    public function meta()
    {
        $meta = [];

        // look for basic parameters of transactions, only unconfirmed
        // transactions will have those fields empty.

        if ($this->attributes["id"])
            $meta["id"] = (int) $this->attributes["id"];

        if ($this->attributes["height"])
            $meta["height"] = (int) $this->attributes["height"];

        if ($this->attributes["hash"])
            $meta["hash"] = ["data" => $this->attributes["hash"]];

        return $meta;
    }

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO()
    {
        $baseMeta = $this->meta();
        $baseEntity = [
            "timeStamp" => $this->timeStamp()->toNEMTime(),
            "amount"    => $this->amount()->toMicro(),
            "fee"       => $this->fee()->toMicro(),
            "recipient" => $this->recipient()->address()->toClean(),
            "type"      => (int) $this->attributes["type"],
            "deadline"  => $this->timeStamp()->deadline()->toNEMTime(),
            "message"   => $this->message()->toDTO(),
            "version"   => (int) $this->attributes["version"],
            "signer"    => $this->attributes["signer"],
        ];

        $meta = array_merge($baseMeta, $this->extendMeta());
        $entity = array_merge($baseEntity, $this->extend());

        return [
            "meta" => $meta,
            "transaction" => $entity,
        ];
    }

    /**
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function recipient($address = null)
    {
        return new Account(["address" => $address ?: $this->attributes["recipient"]]);
    }

    /**
     * Mutator for the amount object.
     *
     * @return \NEM\Models\Amount
     */
    public function amount($amount = null)
    {
        return new Amount(["amount" => $amount ?: $this->attributes["amount"]]);
    }

    /**
     * Mutator for the fee amount object.
     *
     * @return \NEM\Models\Fee
     */
    public function fee($fee = null)
    {
        $amount = $fee ?: $this->attributes["fee"];
        if (!$amount)
            $amount = Fee::calculateForTransaction($this)->toMicro();

        return new Fee(["amount" => $amount]);
    }

    /**
     * Mutator for the message object.
     *
     * @return \NEM\Models\Message
     */
    public function message($payload = null)
    {
        return new Message(["payload" => $payload ?: $this->attributes["message"]]);
    }
}
