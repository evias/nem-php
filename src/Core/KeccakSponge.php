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

use NEM\Core\KeccakHasher;
use \desktopd\SHA3\Sponge as KeccakSpongeImpl;

class KeccakSponge
{
    /**
     * Keccak Sponge Algorithm indexes.
     * 
     * @var integer
     */
    const SHA3_224 = 1;
    const SHA3_256 = 2;
    const SHA3_384 = 3;
    const SHA3_512 = 4;
    const SHAKE128 = 5;
    const SHAKE256 = 6;

    /**
     * Type of SHA algorithm
     * 
     * @see \desktopd\SHA3\Sponge
     * @var integer
     */
    protected $type;

    /**
     * The sponge instance.
     * 
     * @var \desktopd\SHA3\Sponge
     */
    protected $sponge;

    /**
     * The sponge delimited suffix.
     * 
     * @var integer
     */
    protected $suffix;

    /**
     * The output length in bytes.
     * 
     * @var integer
     */
    protected $length;

    /**
     * Initialize a Keccak Sponge.
     * 
     * @param   integer     $shaType    Type of SHA algorithm.
     * @return  void
     */
    public function __construct(int $shaType = self::SHA3_512, $hashBitLen = 512)
    {
        $this->type = $shaType;
        $this->sponge = KeccakSpongeImpl::init($shaType);
        $this->suffix = $shaType < self::SHAKE128 ? 0x06 : 0x1f;
        $this->length = $hashBitLen / 8;
    }

    /**
     * Keccak Sponge Absorb function
     * 
     * @param   string      $data
     * @param   integer     $length
     * @return  \NEM\Core\KeccakSponge
     */
    public function absorb($data, int $length = null)
    {
        $this->sponge = $this->sponge->absorb($data);
        return $this;
    }

    /**
     * Keccak Sponge Squeeze function
     * 
     * @param   integer     $length
     * @param   boolean     $raw_output
     * @return  string|\NEM\Core\Buffer
     */
    public function squeeze(int $length = null, $raw_output = false)
    {
        $binary = $this->sponge->squeeze($length);

        if ($raw_output) {
            return $binary;
        }

        // add size security through Buffer class
        if (! $length) {
            $length = mb_strlen($binary, "8bit");
        }

        return new Buffer($binary, $length);
    }

    /**
     * Setter for the suffix of the sponge instance.
     * 
     * @param   integer     $suffix
     * @return  \NEM\Core\KeccakSponge
     */
    public function setSuffix(int $suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Getter for the suffix of the sponge instance.
     * 
     * @return  integer
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Getter for the output length of the sponge instance.
     * 
     * @return  integer
     */
    public function getOutputLength()
    {
        return $this->length;
    }
}
