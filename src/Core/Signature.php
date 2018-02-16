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

use NEM\Core\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encryption;
use NEM\Errors\NISInvalidSignatureContent;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519;
USE \SodiumException;

class Signature
    extends ParagonIE_Sodium_Core_Ed25519
{
    /**
     * Public Key of the Signer in Hexadecimal Format (32 bytes).
     * 
     * @var \NEM\Core\Buffer
     */
    protected $signer;

    /**
     * Generated Signature in Hexadecimal Format (64 bytes)
     * 
     * @var \NEM\Core\Buffer
     */
    protected $signature;

    /**
     * Holds the Encryption algorithm name.
     * 
     * This is to provide more flexibility when it comes
     * to signatures with keccak-512, sha-512, etc.
     * 
     * @see https://php.net/hash_algos
     * @var string
     */
    protected $algorithm;

    /**
     * This method creates a Signature of said `data` with the
     * given `keyPair` KeyPair object.
     *
     * @param   \NEM\Core\KeyPair          $keyPair      The KeyPair object with which you want to sign `data`.
     * @param   string|\NEM\Core\Buffer    $data         The data that needs to be signed.
     * @param   null|string                $algorithm    The hash algorithm that you wish to use for signature creation.
     * @return  \NEM\Core\Signature
     * @throws  \NEM\Errors\NISInvalidSignatureContent      On invalid `data` argument. Should be a \NEM\Core\Buffer or a string.
     */
    static public function create(KeyPair $kp, $data, $algorithm = 'keccak-512')
    {
        $sig = new static($privateKey, $publicKey, $algorithm);
        return $sig;
    }

    /**
     * Signature object constructor.
     *
     * @param   \NEM\Core\KeyPair          $keyPair      The KeyPair object with which you want to sign `data`.
     * @param   string|\NEM\Core\Buffer    $data         The data that needs to be signed.
     * @param   null|string                $algorithm    The hash algorithm that you wish to use for signature creation.
     * @return  void
     * @throws  \NEM\Errors\NISInvalidSignatureContent      On invalid `data` argument. Should be a \NEM\Core\Buffer or a string.
     */
    public function __construct(KeyPair $kp, $data, $algorithm = 'keccak-512')
    {
        // wrap data in internal \NEM\Core\Buffer class
        $this->prepareData($data);

        $this->algorithm = $algorithm ?: "keccak-512";
        $this->signature = Encryption::sign($kp, $this->data, $algorithm);
        $this->signer = $kp->getPublicKey();
    }

    /**
     * Getter for the `signer` property.
     * 
     * @return \NEM\Core\Buffer
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * Getter for the `signature` property.
     * 
     * @return \NEM\Core\Buffer
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * This method will prepare the given data and populate the
     * `data` property with a prepared and correctly sized Buffer.
     * 
     * @param   string|\NEM\Core\Buffer    $data         The data that needs to be signed.
     * @return  \NEM\Core\Signature
     * @throws  \NEM\Errors\NISInvalidSignatureContent      On invalid `data` argument. Should be a \NEM\Core\Buffer or a string.
     */
    protected function prepareData($data) 
    {
        if ($data instanceof Buffer) {
            $this->data = $data;
        }
        elseif (is_string($data)) {
            $this->data = new Buffer($data); // auto-sized
        }
        else {
            throw new NISInvalidSignatureContent("Invalid content provided for \\NEM\\Core\\Signature object.");
        }

        return $this;
    }

}
