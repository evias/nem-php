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
use NEM\Core\Encoder;
use NEM\Core\Encryption;
use \ParagonIE_Sodium_Core_Ed25519;

/**
 * This is the KeyGenerator class
 *
 * This class implements the NEM Public Key derivation
 * system. Public Keys on NEM are generated from a Keccak-512
 * hash of the KeyPair's secret key.
 * 
 * Scalar multiplication is done using the ParagonIE library
 * internally. See the `sk_to_pk` call which handle ED25519.
 */
class KeyGenerator
{
    /**
     * Derive the public key from the KeyPair's secret.
     *
     * This method uses Keccak 64 bytes (512 bits) hashing on
     * the provided `keyPair`'s secret key.
     *
     * @param   \NEM\Core\KeyPair   $keyPair    The initialized key pair from which to derive the public key.
     * @return  \NEM\Core\Buffer
     */
    public function derivePublicKey(KeyPair $keyPair)
    {
        $buffer = new Buffer;

        // hash the secret key with Keccak SHA3 variation with 512-bit output (64 bytes)
        $hashedSecret = Encryption::hash("keccak-512", $keyPair->getSecretKey()->getBinary(), true); // raw=true

        // clamp bits of the scalar *before* scalar multiplication
        $safeSecret = Buffer::clampBits($hashedSecret);

        // do scalar multiplication for: `basePoint` * `safeSecret`
        // the result of this multiplication is the `publicKey`.
        // sk_to_pk() does scalarmult_base() and ge_p3_tobytes()
        $publicKey = ParagonIE_Sodium_Core_Ed25519::sk_to_pk($safeSecret);
        $publicBuf = new Buffer($publicKey, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);

        assert(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES === $publicBuf->getSize());
        return $publicBuf;
    }
}
