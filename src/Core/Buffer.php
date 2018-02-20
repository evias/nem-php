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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Core;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Math\GmpMathInterface;

use NEM\Contracts\Serializable;
use NEM\Core\Encoder;

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
     * Buffer padding directions
     * 
     * @internal
     * @var integer
     */
    const PAD_LEFT = 1;
    const PAD_RIGHT = 2;

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
     * The padding direction (default LEFT)
     *
     * @var integer
     */
    protected $paddingDirection;

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
    public function __construct($byteString = '', $byteSize = null, $paddingDirection = self::PAD_LEFT)
    {
        $this->math = EccFactory::getAdapter();
        if ($byteSize !== null) {
            // Check that the buffer content doesn't overflow its supposed size
            if (strlen($byteString) > $byteSize) {
                throw new InvalidArgumentException('Byte string exceeds maximum size');
            }
        }
        else {
            $byteSize = strlen($byteString);
        }

        $this->size   = $byteSize;
        $this->buffer = $byteString === null ? "" : $byteString;
        $this->paddingDirection = $paddingDirection;
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
    static public function bufferize($data, $byteSize = null, $paddingDirection = self::PAD_LEFT)
    {
        if (is_integer($data)) {
            // Buffer from Decimal
            return Buffer::fromInt($data, $byteSize, null, $paddingDirection);
        }

        $charLen = strlen($data);
        if (ctype_xdigit($data) && $charLen % 32 === 0) {
            // Buffer from Hexadecimal
            return Buffer::fromHex($data, $byteSize, $paddingDirection);
        }

        // Buffer from Normalized String
        return Buffer::fromString($data, $paddingDirection);
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
    static public function fromString($string, $paddingDirection = self::PAD_LEFT)
    {
        if (!class_exists("Normalizer")) {
            // Data representation Normalization not supported
            return new Buffer($string, null, $paddingDirection);
        }

        // Normalizer is used to avoid problems with UTF-8 serialization
        $normalized = \Normalizer::normalize($string, \Normalizer::FORM_KD);
        return new Buffer($normalized, null, $paddingDirection);
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
    static public function fromHex($hexString = '', $byteSize = null, $paddingDirection = self::PAD_LEFT)
    {
        if (strlen($hexString) > 0 && !ctype_xdigit($hexString)) {
            throw new InvalidArgumentException('NEM\\Core\\Buffer::hex: non-hexadecimal character passed');
        }

        // format to binary hexadecimal string
        $binary = pack("H*", $hexString);
        return new self($binary, $byteSize, $paddingDirection);
    }

    /**
     * Build a Buffer object from a UInt8 array.
     * 
     * @param   array   $uint8
     * @return  \NEM\Core\Buffer
     */
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
    static public function fromInt($integer, $byteSize = null, GmpMathInterface $math = null, $paddingDirection = self::PAD_LEFT)
    {
        if ($integer < 0) {
            throw new InvalidArgumentException('Buffer::int supports only unsigned integers.');
        }

        $math = $math ?: EccFactory::getAdapter();
        $binary = null;
        if ($integer !== null) {
            $binary = pack("H*", $math->decHex($integer));
        }

        return new self($binary, $byteSize, $paddingDirection);
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
                $direction = $this->paddingDirection == self::PAD_RIGHT ? STR_PAD_RIGHT : STR_PAD_LEFT; 
                return str_pad($this->buffer, $this->size, chr(0), $direction);
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
        return new self($string, $length);
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
    public function decimalToBinary($decimal, $size = null, $padding = false, $direction = self::PAD_LEFT)
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

        // add padding when needed
        if ($padding = true && $size) {
            $buf = new Buffer($bin, $size, $direction);
            $bin = $buf->getBinary();
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
            $item = (int) $buf->getInt();
        });

        return $split;
    }

    /**
     * Buffer::concat()
     *
     * Concatenate buffers
     *
     * @param   \NEM\Core\Buffer    $buffer1
     * @return  \NEM\Core\Buffer
     */
    public function concat(Buffer $buffer)
    {
        // size-protected through Buffer class
        $this->buffer = $this->buffer . $buffer->getBinary();
        $this->size  += $buffer->getSize();
        return $this;
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

    /**
     * Convert 64 Bytes Keccak SHA3-512 Hashes into a Secret Key.
     * 
     * @param   string  $unsafeSecret   A 64 bytes (512 bits) Keccak hash produced from a KeyPair's Secret Key.
     * @return  string                  Byte-level representation of the Secret Key.
     */
    static public function clampBits($unsafeSecret, $bytes = 64)
    {
        if ($unsafeSecret instanceof Buffer) {
            // copy-construct to avoid malformed and wrong size
            $toBuffer = new Buffer($unsafeSecret->getBinary(), $bytes);
        }
        elseif (! ctype_xdigit($unsafeSecret)) {
            // build from binary
            $toBuffer = new Buffer($unsafeSecret, $bytes);
        }
        else {
            $toBuffer = Buffer::fromHex($unsafeSecret, $bytes);
        }

        // clamping bits
        $clampSecret  = $toBuffer->toUInt8();
        $clampSecret[0] &= 0xf8; // 248
        $clampSecret[31] &= 0x7f; // 127
        $clampSecret[31] |= 0x40; // 64

        // build Buffer object from UInt8 and return byte-level representation
        $encoder = new Encoder;
        $safeSecret = $encoder->ua2bin($clampSecret);
        return $safeSecret;
    }
}
