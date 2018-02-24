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

use NEM\Models\MosaicAttachment;

class MosaicAttachments
    extends ModelCollection
{
    /**
     * Overload of the \NEM\Core\ModelCollection::serialize() method to provide
     * with a specialization for *MosaicAttachments Arrays* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // shortcuts
        $serializer = $this->getSerializer();

        $mapped = $this->map(function(&$attach) {
            return new MosaicAttachment($attach);
        });

        // sort attachments lexicographically
        $sorted = $mapped->sort(function($attach1, $attach2)
        {
            $lexic1 = $attach1->mosaicId()->getFQN() . " : " . $attach1->quantity;
            $lexic2 = $attach2->mosaicId()->getFQN() . " : " . $attach2->quantity;

            return $lexic1 < $lexic2 ? -1 : $lexic1 > $lexic2;
        })->values();

        // serialize attachments
        // prepend size on 4 bytes
        $prependSize = $serializer->serializeInt($sorted->count());

        // serialize each attachment
        $stateUInt8 = $prependSize;
        for ($i = 0, $len = $sorted->count(); $i < $len; $i++) {

            $attachment = $sorted->get($i);

            // use MosaicAttachment::serialize() specialization
            $uint8_attach = $attachment->serialize();

            // use merge here, no aggregator
            $stateUInt8 = array_merge($stateUInt8, $uint8_attach);
        }

        // no need to use the aggregator, we dynamically aggregated
        // our collection data and prepended the size on 4 bytes.
        return $stateUInt8;
    }
}
