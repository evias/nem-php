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
use NEM\Models\TransactionType;
use NEM\Models\Account;
use NEM\Models\Fee;

/**
 * This is the NamespaceProvision class
 *
 * This class extends the NEM\Models\Transaction class
 * to provide with an integration of NEM's namespace 
 * provision transactions
 * 
 * @link https://nemproject.github.io/#provisionNamespaceTransaction
 */
class NamespaceProvision
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "rentalFeeSink"     => "transaction.rentalFeeSink",
        "rentalFee"         => "transaction.rentalFee",
        "parent"            => "transaction.parent",
        "newPart"           => "transaction.newPart",
    ];

    /**
     * Return specialized fields array for Namespace Provision Transactions.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "rentalFeeSink" => $this->rentalFeeSink()->address()->toClean(),
            "rentalFee" => empty($this->parent) ? Fee::ROOT_PROVISION_NAMESPACE : Fee::SUB_PROVISION_NAMESPACE,
            "parent" => $this->parent,
            "newPart" => $this->newPart,
            // transaction type specialization
            "type" => TransactionType::PROVISION_NAMESPACE,
        ];
    }

    /**
     * The extendFee() method must be overloaded by any Transaction Type
     * which needs to extend the base FEE to a custom FEE.
     *
     * @return array
     */
    public function extendFee()
    {
        return Fee::NAMESPACE_AND_MOSAIC;
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *NamespaceProvision* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->extend();

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        // serialize specialized fields
        $uint8_sink = $serializer->serializeString($nisData["rentalFeeSink"]);
        $uint8_rental = $serializer->serializeLong($nisData["rentalFee"]);
        $uint8_newPart = $serializer->serializeString($nisData["newPart"]);

        // empty parent is *null* on-chain
        $uint8_parent = $serializer->serializeInt(null);
        if (!empty($nisData["parent"])) {
            $uint8_parent = $serializer->serializeString($nisData["parent"]);
        }

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_sink,
            $uint8_rental,
            $uint8_newPart,
            $uint8_parent);

        // specialized data is concatenated to `base transaction data`.
        return ($this->serialized = array_merge($baseTx, $output));
    }

    /**
     * Mutator for the `rentalFeeSink` relation.
     *
     * @return  \NEM\Models\Account
     */
    public function rentalFeeSink($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("rentalFeeSink")]);
    }
}
