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

use NEM\Models\TransactionType;
use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicDefinitions;
use NEM\Models\Fee;
use NEM\Models\Transaction;
use NEM\Infrastructure\Network;

/**
 * This is the MosaicTransfer class
 *
 * This class extends the NEM\Models\Transfer class
 * to provide with an integration of NEM's mosaic 
 * transfer transactions (version 2 transactions).
 * 
 * @link https://nemproject.github.io/#transferTransaction
 */
class MosaicTransfer
    extends Transfer
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "mosaics" => "transaction.mosaics",
    ];

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Transfer* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @see \NEM\Models\Transaction\Transfer::serialize()
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // @see NEM\Models\Transaction\Transfer::serialize()
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->extend();

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        // serialize specialized fields
        $uint8_mosaics = $this->mosaics()->serialize();
        return ($this->serialized = array_merge($baseTx, $uint8_mosaics));
    }

    /**
     * The MosaicTransfer transaction type adds the `mosaics` offset
     * to the Transaction DTO and also adds all fields defined in the
     * Transfer::extend() overload.
     *
     * @return array
     */
    public function extend() 
    {
        $version = $this->getAttribute("version");
        $twoByOld = [
            Transaction::VERSION_1       => Transaction::VERSION_2,
            Transaction::VERSION_1_TEST  => Transaction::VERSION_2_TEST,
            Transaction::VERSION_1_MIJIN => Transaction::VERSION_2_MIJIN,
        ];

        // MosaicTransfer always use *VERSION 2 TRANSACTIONS*.
        // this small block will make sure to stay on the correct
        // network in case a version was set before.

        if (in_array($version, array_keys($twoByOld))) {
            // switch to v2
            $version = $twoByOld[$version];
        }
        elseif (!$version || !in_array($version, array_values($twoByOld))) {
            // invalid version provided, set default
            $version = Transaction::VERSION_2_TEST;
        }

        return [
            "amount"    => $this->amount()->toMicro(),
            "recipient" => $this->recipient()->address()->toClean(),
            "message"   => $this->message()->toDTO(),
            "mosaics"   => $this->mosaics()->toDTO(),
            // transaction type specialization
            "type"      => TransactionType::TRANSFER,
            "version"   => $version,
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
        // identify network
        $address = $this->recipient()->address();

        $networkId = 104;
        if (!empty($address->toClean()))
            $networkId = Network::fromAddress($address);

        // load definitions for attached mosaics.
        $definitions = MosaicDefinitions::create($this->mosaics, $networkId);

        // calculate fees for mosaics
        $mosaicsFee = Fee::calculateForMosaics(
                                $definitions,
                                $this->mosaics(),
                                $this->amount()->toMicro());

        return $mosaicsFee;
    }

    /**
     * Mutator for the mosaic attachment object collection.
     *
     * Returns a collection object of class \NEM\Models\MosaicAttachments.
     * Note the plural form of the class as the plural form describes a 
     * collection of MosaicAttachment entries.
     *
     * @return \NEM\Models\MosaicAttachments    Collection of MosaicAttachment objects.
     */
    public function mosaics(array $data = null)
    {
        return new MosaicAttachments($data ?: $this->getAttribute("mosaics"));
    }

    /**
     * Helper to easily attach mosaics to the attachments.
     * 
     * @param   string|\NEM\Models\Mosaic|\NEM\Models\MosaicAttachment  $mosaic
     * @param   null|integer                                            $quantity
     * @return \NEM\Models\Transaction\MosaicTransfer
     */
    public function attachMosaic($mosaic, $quantity = null)
    {
        $attachment = $this->prepareAttachment($mosaic);
        $actualAmt  = $attachment->quantity ?: $quantity ?: 0;
        $attachment->setAttribute("quantity", $actualAmt);

        // push to internal storage
        $attachments = $this->getAttribute("mosaics") ?: [];
        array_push($attachments, $attachment);

        $this->setAttribute("mosaics", $attachments);
        return $this;
    }

    /**
     * Helper to prepare a MosaicAttachment object out of any one of string,
     */
    protected function prepareAttachment($mosaic)
    {
        if ($mosaic instanceof MosaicAttachment) {
            return $mosaic;
        }
        elseif ($mosaic instanceof MosaicAttachments) {
            return $mosaic->shift();
        }
        elseif ($mosaic instanceof Mosaic) {
            return new MosaicAttachment(["mosaicId" => $mosaic->toDTO()]);
        }
        elseif ($mosaic instanceof MosaicDefinition) {
            return new MosaicAttachment(["mosaicId" => $mosaic->id()->toDTO()]);
        }
        elseif (is_string($mosaic)) {
            return $this->prepareAttachment(Mosaic::create($mosaic));
        }

        throw new InvalidArgumentException("Unrecognized mosaic parameter type passed to \\NEM\\Models\\Transaction\\MosaicTransfer::attachMosaic().");
    }
}
