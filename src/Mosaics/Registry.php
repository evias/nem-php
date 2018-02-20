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
namespace NEM\Mosaics;

use NEM\Models\Mosaic;
use NEM\Models\MosaicAttachment;
use NEM\Models\MosaicDefinition;
use InvalidArgumentException;
use RuntimeException;

final class Registry
{
    /**
     * Array of MosaicDefinition instances containing pre-configured
     * NEM network mosaics.
     *
     * @var array
     */
    static protected $definitions;

    /**
     * Load a pre-configured mosaic definition or fetch it using the
     * Infrastructure service for Mosaics.
     *
     * @param   \NEM\Models\Mosaic|\NEM\Models\MosaicAttachment|string     $mosaic     The mosaic object or name for which to get the definition.
     * @param   string|integer                                             $network    Default is mainnet (104), testnet is -104 (0x98) and mijin is 96 (0x60)
     * @return  \NEM\Models\MosaicDefinition
     * @throws  \InvalidArgumentException       On invalid `mosaic` parameter.
     */
    static public function getDefinition($mosaic, $network = 0x68)
    {
        // read the mosaic's fully qualified name
        if ($mosaic instanceof Mosaic) {
            $fqn = $mosaic->getFQN();
        }
        elseif ($mosaic instanceof MosaicAttachment) {
            $fqn = $mosaic->mosaicId()->getFQN();
        }
        elseif (is_string($mosaic)) {
            $fqn = $mosaic;
        }
        else {
            throw new InvalidArgumentException("Unrecognized `mosaic` parameter to \\NEM\\Models\\Mosaics\\Registry::getDefinition().");
        }

        $preconfigClass = self::morphClass($fqn);
        if (false !== $preconfigClass && class_exists($preconfigClass)) {
            // Pre-Configured mosaic definition found
            return new $preconfigClass();
        }

        // no pre-configured mosaic definition class found, use NIS
        // Web service to read mosaic definition.

        //XXX
        return false;
        //throw new RuntimeException("Unsupported mosaic registry feature.");
    }

    /**
     * Helper to format a fully qualified mosaic name into a PHP class
     * namespaced.
     * 
     * Some of the mosaics present on the NEM network will be represented
     * by pre-configured classes in the SDK as such to give an idea on how
     * to pre-configure mosaic definition and also to allow reducing the 
     * amount of data that needs to be fetched over the network.
     * 
     * @param   string      $fqn
     * @return  string
     * @throws  \InvalidArgumentException       On invalid mosaic fully qualified name.
     */
    static public function morphClass($fqn)
    {
        $namespace = "\\NEM\\Mosaics";
        $classPath = [];

        // each namespace/sub-namespace has its own folder
        if ((bool) preg_match("/([^:]+):([^:]+)$/", $fqn, $classPath)) {

            $nsParts = explode(".", $classPath[1]); // $1 is namespace (before semi-colon ':')
            $className = ucfirst($classPath[2]); // $2 is mosaic name (after semi-colon ':')

            // format class name by mosaic fully qualified name.
            /**
             * @example FQN is `evias.sdk:nem-php`, this will be 
             * represented in a PSR-4 class scheme with the following path:
             * 
             * \NEM\Mosaics\Evias\Sdk\NemPhp.php
             */

            $transReg = "/[^a-zA-Z0-9]/";

            // format class path
            $classPath = array_map(function($item) use ($transReg) {
                // transformer for PSR-4 vali^ class/namespace format.

                $norm  = preg_replace($transReg, "_", $item);
                $upper = ucwords(str_replace("_", " ", strtolower($norm)));
                return str_replace(" ", "", $upper);
            }, array_merge($nsParts, [$className]));

            $preconfigured = $namespace . "\\" . implode("\\", $classPath);
            return $preconfigured;
        }

        return false;
    }
}
