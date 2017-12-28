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
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Core;

use NEM\Contracts\Serializable;
use InvalidArgumentException;
use RuntimeException;

class Encoder
{
    /**
     * Encode a UInt8 array to its bytes representation.
     *
     * @param   array   $uint8      UInt8 array to convert to its bytes representation.
     * @return  string              Byte-level representation of the UInt8 array.
     */
    public function ua2bin(array $uint8)
    {
        $bin = "";
        foreach ($uint8 as $ix => $char) {
            $buf = Buffer::fromInt($char, 1);
            $bin .= $buf->getBinary();
        }

        $buffer = new Buffer($bin);
        return $buffer;
    }

    /**
     * Convert a UInt8 array to a Int32 array (WordArray).
     *
     * The returned array contains entries of type Int32.
     *
     * @param   array   $uint8      UInt8 array to convert to Int32 array.
     * @return  array               Array of Int32 representations.
     */
    public function ua2words(array $uint8)
    {
        $uint8 = $uint8;
        $int32 = [];
        // 4 bytes in a row ! => 4 times uint8 is an int32.
        for ($i = 0, $bytes = count($uint8); $i < $bytes; $i += 4) {

            $b1 = $uint8[$i];
            $b2 = isset($uint8[$i+1]) ? $uint8[$i+1] : 0;
            $b3 = isset($uint8[$i+2]) ? $uint8[$i+2] : 0;
            $b4 = isset($uint8[$i+3]) ? $uint8[$i+3] : 0;

            // (byte_1 * 16777216) + (byte_2 * 65536) + (byte_3 * 256) + byte_4
            $i32 = $b1 * 0x1000000 + $b2 * 0x10000 + $b3 * 0x100 + $b4;

            // negative amounts are represented in [0, 2147483647] range
            // 0x100000000 is double of 2147483647 minus 2.
            $signed = $i32 > 0x7fffffff ? $i32 - 0x100000000 : $i32;

            array_push($int32, $signed);
        }

        return $int32;
    }

    /**
     * Encode a UInt8 array (WordArray) to its hexadecimal representation.
     *
     * @param   array   $uint8      UInt8 array to convert to Int32 array.
     * @return  string              Hexadecimal representation of the UInt8 array.
     */
    public function ua2hex(array $uint8)
    {
        $unsignedRShift = function($a, $b)
        {
            if($b == 0) return $a;
            return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
        };

        $uint8 = $uint8 ?: $this->toUInt8();
        $hex = "";
        $enc = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
        for ($i = 0, $bytes = count($uint8); $i < $bytes; $i++) {
            $code = $uint8[$i];

            $hex .= $enc[$unsignedRShift($code, 4)];
            $hex .= $enc[($code & 0x0f)];
        }

        return $hex;
    }

    /**
     * Convert a Int32 array (WordArray) to a UInt8 array.
     *
     * The returned array contains entries of type UInt8.
     * 
     * @param   array   $words      Int32 array (WordArray) to convert to UInt8 array.
     * @return  array               Array of UInt8 representations.
     */
    public function words2ua(array $words)
    {
        $unsignedRShift = function($a, $b)
        {
            if($b == 0) return $a;
            return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
        };

        $int32 = $words;
        $uint8 = [];
        for ($i = 0, $words = count($int32); $i < $words; $i += 4) {
            $v = $int32[$i / 4];
            if ($v < 0) $v += 0x100000000; // 4294967296

            $uint8[$i] = $unsignedRShift($v, 24);
            $uint8[$i + 1] = ($unsignedRShift($v, 16)) & 0xff; // ff=255
            $uint8[$i + 2] = ($unsignedRShift($v, 8)) & 0xff;
            $uint8[$i + 3] = $v & 0xff;
        }

        return $uint8;
    }

    /**
     * Encode a Int32 array (WordArray) to its hexadecimal representation.
     *
     * @param   array   $words      Int32 array (WordArray) to convert to UInt8 array.
     * @return  string              Hexadecimal representation of the WordArray
     */
    public function words2hex(array $words)
    {
        $int32 = $words ?: $this->ua2words();
        $sigBytes = count($int32) * 4;
        $hex = [];

        for ($i = 0; $i < $sigBytes; $i++) {
            $byte = ($int32[$i >> 2] >> (24 - ($i % 4) * 8)) & 0xff; // ff=255

            array_push($hex, gmp_strval(($byte >> 4), 16));
            array_push($hex, gmp_strval(($byte & 0x0f), 16)); // 0f=15
        }

        return implode("", $hex);
    }
}
