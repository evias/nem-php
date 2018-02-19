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
use NEM\Models\Mosaics\Registry;

use RuntimeException;

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
     * NEM network maximum Fee Amount.
     * 
     * This amount is multiplied by `FEE_FACTOR` during
     * result computation.
     *
     * @internal
     * @var integer
     */
    public const MAX_AMOUNT_FEE = 25;

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
     * This method can be used to calculate all needed fees for a given
     * `transaction` object.
     *
     * @param   \NEM\Models\Transaction     $transaction
     * @return  \NEM\Models\Fee
     */
    static public function calculateForTransaction(Transaction $transaction)
    {
        // generate each content fee accordingly
        $msgFee = self::calculateForMessage($transaction->message());

        // default content fee is XEM amount transfer fee
        $contentFee = Fee::FEE_FACTOR * self::calculateForXEM($transaction->amount()->toMicro() / Amount::XEM);

        // version 2 transaction fees prevail over version 1 (no mosaics)
        if ($transaction instanceof \NEM\Models\Transaction\MosaicTransfer) {
            $contentFee = self::calculateForMosaics($transaction->mosaics(), $transaction->amount()->toMicro());
        }

        return floor(($msgFee + $contentFee) * Amount::XEM);
    }

    /**
     * Calculate the needed fee for a provided `$message` message.
     *
     * Messages are more expensive when they are encrypted.
     *
     * This method is used internally to calculate a transactions's
     * message fees.
     *
     * @internal
     * @param   Message     $message
     * @return  \NEM\Models\Fee
     */
    static public function calculateForMessage(Message $message)
    {
        $dto = $message->toDTO();

        if (empty($dto["payload"]))
            return 0;

        // message fee is 0.05 (current fee factor) multiplied
        // by the count of *started* 31 characters chunks.
        $chunks = floor((strlen($dto["payload"]) / 2) / 32);
        return Fee::FEE_FACTOR * ($chunks + 1);
    }

    /**
     * Calculate the needed fee for a provided `$mosaics` mosaics
     * attachments array.
     *
     * This method is used internally to calculate a mosaic transfer
     * transaction's needed fees.
     * 
     * The `definitions` argument should contain definitions of attached
     * mosaics.
     *
     * @internal
     * @param   \NEM\Models\MosaicDefinitions   $definitions    Collection of MosaicDefinition objects.
     * @param   \NEM\Models\MosaicAttachments   $attachments    Collection of MosaicAttachment objects.
     * @param   integer                         $multiplier
     * @return  \NEM\Models\Fee
     * @throws  RuntimeException                                On missing MosaicDefinition in `definitions` argument.
     */
    static public function calculateForMosaics(MosaicDefinitions $definitions,
                                               MosaicAttachments $attachments,
                                               $multiplier = Amount::XEM)
    {
        if ($attachments->isEmpty())
            return 0;

        $totalFee = 0;
        foreach ($attachments as $attachment) {
            $mosaicFQN = $attachment->mosaicId()->getFQN();

            // in case user didnt provide the definition, try to
            // to find it with the registry of preconfigured Mosaics classes.
            $definition = $definitions->getDefinition($attachment->mosaicId())
                        ?: Registry::getDefinition($mosaicFQN);

            if ($definition === false) {
                throw new RuntimeException("Missing MosaicDefinition for Mosaic: `" . $fqn . "`.");
            }

            // read properties for calculations
            $divisibility = $definition->getProperty("divisibility") ?: 0;
            $supply   = $definition->getTotalSupply() ?: Amount::XEM_SUPPLY;
            $quantity = $attachment->quantity;
            $supplyAdjust = 0;

            // small business mosaic fee
            if ($supply <= 10000 && $divisibility === 0) {
                $fee = Fee::FEE_FACTOR; // 0.05
            }
            // all other mosaics are first converted to XEM amounts
            else {
                $maxQuantity   = Amount::MAX_AMOUNT; // 9_000_000_000_000_000
                $totalQuantity = $supply * pow(10, $divisibility);
                $supplyAdjust  = floor(0.8 * log($maxQuantity / $totalQuantity));

                $xemAmount = Amount::mosaicQuantityToXEM($divisibility,
                                                         $supply,
                                                         $quantity,
                                                         $multiplier);

                // mosaic fee is calculate the same a XEM amounts after being converted.
                $fee = self::calculateForXEM(ceil($xemAmount));
            }

            // add current mosaic attachment's fee
            $totalFee += Fee::FEE_FACTOR * max([1, $fee - $supplyAdjust]);
        }

        return $totalFee;
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
    static public function calculateForXEM($amountXEM = Amount::XEM)
    {
        $fee = floor(max([1, $amountXEM / 10000]));
        return $fee > self::MAX_AMOUNT_FEE ? self::MAX_AMOUNT_FEE : $fee;
    }
}
