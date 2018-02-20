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

class MosaicTransfer
    extends Transfer
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "mosaics",
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
            "mosaics" => $this->mosaics(),
        ];
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
        $attachments = $data ?: $this->getAttribute("mosaics") ?: [];
        return (new CollectionMutator())->mutate("mosaicAttachments", $attachments);
    }
}
