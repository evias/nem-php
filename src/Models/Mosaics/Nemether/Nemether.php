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
namespace NEM\Models\Mosaics\Dim;

use NEM\Models\MosaicDefinition;

class Nemether
    extends MosaicDefinition
{
    public $creator = "b089dc609b9ba65d9f5f3b5f58561ec5cc480f6e7eebf22b7ab88479f08706db";

    /**
     * Mutator for `mosaic` relation.
     *
     * This will return a NIS compliant [MosaicId](https://bob.nem.ninja/docs/#mosaicId) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `namespaceId` and `name`.
     * @return  \NEM\Models\Mosaic
     */
    public function id(array $mosaicId = null)
    {
        return new Mosaic($mosaicId ?: ["namespaceId" => "nemether", "name" => "nemether"]);
    }

    /**
     * Mutator for `levy` relation.
     *
     * This will return a NIS compliant [MosaicLevy](https://bob.nem.ninja/docs/#mosaicLevy) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `type`, `recipient`, `mosaicId` and `fee`.
     * @return  \NEM\Models\MosaicLevy
     */
    public function levy(array $levy = null)
    {
        $xem = new Mosaic(["namespaceId" => "nem", "name" => "xem"]);
        $data = $levy ?: [
            "type" => self::TYPE_ABSOLUTE,
            "fee" => 10,
            "recipient" => "NC56RYVRUPG3WRNGMVNRKODJZJNZKZYS76UAPO7K",
            "mosaicId" => $xem->toDTO(),
        ];

        return new MosaicLevy($data);
    }

    /**
     * Mutator for `properties` relation.
     *
     * This will return a NIS compliant collection of [MosaicProperties](https://bob.nem.ninja/docs/#mosaicProperties) object. 
     *
     * @param   array   $properties       Array of MosaicProperty instances
     * @return  \NEM\Models\MosaicProperties
     */
    public function properties(array $properties = null)
    {
        $data = $properties ?: [
            ["name" => "divisibility", "value" => 6],
            ["name" => "initialSupply", "value" => 95100000],
            ["name" => "supplyMutable", "value" => true],
            ["name" => "transferable", "value" => true],
        ];

        return MosaicProperties($data);
    }
}
