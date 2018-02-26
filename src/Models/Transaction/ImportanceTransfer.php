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
 * This is the ImportanceTransfer class
 *
 * This class extends the NEM\Models\Transaction class
 * to provide with an integration of NEM's importance 
 * transfer transactions.
 * 
 * @link https://nemproject.github.io/#importanceTransferTransaction
 */
class ImportanceTransfer
    extends Transaction
{
    /**
     * NIS Delegated Harvesting modes.
     * 
     * @var integer
     */
    const MODE_ACTIVATE   = 1;
    const MODE_DEACTIVATE = 2;

    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "remoteAccount" => "transaction.remoteAccount",
        "mode"          => "transaction.mode",
    ];

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *ImportanceTransfer* serialization.
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
        $uint8_mode = $serializer->serializeInt($nisData["mode"]);
        $uint8_acct = $serializer->serializeString(hex2bin($nisData["remoteAccount"]));

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_mode,
            $uint8_acct);

        // specialized data is concatenated to `base transaction data`.
        return ($this->serialized = array_merge($baseTx, $output));
    }

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        // set default mode in case its invalid
        $mode = $this->getAttribute("mode");
        if (! $mode || ! in_array($mode, [self::MODE_ACTIVATE, self::MODE_DEACTIVATE])) {
            $mode = self::MODE_ACTIVATE;
            $this->setAttribute("mode", $mode);
        }

        return [
            "remoteAccount" => $this->remoteAccount()->publicKey,
            "mode" => $this->mode,
            // transaction type specialization
            "type"      => TransactionType::IMPORTANCE_TRANSFER,
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
        return Fee::IMPORTANCE_TRANSFER;
    }

    /**
     * Mutator for the `remoteAccount` relationship.
     * 
     * @param   string      $pubKey
     */
    public function remoteAccount($pubKey = null)
    {
        return new Account(["publicKey" => $pubKey ?: $this->getAttribute("remoteAccount")]);
    }
}
