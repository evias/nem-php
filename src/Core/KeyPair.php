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

class KeyPair
    extends Buffer
    implements KeyPairContract
{
    /**
     * The *hexadecimal* data of the public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * The *reversed hexadecimal* data of the private key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * This method creates a KeyPair 
     *
     * @param   null|string|\NEM\Core\Buffer   $privateKey      The private key in hexadecimal format (or in Buffer).
     * @return  \NEM\Core\KeyPair
     */
    static public function create($privateKey = null)
    {
        $kp = new static;
        if ($privateKey !== null && is_string($privateKey)) {
            $kp->privateKey = new Buffer($privateKey);
        }
        elseif ($privateKey instanceof KeyPair) {
            $kp = clone $privateKey;
        }
        elseif ($privateKey !== null) {
            // `privateKey` could not be interpreted.
            throw new RuntimeException("Invalid Private key for KeyPair creation. Please use hexadecimal notation (in a string) or the \\NEM\\Core\\Buffer class.");
        }
        else {
            // no `privateKey` provided, generate a new KeyPair
            $kp->privateKey = new Buffer(random_bytes(16), 32);
        }

        if (!$kp->publicKey) {
            //XXX public key - openssl/libsodium? secp256k1?
        }
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * Binary data should and will only be used internally.
     *
     * @internal
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
     * @internal
     * @return  string
     */
    public function getPrivateKey()
    {
        return $this->privateKey->getHex();
    }
}