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

use NEM\Models\Mutators\CollectionMutator;
use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicAttachments;
use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicDefinitions;
use NEM\Models\Fee;

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
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "mosaics" => $this->mosaics()->toDTO(),
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
        $definitions = MosaicDefinitions::create();
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
