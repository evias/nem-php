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

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Math\GmpMathInterface;

use NEM\Contracts\Serializable;

use InvalidArgumentException;
use RuntimeException;

/**
 *
 * The Buffer class aims to provide an easy interface for interacting
 * with Public Keys and Private Keys formatted as Hexadecimal Strings.
 *
 * Conversion from Hex to Decimal to String, etc. can get quite complicated
 * and the Buffer class will be integrated in the Kernel for Encryption such
 * that Hashes are *always* represented correctly
 *
 * This class should be used internally only. Unit and Stress tests will be
 * provided during development to ensure the Security and Performance of
 * working with this Buffer class.
 *
 */
class Buffer
{
    /**
     * Size of the Buffer
     *
     * @var int
     */
    protected $size;

    /**
     * Content of the Buffer
     *
     * @var string
     */
    protected $buffer;

    /**
     * Elliptic Curve implementation for this Buffer
     *
     * @see \Mdanter\Ecc\EccFactory
     * @see \Mdanter\Ecc\Math\GmpMathInterface
     * @var \Mdanter\Ecc\Math\GmpMathInterface
     */
    protected $math;

    /**
     * Buffer::__construct()
     *
     * Construct a Buffer from a String. Both parameters are optional.
     *
     * When a `byteSize` is provided, the `byteString` will be evaluated
     * to make sure we don't run into size overflow errors.
     *
     * @param   string               $byteString        Content of the Buffer
     * @param   null|integer         $byteSize          Size of the Content (Optional)
     * @throws  \InvalidArgumentException    On Invalid `byteSize` and `byteString` pair.
     */
    public function __construct($byteString = '', $byteSize = null)
    {
        $this->math = EccFactory::getAdapter();
        if ($byteSize !== null) {
            // Check the integer doesn't overflow its supposed size
            if (strlen($byteString) > $byteSize) {
                throw new InvalidArgumentException('Byte string exceeds maximum size');
            }
        }
        else {
            $byteSize = strlen($byteString);
        }

        $this->size   = $byteSize;
        $this->buffer = $byteString;
    }

    /**
     * Buffer::__debugInfo() overload
     *
     * Return Buffer data formatted for `var_dump`.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'size' => $this->size,
            'buffer' => '0x' . unpack("H*", $this->buffer)[1],
        ];
    }

    /**
     * Build a Buffer from `data`.
     *
     * Dynamic Buffer creation. Decimals, Hexadecimals
     * and String are handled differently in binary form.
     *
     * Only use the bufferize method for Hexadecimal input
     * in case you are sure about the data length. It must
     * be a multiple of 32-bytes. (32, 64, 96, etc.)
     *
     * When you are working with Hexadecimal data, it is
     * *preferred* to use the `fromHex` method directly.
     *
     * @param   string|integer  $data
     * @param   integer         $byteSize
     * @return  \NEM\Core\Buffer
     */
    static public function bufferize($data, $byteSize = null)
    {
        if (is_integer($data)) {
            // Buffer from Decimal
            return Buffer::fromInt($data, $byteSize);
        }

        $charLen = strlen($data);
        if (ctype_xdigit($data) && $charLen % 32 === 0) {
            // Buffer from Hexadecimal
            return Buffer::fromHex($data, $byteSize);
        }

        // Buffer from Normalized String
        return Buffer::fromString($data);
    }

    /**
     * Build Buffer from string
     *
     * In case \Normalizer is available, the utf-8 string will
     * be normalized with Normalization Form KD.
     *
     * @param   string  $string
     * @return  \NEM\Core\Buffer
     */
    static public function fromString($string)
    {
        if (!class_exists("Normalizer")) {
            // Data representation Normalization not supported
            return new Buffer($string);
        }

        // Normalizer is used to avoid problems with UTF-8 serialization
        $normalized = \Normalizer::normalize($string, \Normalizer::FORM_KD);
        return new Buffer($normalized);
    }

    /**
     * Buffer::fromHex()
     *
     * Create a new buffer from a hexadecimal string.
     *
     * @param   string                  $hexString
     * @param   integer                 $byteSize
     * @return  \NEM\Core\Buffer
     * @throws  \InvalidArgumentException   On non-hexadecimal content in `hexString`
     */
    static public function fromHex($hexString = '', $byteSize = null)
    {
        if (strlen($hexString) > 0 && !ctype_xdigit($hexString)) {
            throw new InvalidArgumentException('NEM\\Core\\Buffer::hex: non-hexadecimal character passed');
        }

        // format to binary hexadecimal string
        $binary = pack("H*", $hexString);
        return new self($binary, $byteSize);
    }

    /**
     * Buffer::fromInt()
     *
     * Create a new buffer from an integer.
     *
     * The decimal format will be converted to hexadecimal first then
     * packed as a hexadecimal string.
     *
     * @param   int|string                          $integer
     * @param   null|int                            $byteSize
     * @param   \Mdanter\Ecc\Math\GmpMathInterface  $math       Allow to define custom Math Adapter.
     * @return  \NEM\Core\Buffer
     * @throws  InvalidArgumentException   On negative integer value
     */
    static public function fromInt($integer, $byteSize = null, GmpMathInterface $math = null)
    {
        if ($integer < 0) {
            throw new InvalidArgumentException('Buffer::int supports only unsigned integers.');
        }

        $math = $math ?: EccFactory::getAdapter();
        $binary = pack("H*", $math->decHex($integer));
        return new self($binary, $byteSize);
    }

    /**
     * Buffer::getSize()
     *
     * Get the size of the buffer to be returned
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Buffer::getInternalSize()
     *
     * Get the size of the value stored in the buffer
     *
     * @return int
     */
    public function getInternalSize()
    {
        return strlen($this->buffer);
    }

    /**
     * Buffer::getBinary()
     *
     * Get the binary data of the buffer.
     *
     * The binary data will be truncated or padded in case
     * the buffer content size and buffer size do not
     * match.
     *
     * This ensures that *whenever a size is set*, we will
     * get a strictly well-sized binary data format.
     *
     * @return string
     */
    public function getBinary()
    {
        // if a size is specified we'll make sure the value returned is *strictly* of that size
        if ($this->size !== null) {
            if (strlen($this->buffer) < $this->size) {
                // internal size of buffer is *too small*
                // will now pad the string (zeropadding).
                return str_pad($this->buffer, $this->size, chr(0), STR_PAD_LEFT);
            }
            elseif (strlen($this->buffer) > $this->size) {
                // buffer size overflow - truncate the buffer
                return substr($this->buffer, 0, $this->size);
            }
        }

        return $this->buffer;
    }

    /**
     * Buffer::getHex()
     *
     * Get hexadecimal representation of buffer content.
     *
     * @return string
     */
    public function getHex()
    {
        return unpack("H*", $this->getBinary())[1];
    }

    /**
     * Buffer::getGmp()
     *
     * Get Base16 representation (hexadecimal).
     *
     * This uses the GNU Multiple Precision PHP wrapper to
     * create a GMP number with the given base. (here 16 - hexadecimal)
     *
     * @return \GMP
     */
    public function getGmp($base = null)
    {
        $gmp = gmp_init($this->getHex(), 16);

        if (null !== $base)
            return gmp_strval($gmp, (int) $base);

        return $gmp;
    }

    /**
     * Buffer::getInt()
     *
     * Get Base10 representation (decimal).
     *
     * This uses the GNU Multiple Precision PHP wrapper to
     * create a GMP number with the given base. (here 10 - decimal)
     *
     * @return int|string
     */
    public function getInt()
    {
        return gmp_strval($this->getGmp(), 10);
    }

    /**
     * Buffer::getLong()
     *
     * Get Base10 Integer32 representation (signed long).
     *
     * @return int|string
     */
    public function getLong()
    {
        return pack("l", $this->getBinary());
    }

    /**
     * Buffer::flip()
     *
     * Reverse the bytes order of a Buffer. (Flip byte order)
     *
     * @return  \NEM\Core\Buffer
     */
    public function flip()
    {
        return $this->reverse();
    }

    /**
     * Buffer::reverse()
     *
     * Reverse the bytes order of a Buffer. (Flip byte order)
     *
     * @return  \NEM\Core\Buffer
     */
    public function reverse()
    {
        return $this->flipBytes();
    }

    /**
     * Buffer::slice()
     *
     * Get part of a buffer content.
     *
     * @param   integer             $start     Where in the buffer content should we start?
     * @param   integer|null        $end       (Optional) end of slice
     * @return  \NEM\Core\Buffer
     * @throws  \InvalidArgumentException    On invalid start or end parameter
     * @throws  \RuntimeException            On invalid resulting string slice
     */
    public function slice($start, $end = null)
    {
        if ($start > $this->getSize()) {
            throw new InvalidArgumentException('Start exceeds buffer length');
        }

        if ($end === null) {
            return new self(substr($this->getBinary(), $start));
        }

        if ($end > $this->getSize()) {
            throw new InvalidArgumentException('Length exceeds buffer length');
        }

        $string = substr($this->getBinary(), $start, $end);
        if (!is_string($string)) {
            throw new RuntimeException('Failed to slice string of with requested start/end');
        }

        $length = strlen($string);
        return new self($string, $length, $this->math);
    }

    /**
     * Buffer::equals()
     *
     * Buffer comparison operator.
     *
     * @param   Buffer     $other
     * @return  bool
     */
    public function equals(Buffer $other)
    {
        return ($other->getSize() === $this->getSize()
             && $other->getBinary() === $this->getBinary());
    }

    /**
     * Buffer::decimalToBinary()
     *
     * Return variable-sized Binary Integer value from decimal
     * value `decimal`.
     *
     * This will return either a `unsigned char`, a 16-bit
     * number, a 32-bit number or a 64-bit
     * number.
     *
     * @param   int     $decimal
     * @return  string          Binary Data
     */
    public function decimalToBinary($decimal)
    {
        if ($decimal < 0xfd) {
            // Uint8 (unsigned char)
            $bin = chr($decimal);
        }
        elseif ($decimal <= 0xffff) {
            // Uint16 (unsigned short)
            $bin = pack("Cv", 0xfd, $decimal);
        }
        elseif ($decimal <= 0xffffffff) {
            // Uint32 (unsigned long)
            $bin = pack("CV", 0xfe, $decimal);
        }
        else {
            // Uint64 (unsigned long long)
            // convert to 32-bit notation and concat-pack

            $smallerThan = 0x00000000ffffffff;
            $biggerThan  = 0xffffffff00000000;

            $a32 = ($decimal & $biggerThan) >>32;
            $b32 = $decimal & $smallerThan;
            $bin = pack("NN", $a32, $b32);
        }

        return $bin;
    }

    /**
     * Buffer::decimalToBuffer()
     *
     * Convert a decimal number into a Buffer
     *
     * @param   integer     $decimal
     * @return  \NEM\Core\Buffer
     */
    public function decimalToBuffer($decimal)
    {
        return new Buffer($this->decimalToBinary($decimal));
    }

    /**
     * Buffer::flipBytes()
     *
     * Flip byte order (reverse order) of this binary string. Accepts a string or Buffer,
     * and will return whatever type it was given.
     *
     * @param   null|string|\NEM\Core\Buffer  $bytes  Bytes that must be reversed.
     * @return  string|\NEM\Core\Buffer
     */
    public function flipBytes($bytes = null)
    {
        if (null === $bytes && $this instanceof Buffer) {
            $bytes = $this;
        }

        $isBuffer = $bytes instanceof Buffer;
        if ($isBuffer) {
            $bytes = $bytes->getBinary();
        }

        $flipped = implode('', array_reverse(str_split($bytes, 1)));
        if ($isBuffer) {
            $flipped = new Buffer($flipped);
        }

        return $flipped;
    }

    /**
     * Transfer binary data into a unsigned char array. Unsigned Char
     * is the same as UInt8 in Javascript or other languages, it represents
     * unsigned integers on 8 bits (1 byte).
     *
     * @return array
     */
    public function toUInt8()
    {
        $binary = $this->getBinary();
        $split = str_split($binary, 1);

        // argument *by-reference*
        array_walk($split, function(&$item, $ix) {
            $buf = new Buffer($item, 1);
            $item = $buf->getInt();
        });

        return $split;
    }

    static public function fromUInt8(array $uint8)
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
     * Transfer binary data into a signed integer array.
     *
     * @see getUInt8Array
     * @return array
     */
    public function ua2words(array $uint8 = null)
    {
        $uint8 = $uint8 ?: $this->toUInt8();
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

    public function words2ua(array $words = null)
    {
        $unsignedRShift = function($a, $b)
        {
            if($b == 0) return $a;
            return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
        };

        $int32 = $words ?: $this->ua2words();
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

    public function words2hex(array $words = null)
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

    public function ua2hex(array $uint8 = null)
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
     * Buffer::concat()
     *
     * Concatenate buffers
     *
     * @param   \NEM\Core\Buffer    $buffer1
     * @param   int                 $size
     * @return  \NEM\Core\Buffer
     */
    public function concat(Buffer $buffer, $size = null)
    {
        if (null === $size || $size < $this->getSize() + $buffer->getSize())
            $size = $this->getSize() + $buffer->getSize();

        return new Buffer($this->getBinary() . $buffer->getBinary(), $size);
    }

    /**
     * Buffer::sort()
     *
     * Sorting multiple buffers.
     *
     * The default behaviour should be, take a list of Buffers/SerializableInterfaces, and
     * sort their binary representation.
     *
     * If an anonymous function is provided, we completely defer the conversion of values to
     * Buffer to the $convertToBuffer callable.
     *
     * This is to allow anonymous functions which are responsible for converting the item to a buffer,
     * and which optionally type-hint the items in the array.
     *
     * @param   array       $items              Array of buffers to sort
     * @param   callable    $convertToBuffer    (Optional) closure for converting `items` entries to a Buffer instance
     * @return  array
     * @throws  \RuntimeException    On unknown value type (cannot be converted to Buffer)
     */
    public function sort(array $items, callable $convertToBuffer = null)
    {
        if (null == $convertToBuffer) {
            $convertToBuffer = function ($value) {
                if ($value instanceof Buffer) {
                    return $value;
                }

                if ($value instanceof Serializable) {
                    return $value->getBuffer();
                }

                throw new RuntimeException('Requested to sort unknown buffer type');
            };
        }

        usort($items, function ($a, $b) use ($convertToBuffer) {
            $av = $convertToBuffer($a)->getBinary();
            $bv = $convertToBuffer($b)->getBinary();
            return $av == $bv ? 0 : $av > $bv ? 1 : -1;
        });

        return $items;
    }

    /**
     * Buffer::hash()
     *
     * Create HMAC out of buffer using said `algorithm`.
     *
     * Currently supported algorithm include, but are not limited to:
     *
     * - sha256 (32 bytes)
     * - sha512 (64 bytes)
     * - sha1 (20 bytes)
     *
     * @param   string  $algorithm      Hash algorithm (Example: sha512)
     * @return  \NEM\Core\Buffer
     */
    public function hash($algorithm = "sha512")
    {
        if (! in_array($algorithm, hash_algos())) {
            throw new InvalidArgumentException("Hash algorithm '" . $algorithm . "' not supported.");
        }

        $byteSize = false !== strpos($algorithm, "512") ? 64 : 32;
        if ($algorithm === "sha1") $byteSize = 20;

        $hashed = new Buffer(hash($algorithm, $this->getBinary(), true), $byteSize);
        //$hashed = Buffer::fromHex(hash($algorithm, $this->getHex()));
        return $hashed->getHex();
    }
}
