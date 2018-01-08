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
namespace NEM\Infrastructure;

use NEM\Models\Address;
use NEM\Errors\NISInvalidAddressFormat;
use NEM\Errors\NISInvalidNetworkId;

class Network
    extends Service
{
    /**
     * Array of available networks by name.
     *
     * @var array 
     */
    static public $networkInfos = [
        "mainnet" => [
            "id"   => 104,
            "hex"  => "68", // N
            "char" => 'N',
        ],
        "testnet" =>  [
            "id"   => -104,
            "hex"  => "98", // T
            "char" => 'T',
        ],
        "mijin"   =>  [
            "id"   => 96,
            "hex"  => "60", // M
            "char" => 'N',
        ],
    ];

    /**
     * Load a NetworkInfo object from an `address`.
     *
     * @param   string|\NEM\Models\Address  $address
     * @return  \NEM\Models\Model
     * @throws  \NEM\Errors\NISInvalidAddressFormat     On invalid address format or unrecognized address first character.
     */
    static public function fromAddress($address)
    {
        if ($address instanceof Address) {
            $addr = $address->toClean();
            $prefix = substr($addr, 0, 1);
        }
        elseif (is_string($address)) {
            $prefix = substr($address, 0, 1);
        }
        else {
            throw new NISInvalidAddressFormat("Could not identify address format: " . var_export($address, true));
        }

        foreach (self::$networkInfos as $name => $spec) {
            $netChar = $spec['char'];

            if ($prefix == $netChar)
                return $this->createBaseModel($spec);
        }

        throw new NISInvalidAddressFormat("Could not identify network from provided address: " . var_export($address, true));
    }

    /**
     * Helper to get a network address prefix hexadecimal representation
     * from a network id.
     *
     * @param   integer     $networkId
     * @return  string 
     */
    static public function getPrefixFromId($networkId)
    {
        foreach (self::$networkInfos as $name => $spec) {
            if ($networkId === $spec['id'])
                return $spec['hex'];
        }

        throw new NISInvalidNetworkId("Network Id '" . $networkId . "' is invalid.");
    }
}
