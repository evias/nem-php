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
namespace NEM\Core;

use NEM\Core\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encryption;
use NEM\Errors\NISInvalidSignatureContent;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519;
USE \SodiumException;

class Serializer
{
    static protected $_instance = null;

    /**
     * Singleton instance getter.
     * 
     * @return \NEM\Core\Serializer
     */
    static public function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new self();
        }

        return static::$_instance;
    }

    /**
     * Serializer object constructor.
     * 
     * This constructor is private because the serializer instance
     * should be pre-configured to allow easier usage.
     * 
     * @see getInstance
     * @return  void
     * @throws  \NEM\Errors\NISInvalidSignatureContent      On invalid `data` argument. Should be a \NEM\Core\Buffer or a string.
     */
    protected function __construct()
    {
    }

    /**
     * Serialize string data. The serialized string will be prefixed
     * with a 4-bytes long Size field followed by the UInt8 representation
     * of the given `str`.
     * 
     * Returns an array of Unsigned Integers on 8-bits.
     * 
     * @internal This method is used internally
     * @param   string  $str
     * @return  array
     */
    public function serializeString($str)
    {
        if (null === $str) {
            $binary = Buffer::fromInt(0xffffffff, 4)->getBinary();
        }
        else {
            $binary = Buffer::fromInt(strlen($str), 4, null, Buffer::PAD_RIGHT)->getBinary();

            for ($i = 0; $i < strlen($str); $i++) {
                $char = Ed25519::intToChr(Ed25519::chrToInt((substr($str, $i, 1))));
                $binary .= $char;
            }
        }

        $buffer = new Buffer($binary, strlen($binary));
        return $buffer->toUInt8();
    }
}
