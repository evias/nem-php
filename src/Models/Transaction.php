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
use NEM\Models\Transaction\Transfer;
use NEM\Models\Transaction\Signature;
use NEM\Models\TimeWindow;
use NEM\Models\Amount;
use NEM\Models\Fee;
use NEM\Models\Account;
use NEM\Models\Message;

/**
 * This is the Transaction class
 *
 * This class extends the NEM\Models\Model class
 * to provide with an integration of NEM's transactions.
 * 
 * The transaction class provides with an abstraction layer for
 * any typed transaction on the NEM network. Classes extending 
 * this one define specializations for transactions data on the
 * blockchain.
 * 
 * @internal This class should not be used directly by your
 *           program. Please have a look at the classes extending
 *           this one under \NEM\Models\Transaction\. For example
 *           \NEM\Models\Transaction\Transfer.
 * 
 * @link https://nemproject.github.io/
 */
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
    const VERSION_1_TEST = -1744830463;
    const VERSION_2_TEST = -1744830462;
    const VERSION_1_MIJIN = 0x60000000 | 1;
    const VERSION_2_MIJIN = 0x60000000 | 2;

    /**
     * List of valid Transaction Types on the NEM network.
     * 
     * @var array
     */
    static public $typesClassMap = [
        TransactionType::TRANSFER              => "\\NEM\\Models\\Transaction\\Transfer",
        TransactionType::IMPORTANCE_TRANSFER   => "\\NEM\\Models\\Transaction\\ImportanceTransfer",
        TransactionType::MULTISIG_MODIFICATION => "\\NEM\\Models\\Transaction\\MultisigAggregateModification",
        TransactionType::MULTISIG_SIGNATURE    => "\\NEM\\Models\\Transaction\\Signature",
        TransactionType::MULTISIG              => "\\NEM\\Models\\Transaction\\Multisig",
        TransactionType::PROVISION_NAMESPACE   => "\\NEM\\Models\\Transaction\\NamespaceProvision",
        TransactionType::MOSAIC_DEFINITION     => "\\NEM\\Models\\Transaction\\MosaicDefinition",
        TransactionType::MOSAIC_SUPPLY_CHANGE  => "\\NEM\\Models\\Transaction\\MosaicSupplyChange",
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
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "id"        => "int",
        "height"    => "int",
        "type"      => "int",
        "version"   => "int",
    ];

    /**
     * This contains the serialized copy of a transaction.
     * 
     * This array will be filled the first time you call the
     * serialize() method.
     * 
     * @var array
     */
    protected $serialized = null;

    /**
     * Class method to create a Transaction object out of
     * a DTO data set.
     * 
     * The `type` field is used to determine which class
     * must be loaded, see the static `typeClassMap` property
     * for details.
     */
    static public function create(array $data = null)
    {
        if (empty($data["type"]) && isset($data["transaction"])) {
            $type = $data["transaction"]["type"];
        }
        elseif (! $data || empty($data["type"])) {
            return new static($data);
        }
        else {
            $type = $data["type"];
        }

        // valid transaction type input
        $validTypes = array_keys(self::$typesClassMap);
        if (! $type || ! in_array($type, $validTypes)) {
            $type = TransactionType::TRANSFER;
        }

        // mutate transaction (morph specialized class)
        $classTx = self::$typesClassMap[$type];

        if ($type === TransactionType::TRANSFER
            && in_array($data["version"], [self::VERSION_2, self::VERSION_2_TEST, self::VERSION_2_MIJIN])) {

            $classTx = "\\NEM\\Models\\Transaction\\MosaicTransfer";
        }

        return new $classTx($data);
    }

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
     * The extendFee() method must be overloaded by any Transaction Type
     * which needs to extend the base FEE to a custom FEE.
     * 
     * For example MosaicTransfer transactions define a specific FEE or 
     * ImportanceTransfer, etc.
     *
     * @return array
     */
    public function extendFee()
    {
        return 0;
    }

    /**
     * The extendSerializeMeta() method must be overloaded by any Transaction Type
     * which needs to extend the base meta data serialization of Transaction with 
     * its own serialized meta data.
     *
     * @see \NEM\Models\Transaction\Transfer
     * @return array   Returns a byte-array with values in UInt8 representation.
     */
    public function extendSerializeMeta()
    {
        // Base Transaction does not specify any extension meta data
        // @see \NEM\Models\Transaction\Transfer
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
            "fee"       => $this->fee()->toMicro(),
        ];

        // extend entity data in sub class
        // @see \NEM\Models\Transaction\MosaicTransfer
        $meta = array_merge($baseMeta, $this->extendMeta());
        $entity = array_merge($baseEntity, $this->extend());

        // mosaics field is used to determine the version
        $versionByContent = !isset($entity["mosaics"]) ? self::VERSION_1 
                                                       : self::VERSION_2;

        // validate version field, should always reflect valid NIS tx version
        $version = isset($entity["version"]) ? $entity["version"] : $this->getAttribute("version");
        $versions = [
            self::VERSION_1,        self::VERSION_2,
            self::VERSION_1_TEST,   self::VERSION_2_TEST,
            self::VERSION_1_MIJIN,  self::VERSION_2_MIJIN
        ];
        if (! $version || !in_array($version, $versions)) {
            $version = $versionByContent;
        }

        // validate transaction type, should always be a valid type
        $type = isset($entity["type"]) ? $entity["type"] : $this->getAttribute("type");
        $validTypes = array_keys(self::$typesClassMap);
        if (! $type || ! in_array($type, $validTypes)) {
            $type = TransactionType::TRANSFER;
            $this->setAttribute("type", $type);
        }

        // deadline set to +1 hour if none set
        $deadline = $this->deadline()->toDTO();
        if (! $deadline || $deadline <= 0) {
            $txTime = $entity["timeStamp"];
            $deadline = $txTime + 3600;
            $this->setAttribute("deadline", $deadline);
        }

        // do we have optional fields
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
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Transaction* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $nisData = $this->toDTO("transaction");

        // shortcuts
        $serializer = $this->getSerializer();
        $output = [];

        // serialize all the base data
        $uint8_type = $serializer->serializeInt($nisData["type"]);
        $uint8_version = $serializer->serializeInt($nisData["version"]);
        $uint8_timestamp = $serializer->serializeInt($nisData["timeStamp"]);
        $uint8_signer = $serializer->serializeString(hex2bin($nisData["signer"]));
        $uint8_fee = $serializer->serializeLong($nisData["fee"]);
        $uint8_deadline = $serializer->serializeInt($nisData["deadline"]);

        // step 1: meta data at the beginning
        $output = array_merge(
            $uint8_type,
            $uint8_version,
            $uint8_timestamp,
            $uint8_signer);

        // step 2: serialize fee and deadline
        $output = array_merge($output,
            $uint8_fee,
            $uint8_deadline);

        // done with `base transaction data` serialization.
        return ($this->serialized = $output);
    }

    /**
     * Returns timestamp of the transaction.
     *
     * @return int
     */
     public function timeStamp($timestamp = null) 
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
        $dto = $payload ?: $this->getAttribute("message") ?: [];
        return new Message($dto);
    }
}
