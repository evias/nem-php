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
     * 
     */
    protected $engine;

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
        $kp = new static;
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

            $kp->privateKey = Buffer::fromHex(substr($privateKey, -64));
        }
        elseif ($privateKey instanceof KeyPair) {
            // copy construction - clone the KeyPair object
            $kp = clone $privateKey;
        }
        elseif ($privateKey instanceof Buffer) {
            // copy construction - clone the buffer (binary data of the private key)
            $kp->privateKey = clone $privateKey;
        }
        elseif (null === $privateKey) {
            // no `privateKey` provided, generate a new KeyPair
            $kp->privateKey = new Buffer(random_bytes(32), 32);
        }
        elseif ($privateKey !== null) {
            // `privateKey` could not be interpreted.
            throw new RuntimeException("Invalid Private key for KeyPair creation. Please use hexadecimal notation (in a string) or the \\NEM\\Core\\Buffer class.");
        }

        // secretKey is the reversed private key hexadecimal representation
        $kp->secretKey = $kp->privateKey->flip();

        // public key is derived using libsodium
        // @link https://github.com/jedisct1/libsodium-php
        $kp->publicKey = $kp->derivePublicKey();
        return $kp;
    }

    protected function derivePublicKey()
    {
        $keypair = sodium_crypto_sign_seed_keypair($this->secretKey->getBinary());
        $keypairBuf = new Buffer($keypair, SODIUM_CRYPTO_SIGN_KEYPAIRBYTES);

        $secret = sodium_crypto_sign_secretkey($keypair);
        $secretBuf = new Buffer($secret, SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);

        $public = sodium_crypto_sign_publickey_from_secretkey($secretBuf->getBinary());
        $publicKey = new Buffer($public, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
        return $publicKey;
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * Binary data should and will only be used internally.
     *
     * @return  string
     */
    public function getPublicKey()
    {
        return $this->publicKey->getHex();
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Private Key.
     *
     * Binary data should and will only be used internally.
     *
     * @return  string
     */
    public function getPrivateKey()
    {
        return $this->privateKey->getHex();
    }

    /**
     * This method should return the *reversed Hexadecimal representation*
     * of the object's private key.
     *
     * Reversed hexadecimal notation happens on binary data, the \NEM\Core\Buffer
     * class represents the given hexadecimal payload in binary form and flips
     * the bytes of the buffer.
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey->getHex();
    }
}