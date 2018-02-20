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

use kornrunner\Keccak;
use NEM\Core\Buffer;

class KeccakHasher
{
    /**
     * Constant for Default Hash Bit Length.
     * 
     * @var integer
     */
    const HASH_BIT_LENGTH = 512;

    /**
     * Keccak Sponge Lane size in Bytes. (Default 8)
     * 
     * @var integer
     */
    const KECCAK_LANE_SIZE  = 8;

    /**
     * Keccak Sponge Width.(Default 1600)
     * 
     * @var integer
     */
    const KECCAK_WIDTH      = 1600;

    /**
     * Keccak State size in Bytes. (Default 200)
     * 
     * @var integer
     */
    const KECCAK_STATE_SIZE = self::KECCAK_WIDTH / self::KECCAK_LANE_SIZE;

    /**
     * Non-Incremental Keccak Hash implementation.
     * 
     * @param   null|string|integer     $algorithm      The hashing algorithm or Hash Bit Length.
     * @param   string|\NEM\Core\Buffer $data           The data that needs to be hashed.
     * @param   boolean                 $raw_output     Whether to return raw data or a Hexadecimal hash.
     * @return  string
     */
    static public function hash($algorithm, $data, $raw_output = false)
    {
        $hashBits = self::getHashBitLength($algorithm);
        return Keccak::hash($data, (int) $hashBits, (bool) $raw_output);
    }

    /**
     * Helper function used to determine each hash's Bits length
     * by a given `algorithm`.
     * 
     * The `algorithm` parameter can be a integer directly and should
     * then represent a Bits Length for generated Hashes.
     * 
     * @param   null|string|integer     $algorithm      The hashing algorithm or Hashes' Bits Length.
     * @return  integer
     */
    static public function getHashBitLength($algorithm = null)
    {
        if (!$algorithm) {
            return self::HASH_BIT_LENGTH;
        }

        if (is_integer($algorithm)) {
            // direct hash-bit-length provided
            return (int) $algorithm;
        }
        elseif (strpos(strtolower($algorithm), "keccak-") !== false) {
            $bits = (int) substr($algorithm, -3); // keccak-256, keccak-512, etc.

            if (! in_array($bits, [256, 228, 384, 512]))
                $bits = 512;

            return $bits;
        }

        return self::HASH_BIT_LENGTH;
    }

/**
     * Initialize a *Incremental* Keccak Hash.
     * 
     * This will construct a Keccak Sponge. The `algorithm`
     * parameter can contain any of: keccak-512, keccak-384,
     * keccak-256, keccak-224.
     * 
     * @param   integer|string  $algorithm
     * @return  \NEM\Core\KeccakSponge

    static public function hash_init($algorithm)
    {
        $hashBits = self::getHashBitLength($algorithm);
        $typeByBits = [
            512 => KeccakSponge::SHA3_512,
            384 => KeccakSponge::SHA3_384,
            256 => KeccakSponge::SHA3_256,
            224 => KeccakSponge::SHA3_224,
        ];

        // determine SHA algorithm type by Hash Bit Length
        $shaType = $typeByBits[$hashBits];

        // `shaType` also defines `rate` and `capacity`.
        $sponge = new KeccakSponge($shaType, $hashBits);
        return $sponge;
    }

    /**
     * Add data to an incremental Keccak Sponge instance.
     * 
     * @param   \NEM\Core\KeccakSponge  $sponge
     * @param   string                  $data
     * @param   integer                 $dataBitLen
     * @return  \NEM\Core\KeccakSponge

    static public function hash_update(KeccakSponge $sponge, $data, $dataBitLen = null)
    {
        if (! $dataBitLen) {
            $bytes = mb_strlen($data, "8bit");
            $dataBitLen = $bytes * 8;
        }

        if (($dataBitLen % 8) === 0) {
            // we have a full block
            return $sponge->absorb($data, $dataBitLen/8);
        }
        else {
            $sponge = $sponge->absorb($data, $dataBitLen/8);

            // The last partial byte is assumed to be aligned on the 
            // least significant bits
            $lastByte = $data[$dataBitLen/8];
            $lastBytes = $lastByte | (0x06 << ($dataBitLen % 8));

            if (($lastByte & 0xff00) === 0x0000) {
                $sponge->setSuffix($lastBytes & 0xff);
            }
            else {
                $oneByte = $lastBytes & 0xff;
                $sponge  = $sponge->absorb($oneByte, 1);
                $sponge->setSuffix(($lastBytes >> 8) & 0xff);
            }
        }

        return $sponge;
    }

    /**
     * Finalize an *Incremental Keccak Hash* generation.
     * 
     * @param   boolean                 $raw_output
     * @return  string|\NEM\Core\Buffer

    public function hash_final(KeccakSponge $sponge, bool $raw_output = false)
    {
        $output = $sponge->squeeze(null, $raw_output);

        if ($raw_output) {
            return $output;
        }

        $outputLength = $sponge->getLength();
        return new Buffer($output, $outputLength);
    }
**/
}
