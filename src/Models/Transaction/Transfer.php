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
use NEM\Models\Fee;

/**
 * This is the Transfer class
 *
 * This class extends the NEM\Models\Transaction class
 * to provide with an integration of NEM's transfer 
 * transactions
 * 
 * @link https://nemproject.github.io/#transferTransaction
 */
class Transfer
    extends Transaction
{
    /**
     * The extend() method must be overloaded by any Transaction Type
     * which needs to extend the base DTO structure.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "amount"    => $this->amount()->toMicro(),
            "recipient" => $this->recipient()->address()->toClean(),
            "message"   => $this->message()->toDTO(),
            // transaction type specialization
            "type"      => TransactionType::TRANSFER,
        ];
    }

    /**
     * The extendMeta() method must be overloaded by any Transaction Type
     * which needs to extend the base META structure.
     *
     * @return array
     */
    public function extendMeta()
    {
        // Transfer transaction is *default transaction type* for NEM.
        // No data needs to be added to the base transaction META.
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
        // Transfer transaction is *default transaction type* for NEM.
        // No more fees need to be added to the base transaction FEE.
        return 0;
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Transfer* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->toDTO("transaction");

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        // serialize specialized fields
        $uint8_acct = $serializer->serializeString($nisData["recipient"]);
        $uint8_amt  = $serializer->serializeLong($nisData["amount"]);

        $messagePayload = $nisData["message"]["payload"];
        $messageType = $nisData["message"]["type"];

        // message payload is optional
        $uint8_msg   = [];
        if (!empty($messagePayload)) {
            $uint8_len  = $serializer->serializeInt(8 + strlen(hex2bin($messagePayload)));
            $uint8_type = $serializer->serializeInt($messageType);
            $uint8_hex  = $serializer->serializeString(hex2bin($messagePayload));

            $uint8_msg = array_merge($uint8_len, $uint8_type, $uint8_hex);
        }
        else { // empty message is 0 on-chain
             $uint8_msg = $serializer->serializeInt(0);
        }

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_acct,
            $uint8_amt,
            $uint8_msg);

        // specialized data is concatenated to `base transaction data`.
        return ($this->serialized = array_merge($baseTx, $output));
    }
}
