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

use \NEM\Models\Mosaic;
use \NEM\Models\MosaicDefinition;
use \NEM\Mosaics\Registry;
use \NEM\Infrastructure\Mosaic as MosaicService;
use \NEM\Infrastructure\ConnectionPool;

/**
 * This is the MosaicDefinitions class
 *
 * This class extends the NEM\Models\ModelCollection class
 * to provide with an integration of NEM's mosaic 
 * definition lists (not transaction).
 * 
 * @link https://nemproject.github.io/#mosaicDefinition
 */
class MosaicDefinitions
    extends ModelCollection
{
    static public $definitions = null;

    /**
     * Class method to create a MosaicDefinitions *array*.
     * 
     * This array will *always* contain MosaicDefinition objects
     * for the mosaics that are pre-configured.
     * 
     * @return \NEM\Models\MosaicDefinitions
     */
    static public function create(MosaicAttachments $mosaics = null, $networkId = 104)
    {
        if (! self::$definitions) {
            self::$definitions = new static([
                Registry::getDefinition("nem:xem")
            ]);
        }

        if ($mosaics === null) {
            return self::$definitions; // only contains nem:xem
        }

        $object = new static;

        // for each attached mosaic, we need the mosaic definition
        foreach ($mosaics as $attachment) {

            $mosaicId = is_array($attachment) ? $attachment["mosaicId"] : $attachment->mosaicId();
            $mosaic = $object->prepareMosaic($mosaicId);
            $definition = self::$definitions->getDefinition($mosaic);

            if (false !== $definition) {
                // definition found for current attached mosaic
                continue;
            }

            // try to use Registry
            $definition = Registry::getDefinition($mosaic);
            if (false !== $definition) {
                // mosaic definition *morphed* with Registry.
                self::$definitions->push($definition);
                continue;
            }

            // need network fetch

            // all definitions fetched will be stored in `self::$definitions`
            $definition = self::fetch($mosaic, $networkId);

            if (false === $definition) {
                throw new \InvalidArgumentException("Mosaic '" . $mosaic->getFQN() . "' does not exist on network: " . $networkId);
            }
        }

        return self::$definitions;
    }

    /**
     * 
     */
    static public function fetch(Mosaic $mosaic, $networkId = 104) 
    {
        if (! self::$definitions) {
            self::$definitions = new static([
                Registry::getDefinition("nem:xem")
            ]);
        }
        elseif (false !== ($definition = self::$definitions->getDefinition($mosaic))) {
            return $definition;
        }

        // use NEM network to fetch mosaic definition page(s)
        $pool    = new ConnectionPool($networkId);
        $service = new MosaicService($pool->getEndpoint());
        $namespace = $mosaic->getAttribute("namespaceId");

        // start with null
        $lastId = null;
        do {
            // each mosaic definition page may hold up to 50
            // mosaic definitions. We need to iterate through
            // *each page* and also *each definition* to find 
            // the definition for our said `mosaic`.

            $fetchDefs = $service->getMosaicDefinitionsPage($namespace, $lastId, 50);
            foreach ($fetchDefs as $currentDef) {

                // cache mosaic definitions
                $mosDefinition = new MosaicDefinition($currentDef->mosaic);
                self::$definitions->push($mosDefinition);

                if ($mosDefinition->id()->getFQN() === $mosaic->getFQN()) {
                    return $mosDefinition;
                }

                $lastId = $currentDef->meta->id;
            }

            $cntFound = $fetchDefs->count();
        }
        while ($cntFound > 0);

        return false;
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
        elseif (is_array($mosaic)) {
            $fqmn = $mosaic["namespaceId"] . ":" . $mosaic["name"];
            return Mosaic::create($fqmn);
        }

        throw new InvalidArgumentException("Unsupported mosaic argument type provided to \\NEM\\Models\\MosaicDefinitions: ", var_export($mosaic));
    }
}
