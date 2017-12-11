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

use NEM\Models\Transaction;

class Fee
    extends Amount
{
    /**
     * NEM network current fee factor.
     *
     * @internal
     * @var integer
     */
    public const FEE_FACTOR = 0.05;

    /**
     * NEM network transaction fee.
     *
     * @internal
     * @var integer
     */
    public const TRANSACTION_FEE = 3;

    /**
     * NEM Multisig Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const MULTISIG = (Fee::TRANSACTION_FEE * Fee::FEE_FACTOR) * Amount::XEM;

    /**
     * NEM Mosaic AND Namespaces common Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const NAMESPACE_AND_MOSAIC = (Fee::TRANSACTION_FEE * Fee::FEE_FACTOR) * Amount::XEM;

    /**
     * NEM Root Namespace Provision Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const ROOT_PROVISION_NAMESPACE = 100 * Amount::XEM;

    /**
     * NEM Sub Namespace Provision Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const SUB_PROVISION_NAMESPACE = 10 * Amount::XEM;

    /**
     * NEM Mosaic Definition Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const MOSAIC_DEFINITION = 10 * Amount::XEM;

    /**
     * NEM Multisig Signature Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const SIGNATURE = (Fee::TRANSACTION_FEE * Fee::FEE_FACTOR) * Amount::XEM;

    /**
     * NEM Importance Transfer Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const IMPORTANCE_TRANSFER = (Fee::TRANSACTION_FEE * Fee::FEE_FACTOR) * Amount::XEM;

    /**
     * NEM Multisig Aggregate Modification Transaction Fee (in microXEM).
     *
     * @internal
     * @var integer
     */
    public const MULTISIG_AGGREGATE_MODIFICATION = (10 * Fee::FEE_FACTOR) * Amount::XEM;

    /**
     * Calculate the needed fee for a provided `$transaction` NEM transaction
     * object.
     *
     * @param   \NEM\Models\Transaction     $transaction
     * @return  \NEM\Models\Fee
     */
    static public function calculateForTransaction(Transaction $transaction)
    {
        return 0.00;
    }

    /**
     * Calculate the needed fee for a provided `$message` message.
     *
     * Messages are more expensive when they are encrypted.
     *
     * @param   string     $message
     * @return  \NEM\Models\Fee
     */
    static public function calculateForMessage($message)
    {
        return 0.00;
    }

    /**
     * Calculate the needed fee for a provided `$mosaics` mosaics
     * attachments array.
     *
     * @param   string     $message
     * @return  \NEM\Models\Fee
     */
    static public function calculateForMosaics(array $mosaics, $multiplier = Amount::XEM)
    {
        return 0.00;
    }

    /**
     * Calculate the minimum needed fee for a provided `$amountXEM` amount
     * of XEM to transfer.
     * 
     * This method is used internally to calculate a transaction's base fee.
     *
     * @internal
     * @param   string     $message
     * @return  \NEM\Models\Fee
     */
    static public function calculateMinimum($amountXEM = Amount::XEM)
    {
        return 0.00;
    }
}
