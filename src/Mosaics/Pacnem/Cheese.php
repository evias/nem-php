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
namespace NEM\Mosaics\Pacnem;

use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicProperties;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicLevy;
use NEM\Models\Mosaic;

/**
 * This is the Pacnem\Cheese class
 *
 * This class defines the parameters of mosaic
 * definition of the asset `pacnem:cheese` on the
 * NEM Testnet Network.
 * 
 * The `pacnem:cheese` asset is integrated as a 
 * Score Token in the PacNEM game. Any player reaching
 * the High Score top 10 list will be rewarded with the
 * equivalent amount of tokens.
 * 
 * @link https://www.pacnem.com
 */
class Cheese
    extends MosaicDefinition
{
    public $creator = "d33a1f38cb1241f77d3786a2c8547b894ec903a864ae745bd628b81b0c35deec";

    /**
     * Overload of the getTotalSupply() method for fast
     * tracking with preconfigured mosaics.
     * 
     * @return integer
     */
    public function getTotalSupply()
    {
        //XXX mutable supply
        return 290888;
    }

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
        return new Mosaic($mosaicId ?: ["namespaceId" => "pacnem", "name" => "cheese"]);
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
            "type" => MosaicLevy::TYPE_PERCENTILE,
            "fee" => 100,
            "recipient" => "NDHGYUVXKUWYFNO6THLUKAF6ZH2WIDCC6XD5UPC4",
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
            new MosaicProperty(["name" => "divisibility", "value" => 6]),
            new MosaicProperty(["name" => "initialSupply", "value" => 290888]),
            new MosaicProperty(["name" => "supplyMutable", "value" => true]),
            new MosaicProperty(["name" => "transferable", "value" => true]),
        ];

        return new MosaicProperties($data);
    }
}
