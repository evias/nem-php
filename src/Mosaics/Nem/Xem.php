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
namespace NEM\Mosaics\Nem;

use NEM\Models\MosaicDefinition;
use NEM\Models\MosaicProperties;
use NEM\Models\MosaicProperty;
use NEM\Models\MosaicLevy;
use NEM\Models\Mosaic;

/**
 * This is the Nem\Xem class
 *
 * This class defines the parameters of mosaic
 * definition of the asset `nem:xem` on the
 * NEM Mainnet Network.
 * 
 * This mosaic is special on the NEM network as
 * it is also used for paying fees of transactions.
 * 
 * @link https://docs.nem.io
 */
class Xem
    extends MosaicDefinition
{
    /**
     * The `nem:xem` Total Coins Supply
     * 
     * @var integer
     */
    const TOTAL_SUPPLY = 8999999999;

    /**
     * The `nem:xem` mosaics creator public key
     * in hexadecimal format.
     * 
     * @var string
     */
    public $creator = "3e82e1c1e4a75adaa3cba8c101c3cd31d9817a2eb966eb3b511fb2ed45b8e262";

    /**
     * Overload of the getTotalSupply() method for fast
     * tracking with preconfigured mosaics.
     * 
     * @return integer
     */
    public function getTotalSupply()
    {
        return self::TOTAL_SUPPLY;
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
        return new Mosaic(["namespaceId" => "nem", "name" => "xem"]);
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
        return new MosaicLevy([]); // no levy on nem:xem
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
        $data = [
            new MosaicProperty(["name" => "divisibility", "value" => 6]),
            new MosaicProperty(["name" => "initialSupply", "value" => 8999999999]),
            new MosaicProperty(["name" => "supplyMutable", "value" => false]),
            new MosaicProperty(["name" => "transferable", "value" => true]),
        ];

        return new MosaicProperties($data);
    }
}
