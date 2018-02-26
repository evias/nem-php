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
namespace NEM\Core;

use NEM\Core\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encryption;
use NEM\Contracts\Serializable;
use NEM\Errors\NISInvalidSignatureContent;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519;
USE \SodiumException;

/**
 * This is the Serializer class
 *
 * This class provides the rules for low level serialization
 * of data for the NEM network.
 * 
 * Methods for serialization include `serializeInt()`, 
 * `serializeLong()`, `serializeString()` and `serializeUInt8()`.
 * You can also use the `aggregate()` method to merge multiple
 * UInt8 representation into one array, prefixed by its height.
 */
class Serializer
{

    /**
     * Sentinel value used to indicate that a serialized byte array 
     * should be deserialized as null.
     * 
     * @var integer
     */
    const NULL_SENTINEL = 0xffffffff;

    /**
     * The singleton instance
     * 
     * @var \NEM\Core\Serializer
     */
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
     * @param   array|string|integer|\NEM\Core\Serializer   $data
     * @return  array       Returns a byte-array with values in UInt8 representation.
     * @throws  RuntimeException    On unrecognized `data` argument.
     */
    public function serialize(/*mixed*/ $data) 
    {
        if (null === $data) {
            return $this->serializeInt(null);
        }
        elseif (is_array($data)) {
            return $this->serializeUInt8($data);
        }
        elseif (is_integer($data)) {
            return $this->serializeLong($data);
        }
        elseif (is_string($data)) {
            return $this->serializeString($data);
        }
        elseif ($data instanceof Serializable) {
            return $data->serialize();
        }

        throw new RuntimeException("Invalid parameter provided to \\NEM\\Core\\Serialize::serialize().");
    }

    /**
     * Internal method to serialize a decimal number into a
     * Integer on 4 Bytes.
     * 
     * This method is used in all other serializeX methods and
     * should not be used directly from outside the NEM SDK.
     * 
     * @internal This method should not be used directly
     * 
     * @param   integer     $number
     * @return  array       Returns a byte-array with values in UInt8 representation.
     */
    public function serializeInt(int $number = null)
    {
        if (null === $number) {
            return $this->serializeInt(self::NULL_SENTINEL);
        }
        else {
            $uint8 = [
                $number         & 0xff,
                ($number >> 8)  & 0xff,
                ($number >> 16) & 0xff,
                ($number >> 24) & 0xff
            ];
        }

        return $uint8;
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
     * @return  array       Returns a byte-array with values in UInt8 representation.
     */
    public function serializeString(string $str = null)
    {
        if (null === $str) {
            $uint8 = $this->serializeInt(null);
        }
        else {
            // prepend size on 4 bytes
            $count = strlen($str);
            $uint8 = $this->serializeInt($count);

            // UTF-8 to binary
            for ($i = 0; $i < $count; $i++) {
                $dec = Ed25519::chrToInt(substr($str, $i, 1));
                array_push($uint8, $dec);
            }
        }

        return $uint8;
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
     * @return  array       Returns a byte-array with values in UInt8 representation.
     */
    public function serializeUInt8(array $uint8Str = null)
    {
        if (null === $uint8Str) {
            $uint8 = $this->serializeInt(null);
        }
        else {
            // prepend size on 4 bytes
            $count = count($uint8Str);
            $uint8 = $this->serializeInt($count);

            for ($i = 0; $i < $count; $i++) {
                array_push($uint8, $uint8Str[$i]);
            }
        }

        return $uint8;
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
            // long on 8 bytes always
            $uint8 = array_merge($this->serializeInt(null), $this->serializeInt(0));
        }
        else {
            // prepend size on 4 bytes
            $uint64L = $this->serializeInt($long);
            $uint64H = $this->serializeInt($long >> 32);

            $uint8 = array_merge($uint64L, $uint64H);
            if (($len = count($uint8)) === 8) 
                // job done
                return $uint8;

            // right padding to 8 bytes
            for ($i = 0, $done = 8 - $len; $i < $done; $i++) {
                array_push($uint8, 0);
            }
        }

        return $uint8;
    }

    /**
     * This method lets you aggregate multiple serialized
     * byte arrays.
     * 
     * It will also prepend a size on 4 bytes representing
     * the *count of the merged/aggregated byte arrays*.
     * 
     * This method accepts *any* count of array arguments.
     * The passed array must contain UInt8 representation
     * of bytes (see serializeX methods).
     * 
     * @param   array   $uint8_1
     * @param   array   $uint8_2
     * @param   array   $uint8_X
     * @return  array
     */
    public function aggregate(/* [array $uint8_1, array $uint8_2, ...] */)
    {
        // read dynamic arguments
        $count = func_num_args();
        if (! $count) {
            return [];
        }

        // interpret dynamic arguments and concatenate
        // byte arrays into one aggregated byte array `concat`.
        $concat = [];
        $length = 0;
        for ($i = 0; $i < $count; $i++) {
            $argument = func_get_arg($i);
            if (! is_array($argument)) {
                // object is not serialized yet
                $argument = $this->serialize($argument);
            }

            $concat = array_merge($concat, $argument);
            $length += count($argument);
        }

        // prepend size on 4 bytes
        $output = $this->serializeInt($length);

        // append serialized parts
        $output = array_merge($output, $concat);
        return $output;
    }
}
