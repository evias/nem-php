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

use \NEM\Models\Mosaic;
use \NEM\Mosaics\Registry;

class MosaicDefinitions
    extends ModelCollection
{
    /**
     * Class method to create a MosaicDefinitions *array*.
     * 
     * This array will *always* contain MosaicDefinition objects
     * for the mosaics that are pre-configured.
     * 
     * @return \NEM\Models\MosaicDefinitions
     */
    static public function create()
    {
        //XXX check and test whether we want to include *all* preconfigured
        //    classes or do that on a dynamic level
        return new static([
            Registry::getDefinition("nem:xem")
        ]);
    }

    /**
     * This method will find a said `MosaicDefinition` for the
     * given `mosaic` in the current definitions collection.
     * 
     * @param   string|\NEM\Models\Mosaic   $mosaic
     * @return  false|\NEM\Models\MosaicDefinition
     */
    public function getDefinition($mosaic)
    {
        if (! $this->count()) {
            return false;
        }

        $mosaic = $this->prepareMosaic($mosaic);
        foreach ($this->all() as $definition) {
            if ($mosaic->getFQN() != $definition->id()->getFQN()) {
                continue;
            }

            return $definition;
        }

        return false;
    }

    /**
     * Internal helper to wrap a mosaic FQN into a
     * \NEM\Models\Mosaic instance.
     * 
     * @param   string|\NEM\Models\Mosaic   $mosaic
     * @return  \NEM\Models\Mosaic
     */
    protected function prepareMosaic($mosaic)
    {
        if ($mosaic instanceof Mosaic) {
            return $mosaic;
        }
        elseif (is_string($mosaic)) {
            return Mosaic::create($mosaic);
        }

        throw new InvalidArgumentException("Unsupported mosaic argument type provided to \\NEM\\Models\\MosaicDefinitions: ", var_export($mosaic));
    }
}
