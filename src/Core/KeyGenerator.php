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
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Util;

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
     * Combine two keys into a keypair for use in library methods that expect
     * a keypair. This doesn't necessarily have to be the same person's keys.
     *
     * @param   \NEM\Core\Buffer  $secretKey Secret key
     * @param   \NEM\Core\Buffer  $publicKey Public key
     * @return  \NEM\Core\Buffer
     * @throws SodiumException
     * @throws TypeError
     */
    public function combineKeys(Buffer $secretKey, Buffer $publicKey)
    {
        $keypair = ParagonIE_Sodium_Compat::crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);
        return new Buffer($keypair, 64);
    }

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

    /**
     * Asymmetrical cryptography key derivation function. This method will produce a 
     * shared secret for encryption using Public Key Cryptography.
     * 
     * The `secretKey` parameter is used as the Sender key and the `publicKey` parameter
     * is used as the Recipient of the encrypted message. The `salt` parameter is required
     * because salts should always be random and we need to integrate it to the shared
     * secret to allow decryption.
     * 
     * @param   \NEM\Core\Buffer    $salt
     * @param   \NEM\Core\Buffer    $secretKey
     * @param   \NEM\Core\Buffer    $publicKey
     * @return  \NEM\Core\Buffer
     */
    public function deriveKey(Buffer $salt, Buffer $secretKey, Buffer $publicKey)
    {
        // produce ed25519 shared secret
        $sharedSecret = $this->getSharedSecret($salt, $secretKey, $publicKey);

        // salt the byte-level representation   
        $saltUA   = $salt->toUInt8();
        $sharedUA = $sharedSecret->toUInt8();
        for ($i = 0, $len = count($saltUA); $i < $len; $i++) {
            $sharedUA[$i] ^= $saltUA[$i];
        }

        $sharedSecret = Buffer::fromUInt8($sharedUA)->slice(0, 32);
        $hashedSecret = Encryption::hash("keccak-256", $sharedSecret);
        return $hashedSecret;
    }

    /**
     * Get the asymmetrical cryptography *Shared Secret* between `secretKey` sender
     * secret key (reversed private key) and `publicKey` recipient public key.
     * 
     * @param   \NEM\Core\Buffer    $salt
     * @param   \NEM\Core\Buffer    $secretKey
     * @param   \NEM\Core\Buffer    $publicKey
     * @return  \NEM\Core\Buffer
     */
    public function getSharedSecret(Buffer $salt, Buffer $secretKey, Buffer $publicKey)
    {
        $unsafeSecret = $secretKey->getBinary() . $publicKey->getBinary();
        $keccakSecret = ParagonIE_Sodium_Core_Util::substr(
            Encryption::hash("keccak-512", $secretKey->getBinary(), true),
            0, 32
        );

        // generate safe secret
        $safeSecret = Buffer::clampBits($keccakSecret, 32);
        $sharedSecret = ParagonIE_Sodium_Compat::crypto_scalarmult($safeSecret, $publicKey->getBinary());
        $sharedBuf = new Buffer($sharedSecret, 32);
        return $sharedBuf;
    }
}
