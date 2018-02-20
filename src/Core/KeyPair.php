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

use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;
use NEM\Core\Signature;
use NEM\Core\Encryption;
use NEM\Errors\NISInvalidPrivateKeySize;    
use NEM\Errors\NISInvalidPrivateKeyContent;
use NEM\Errors\NISInvalidPublicKeySize;
use NEM\Errors\NISInvalidPublicKeyContent;
use NEM\Errors\NISInvalidSignatureContent;
use \ParagonIE_Sodium_Core_Ed25519;

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
     * The address derived of the public key.
     *
     * @var \NEM\Models\Address
     */
    protected $address;

    /**
     * This method creates a KeyPair out of a private key.
     *
     * The `privateKey` argument must be 64 bytes long or 66 bytes
     * long 
     *
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @param   null|string|\NEM\Core\Buffer                     $publicKey       The public key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     * @throws  \NEM\Errors\NISInvalidPrivateKeySize       On string key size with wrong length. (strictly 64 or 66 characters)
     * @throws  \NEM\Errors\NISInvalidPrivateKeyContent    On string key invalid content. (non hexadecimal characters)
     */
    static public function create($privateKey = null, $publicKey = null)
    {
        $kp = new static($privateKey, $publicKey);
        return $kp;
    }

    /**
     * KeyPair object constructor.
     *
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @param   null|string|\NEM\Core\Buffer                     $publicKey       The public key in hexadecimal format (or in Buffer).
     * @return  void
     */
    public function __construct($privateKey = null, $publicKey = null)
    {
        $this->keygen = new KeyGenerator;
        $this->preparePrivateKey($privateKey);

        if (null !== $publicKey) {
            // use provided `publicKey` parameter
            $this->publicKey = $this->preparePublicKey($publicKey);
        }
        else {
            // use key generator for public key derivation
            $this->publicKey = $this->keygen->derivePublicKey($this);
        }

        $this->address = \NEM\Models\Address::fromPublicKey($this->publicKey);
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
     * This method should return the *base32 encoded representation* of the
     * NEM Address.
     *
     * @param   string|integer  $networkId        A network ID OR a network name. (default mainnet)
     * @param   boolean         $prettyFormat     Boolean whether address should be prettified or not.
     * @return  string
     */
    public function getAddress($networkId = 104, $prettyFormat = false)
    {
        $address = \NEM\Models\Address::fromPublicKey($this->getPublicKey());
        if ($prettyFormat) {
            return $address->toPretty();
        }

        return $address->toClean();
    }

    /**
     * This method returns a 64 bytes signature of the *data signed with the
     * current `secretKey`*.
     * 
     * You can also specify the `enc` parameter to be "hex", "uint8" or "int32".
     * 
     * @param   null|string|\NEM\Core\Buffer   $data        The data that needs to be signed.
     * @param   string                         $algorithm   The hash algorithm used for signature creation.
     * @param   string|integer                 $enc         Which encoding to return (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array   Returns either of Buffer, string hexadecimal representation, or UInt8 or Int32 array.
     */
    public function sign($data, $algorithm = "keccak-512", $enc = null)
    {
        if ($data instanceof Buffer) {
            $data = $data->getBinary();
        }
        elseif (is_string($data) && ctype_xdigit($data)) {
            $data = Buffer::fromHex($data)->getBinary();
        }
        elseif (!is_string($data)) {
            throw new NISInvalidSignatureContent("Invalid data argument passed in \\NEM\\Core\\KeyPair::sign().");
        }

        $buf = new Buffer($data);
        $sig = new Signature($this, $buf, $algorithm);
        return $this->encodeKey($sig->getSignature(), $enc);
    }

    /**
     * This method will parse a given `privateKey` such that we
     * store the binary representation of the private key *always*.
     * 
     * This method is used internally to create the KeyPair secret key
     * which is the key that is actually hashed and derived to get the
     * public key.
     *
     * @internal
     * @param   null|string|\NEM\Core\Buffer|\NEM\Core\KeyPair   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     * @throws  \NEM\Errors\NISInvalidPrivateKeySize       On string key size with wrong length. (strictly 64 or 66 characters)
     * @throws  \NEM\Errors\NISInvalidPrivateKeyContent    On string key invalid content. (non hexadecimal characters)
     */
    protected function preparePrivateKey($privateKey = null)
    {
        if (is_string($privateKey)) {
            // provided a string, must check if it contains 64 or 66 characters.
            // and whether it is valid hexadecimal data.

            $keySize = strlen($privateKey);
            if ($keySize !== 64 && $keySize !== 66) {
                throw new NISInvalidPrivateKeySize("Private keys must be 64 or 66 characters exactly.");
            }
            elseif (! ctype_xdigit($privateKey)) {
                throw new NISInvalidPrivateKeyContent("Argument 'privateKey' in KeyPair::create must contain only Hexadecimal data.");
            }

            // remove NIS negative "00" prefix if available.
            $this->privateKey = Buffer::fromHex(substr($privateKey, -64), 32);
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
     * This method will parse a given `publicKey` such that we
     * store the binary representation of the public key *always*.
     * 
     * This method is used internally whenever a public key *is not*
     * derived of the passed private key but rather passed directly in
     * hexadecimal or binary representation.
     *
     * @internal
     * @param   null|string|\NEM\Core\Buffer   $publicKey           The public key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     * @throws  \NEM\Errors\NISInvalidPublicKeySize       On string key size with wrong length. (strictly 64 characters)
     * @throws  \NEM\Errors\NISInvalidPublicKeyContent    On string key invalid content. (non hexadecimal characters)
     */
    protected function preparePublicKey($publicKey = null)
    {
        if (is_string($publicKey)) {
            // provided a string, must check if it contains 64 characters.
            // and whether it is valid hexadecimal data.

            $keySize = strlen($publicKey);
            if ($keySize !== 64) {
                throw new NISInvalidPublicKeySize("Public keys must be strictly 64 hexadecimal characters.");
            }
            elseif (! ctype_xdigit($publicKey)) {
                throw new NISInvalidPublicKeyContent("Argument 'publicKey' in KeyPair::create must contain only Hexadecimal data.");
            }

            $this->publicKey = Buffer::fromHex($publicKey, 32);
        }
        elseif ($publicKey instanceof Buffer) {
            // copy construction - clone the buffer (binary data of the private key)
            $this->publicKey = clone $publicKey;
        }
        elseif ($publicKey !== null) {
            // `publicKey` could not be interpreted.
            throw new RuntimeException("Invalid Private key for KeyPair creation. Please use hexadecimal notation (64|66 characters string) or the \\NEM\\Core\\Buffer class.");
        }
    }

    /**
     * This method encodes a given `key` to the given `enc` codec or 
     * returns the Buffer itself if no encoding was specified.
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