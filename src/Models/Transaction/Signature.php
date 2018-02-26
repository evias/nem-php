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
namespace NEM\Models\Transaction;

use NEM\Models\Transaction;
use NEM\Models\Account;
use NEM\Models\TransactionType;
use NEM\Models\Fee;

/**
 * This is the Signature class
 *
 * This class extends the NEM\Models\Transaction class
 * to provide with an integration of NEM's signature 
 * transactions
 * 
 * @link https://nemproject.github.io/#multisigSignatureTransaction
 */
class Signature
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "otherHash" => "otherHash",
        "otherAccount" => "otherAccount",
    ];

    /**
     * Overload of the toDTO() logic to skip "meta" and "transaction"
     * sub-dto pairing.
     *
     * @return  array       Associative array containing a NIS *compliable* account representation.
     */
    public function toDTO($filterByKey = null)
    {
        $baseMeta = $this->meta();

        $baseEntity = [
            "timeStamp" => $this->timeStamp()->toDTO(),
            "fee"       => $this->fee()->toMicro(),
            "otherHash" => [
                "data" => $this->getAttribute("otherHash"),
            ],
            "otherAccount" => $this->otherAccount()->address()->toClean(),
            // transaction type specialization
            "type" => TransactionType::MULTISIG_SIGNATURE,
            "version" => $this->getAttribute("version") ?: Transaction::VERSION_1,
        ];

        // extend entity data in sub class
        // @see \NEM\Models\Transaction\MosaicTransfer
        $entity = array_merge($baseEntity, $this->extend());

        // deadline set to +1 hour if none set
        $deadline = $this->getAttribute("deadline");
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
        $entity["deadline"] = $deadline;
        return $entity;
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Signature transaction* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->toDTO();

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];
        $binHash    = hex2bin($nisData["otherHash"]["data"]);

        // serialize specialized fields
        $uint8_hashLen = $serializer->serializeInt(4 + strlen($binHash));
        $uint8_hash = $serializer->serializeString($binHash);
        $uint8_acct = $serializer->serializeString($nisData["otherAccount"]);

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_hashLen,
            $uint8_hash,
            $uint8_acct);

        // specialized data is concatenated to `base transaction data`.
        return ($this->serialized = array_merge($baseTx, $output));
    }

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO because it overloads the toDTO() method.
     *
     * @return array
     */
    public function extend() 
    {
        return [];
    }

    /**
     * The extendFee() method must be overloaded by any Transaction Type
     * which needs to extend the base FEE to a custom FEE.
     *
     * @return array
     */
    public function extendFee()
    {
        return Fee::SIGNATURE;
    }

    /**
     * Mutator for the `otherAccount` relationship.
     * 
     * @param   string      $address
     */
    public function otherAccount($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("otherAccount")]);
    }

    /**
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function signer($publicKey = null)
    {
        return new Account(["publicKey" => $publicKey ?: $this->getAttribute("publicKey")]);
    }
}
