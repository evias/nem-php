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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models;

use NEM\Models\Mutators\ModelMutator;
use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\TransactionType;

class Transaction
    extends Model
{
    /**
     * NEM Transaction Versions
     * 
     * v1 transaction cannot contain mosaics!
     * 
     * v1: 1744830465 (VERSION_1: MainNet)
     * v2: 1744830466 (VERSION_2: MainNet)
     * 
     * @internal
     * @var integer
     */
    const VERSION_1 = 0x68000000 | 1;
    const VERSION_2 = 0x68000000 | 2;
    const VERSION_1_TEST = 0x98000000 | 1;
    const VERSION_2_TEST = 0x98000000 | 2;
    const VERSION_1_MIJIN = 0x60000000 | 1;
    const VERSION_2_MIJIN = 0x60000000 | 2;

    /**
     * List of valid Transaction Types on the NEM network.
     * 
     * @var array
     */
    static protected $validTypes = [
        TransactionType::TRANSFER,
        TransactionType::IMPORTANCE_TRANSFER,
        TransactionType::MULTISIG_MODIFICATION,
        TransactionType::MULTISIG_SIGNATURE,
        TransactionType::MULTISIG,
        TransactionType::PROVISION_NAMESPACE,
        TransactionType::MOSAIC_DEFINITION,
        TransactionType::MOSAIC_SUPPLY_CHANGE,
    ];

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        /**
         Alias          =>          NIS Path
         */
        // NIS "meta" sub-dto
        "id"            => "meta.id", 
        "height"        => "meta.height", 
        "hash"          => "meta.hash",
        // NIS "transaction" sub-dto
        "timeStamp"     => "transaction.timeStamp",
        "amount"        => "transaction.amount",
        "fee"           => "transaction.fee",
        "recipient"     => "transaction.recipient",
        "type"          => "transaction.type",
        "deadline"      => "transaction.deadline",
        "version"       => "transaction.version",
        "signer"        => "transaction.signer",
        "signature"     => "transaction.signature",
        "signatures"    => "transaction.signatures",
        "message"       => "transaction.message",
        // NIS "transaction.message" sub-dto
        "messagePayload"=> "transaction.message.payload",
        "messageType"   => "transaction.message.type",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "timeStamp",
        "deadline",
        "amount",
        "fee",
        "recipient",
        "message",
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

        if (isset($this->attributes["id"]))
            $meta["id"] = (int) $this->attributes["id"];

        if (isset($this->attributes["height"]))
            $meta["height"] = (int) $this->attributes["height"];

        if (isset($this->attributes["hash"]))
            $meta["hash"] = ["data" => $this->attributes["hash"]];

        return $meta;
    }

    /**
     * Account DTO represents NIS API's [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair).
     *
     * @see [AccountMetaDataPair](https://bob.nem.ninja/docs/#accountMetaDataPair)
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO($filterByKey = null)
    {
        $baseMeta = $this->meta();

        $baseEntity = [
            "timeStamp" => $this->timeStamp()->toDTO(),
            "amount"    => $this->amount()->toMicro(),
            "fee"       => $this->fee()->toMicro(),
            "recipient" => $this->recipient()->address()->toClean(),
            "message"   => $this->message()->toDTO(),
        ];

        // extend entity data in sub class
        // @see \NEM\Models\Transaction\MosaicTransfer
        $meta = array_merge($baseMeta, $this->extendMeta());
        $entity = array_merge($baseEntity, $this->extend());

        // mosaics field is used to determine the version
        $versionByContent = !isset($entity["mosaics"]) ? self::VERSION_1 
                                                       : self::VERSION_2;

        // validate version field, should always reflect valid NIS tx version
        $version = $this->getAttribute("version");
        if (! $version || !in_array($version, [self::VERSION_1, self::VERSION_2])) {
            $version = $versionByContent;
        }

        // validate transaction type, should always be a valid type
        $type = $this->getAttribute("type");
        if (! $type || ! in_array($type, self::$validTypes)) {
            $type = TransactionType::TRANSFER;
            $this->setAttribute("type", $type);
        }

        // deadline set to +1 hour if none set
        $deadline = $this->getAttribute("deadline");
        if (! $deadline || $deadline <= 0) {
            $txTime = $entity["timeStamp"];
            $deadline = $txTime + 3600;
            $this->setAttribute("deadline", $deadline);
        }

        // do we have a signer / a signature
        $optionals = ["signer", "signature"];
        foreach ($optionals as $field) {
            $data = $this->getAttribute($field);
            if (null !== $data) {
                $entity[$field] = $data;
            }
        }

        // push validated input
        $entity["type"] = $type;
        $entity["version"] = $version;
        $entity["deadline"] = $deadline;

        $toDTO = [
            "meta" => $meta,
            "transaction" => $entity,
        ];

        if ($filterByKey && isset($toDTO[$filterByKey]))
            return $toDTO[$filterByKey];

        return $toDTO;
    }

    /**
     * Returns timestamp of the transaction.
     *
     * @return int
     */
     public function timestamp($timestamp = null) 
     {
        $ts = $timestamp ?: $this->getAttribute("timeStamp");
        if (is_integer($ts) || $ts instanceof TimeWindow) {
            return new TimeWindow(["timeStamp" => $ts]);
        }

        return new TimeWindow();
    }

    /**
     * Returns deadline associated with the transaction
     *
     * @return int
     */
    public function deadline($deadline = null) 
    {
        $ts = $deadline ?: $this->getAttribute("deadline");
        if (is_integer($ts) || $ts instanceof TimeWindow) {
            return new TimeWindow(["timeStamp" => $ts]);
        }

        return new TimeWindow();
    }

    /**
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function recipient($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("recipient")]);
    }

    /**
     * Mutator for the amount object.
     *
     * @return \NEM\Models\Amount
     */
    public function amount($amount = null)
    {
        return new Amount(["amount" => $amount ?: $this->getAttribute("amount")]);
    }

    /**
     * Mutator for the fee amount object.
     *
     * @return \NEM\Models\Fee
     */
    public function fee($fee = null)
    {
        $amount = $fee ?: $this->getAttribute("fee");
        if (!$amount)
            $amount = (int) Fee::calculateForTransaction($this);

        return new Fee(["amount" => $amount]);
    }

    /**
     * Mutator for the message object.
     *
     * @return \NEM\Models\Message
     */
    public function message($payload = null)
    {
        $messagePayload = $payload ?: $this->getAttribute("message") ?: [];
        return (new ModelMutator())->mutate("message", $messagePayload);
    }

    /**
     * Mutator for the signatures object collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function signatures(array $data = null)
    {
        $signatures = $data ?: $this->getAttribute("signatures") ?: [];
        return (new CollectionMutator())->mutate("Transaction\\Signature", $signatures);
    }
}
