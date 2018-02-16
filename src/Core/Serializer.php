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
     * Serialize any input for the NEM network.
     * 
     * @param   string          $data
     * @return  array
     */
    public function serialize(/*mixed*/ $data) 
    {
        if (is_array($data)) {
            return $this->serializeUInt8($data);
        }
        elseif (is_integer($data)) {
            return $this->serializeLong($data);
        }
        elseif (is_string($data)) {
            return $this->serializeString($data);
        }
        //XXX serializeTransaction
    }

    /**
     * Serialize string data. The serialized string will be prefixed
     * with a 4-bytes long Size field followed by the UInt8 representation
     * of the given `str`.
     * 
     * Takes in a string and returns an array of Unsigned Integers 
     * on 8-bits.
     * 
     * @internal This method is used internally
     * @param   string  $str
     * @return  array
     */
    public function serializeString(string $str = null)
    {
        if (null === $str) {
            $binary = Buffer::fromInt(0xffffffff, 4)->getBinary();
        }
        else {
            $count = strlen($str);
            $binary = Buffer::fromInt($count, 4, null, Buffer::PAD_RIGHT)->getBinary();

            for ($i = 0; $i < $count; $i++) {
                $char = Ed25519::intToChr(
                            Ed25519::chrToInt((substr($str, $i, 1))));
                $binary .= $char;
            }
        }

        $buffer = new Buffer($binary, strlen($binary));
        return $buffer->toUInt8();
    }

    /**
     * Serialize unsigned char data. The serialized string will be prefixed
     * with a 4-bytes long Size field followed by the given `str`.
     * 
     * Takes in a 8bit-string and returns an array of Unsigned Integers 
     * on 8-bits.
     * 
     * @internal This method is used internally
     * @param   string  $str
     * @return  array
     */
    public function serializeUInt8(array $uint8Str = null)
    {
        if (null === $uint8Str) {
            $binary = Buffer::fromInt(0xffffffff, 4)->getBinary();
        }
        else {
            $count = count($uint8Str);
            $binary = Buffer::fromInt(count($uint8Str), 4, null, Buffer::PAD_RIGHT)->getBinary();

            for ($i = 0; $i < count($uint8Str); $i++) {
                $char = Ed25519::intToChr($uint8Str[$i]);
                $binary .= $char;
            }
        }

        $buffer = new Buffer($binary, strlen($binary));
        return $buffer->toUInt8();
    }

    /**
     * Serialize UInt64 numbers. This corresponds to the `long` variable type
     * in C.
     * 
     * @param   integer     $long
     * @return  array
     */
    public function serializeLong(int $long = null)
    {
        if (null === $long) {
            $binary = Buffer::fromInt(0, 8)->getBinary();
        }
        else {
            // low part
            $binary = Buffer::fromInt($long, null, null, Buffer::PAD_RIGHT)->getBinary();
            if (($len = strlen($binary)) < 4)
                $binary = $binary . str_repeat("\0", 4 - $len);

            // high part
            $oflow = floor($long / 0x100000000);
            $binary .= Buffer::fromInt($oflow, 4, null, Buffer::PAD_RIGHT)->getBinary();
        }

        $buffer = new Buffer($binary, strlen($binary));
        return $buffer->toUInt8();
    }
}
