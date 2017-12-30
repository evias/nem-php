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

use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;
use NEM\Errors\NISInvalidPrivateKeySizeException;
use NEM\Errors\NISInvalidPrivateKeyContentException;

class KeyPair
    implements KeyPairContract
{
    /**
     * The Key Generator instance.
     * 
     * @var \NEM\Core\KeyGenerator
     */
    protected $keygen;

    /**
     * The *hexadecimal* data of the public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * The *hexadecimal* data of the private key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * The *reversed hexadecimal* data of the private key.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * This method creates a KeyPair out of a private key.
     *
     * The `privateKey` argument must be 64 bytes long or 66 bytes
     * long 
     *
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     * @throws  \NEM\Errors\NISInvalidPrivateKeySizeException       On string key size with wrong length. (strictly 64 or 66 characters)
     * @throws  \NEM\Errors\NISInvalidPrivateKeyContentException    On string key invalid content. (non hexadecimal characters)
     */
    static public function create($privateKey = null)
    {
        $kp = new static($privateKey);
        return $kp;
    }

    /**
     * KeyPair object constructor.
     *
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @return  void
     */
    public function __construct($privateKey = null)
    {
        $this->keygen = new KeyGenerator;
        $this->preparePrivateKey($privateKey);

        // use key generator for public key derivation
        $this->publicKey = $this->keygen->derivePublicKey($this);
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * Binary data should and will only be used internally.
     *
     * @param   string|integer                  Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array   Returns either of Buffer, string hexadecimal representation, or UInt8 or Int32 array.
     */
    public function getPublicKey($enc = null)
    {
        return $this->encodeKey($this->publicKey, $enc);
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Private Key.
     *
     * Binary data should and will only be used internally.
     *
     * @param   string|integer                  Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array   Returns either of Buffer, string hexadecimal representation, or UInt8 or Int32 array.
     */
    public function getPrivateKey($enc = null)
    {
        return $this->encodeKey($this->privateKey, $enc);
    }

    /**
     * This method should return the *reversed Hexadecimal representation*
     * of the object's private key.
     *
     * Reversed hexadecimal notation happens on binary data, the \NEM\Core\Buffer
     * class represents the given hexadecimal payload in binary form and flips
     * the bytes of the buffer.
     *
     * @param   string|integer      $enc        Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array   Returns either of Buffer, string hexadecimal representation, or UInt8 or Int32 array.
     */
    public function getSecretKey($enc = null)
    {
        return $this->encodeKey($this->secretKey, $enc);
    }

    /**
     * This method will parse a given `privateKey` such that we
     * store the binary representation of the private key *always*.
     *
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     * @throws  \NEM\Errors\NISInvalidPrivateKeySizeException       On string key size with wrong length. (strictly 64 or 66 characters)
     * @throws  \NEM\Errors\NISInvalidPrivateKeyContentException    On string key invalid content. (non hexadecimal characters)
     */
    protected function preparePrivateKey($privateKey = null)
    {
        if (is_string($privateKey)) {
            // provided a string, must check if it contains 64 or 66 characters.
            // and whether it is valid hexadecimal data.

            $keySize = strlen($privateKey);
            if ($keySize !== 64 && $keySize !== 66) {
                throw new NISInvalidPrivateKeySizeException("Private keys must be 64 or 66 characters exactly.");
            }
            elseif (! ctype_xdigit($privateKey)) {
                throw new NISInvalidPrivateKeyContentException("Argument 'privateKey' in KeyPair::create must contain only Hexadecimal data.");
            }

            // remove NIS negative "00" prefix if available.
            $this->privateKey = Buffer::fromHex(substr($privateKey, -64));
        }
        elseif ($privateKey instanceof KeyPair) {
            // copy construction - copy the KeyPair object
            $this->privateKey = $privateKey->privateKey;
            $this->secretKey = $privateKey->secretKey;
            $this->publicKey = $privateKey->publicKey;
        }
        elseif ($privateKey instanceof Buffer) {
            // copy construction - clone the buffer (binary data of the private key)
            $this->privateKey = clone $privateKey;
        }
        elseif (null === $privateKey) {
            // no `privateKey` provided, generate a new KeyPair
            $this->privateKey = new Buffer(random_bytes(32), 32);
        }
        elseif ($privateKey !== null) {
            // `privateKey` could not be interpreted.
            throw new RuntimeException("Invalid Private key for KeyPair creation. Please use hexadecimal notation (64|66 characters string) or the \\NEM\\Core\\Buffer class.");
        }

        // secret key is the byte-level-reversed representation of the private key.
        $this->secretKey = $this->privateKey->flip();
        return $this;
    }

    /**
     * This method encodes a given `key` to the given
     * `enc` codec or returns the Buffer itself if no
     * encoding was specified.
     *
     * @param   \NEM\Core\Buffer    $key        The Key object (Buffer) that needs to be encoded. 
     * @param   string|integer      $enc        Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array   Returns either of Buffer, string hexadecimal representation, or UInt8 or Int32 array.
     */
    protected function encodeKey(Buffer $key, $enc = null)
    {
        if ("hex" === $enc || (int) $enc == 16) {
            return $key->getHex();
        }
        elseif ("uint8" === $enc || (int) $enc == 8) {
            return $key->toUInt8();
        }
        elseif ("int32" === $enc || (int) $enc == 32) {
            $encoder = new Encoder;
            return $encoder->ua2words($key->toUInt8());
        }

        return $key;
    }
}