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
use NEM\Core\Encoder;
use kornrunner\Keccak;
use \ParagonIE_Sodium_Core_Ed25519;

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
        $hashedSecret = Keccak::hash($keyPair->getSecretKey()->getBinary(), 512, true); // raw=true

        // clamp bits of the scalar *before* scalar multiplication
        $safeSecret = $this->clampBits($hashedSecret);

        // do scalar multiplication for: `basePoint` * `safeSecret`
        // the result of this multiplication is the `publicKey`.
        // sk_to_pk() does scalarmult_base() and ge_p3_tobytes()
        $publicKey = ParagonIE_Sodium_Core_Ed25519::sk_to_pk($safeSecret);
        $publicBuf = new Buffer($publicKey, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);

        assert(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES === $publicBuf->getSize());
        return $publicBuf;
    }

    /**
     * Convert 64 Bytes Keccak SHA3-512 Hashes into a Secret Key.
     * 
     * @param   string  $unsafeSecret   A 64 bytes (512 bits) Keccak hash produced from a KeyPair's Secret Key.
     * @return  string                  Byte-level representation of the Secret Key.
     */
    protected function clampBits($unsafeSecret)
    {
        if ($unsafeSecret instanceof Buffer) {
            // copy-construct to avoid malformed and wrong size
            $toBuffer = new Buffer($unsafeSecret->getBinary(), 64);
        }
        elseif (! ctype_xdigit($unsafeSecret)) {
            // build from binary
            $toBuffer = new Buffer($unsafeSecret, 64);
        }
        else {
            $toBuffer = Buffer::fromHex($unsafeSecret, 64);
        }

        // clamping bits
        $clampSecret  = $toBuffer->toUInt8();
        $clampSecret[0] &= 0xf8; // 248
        $clampSecret[31] &= 0x7f; // 127
        $clampSecret[31] |= 0x40; // 64

        // build Buffer object from UInt8 and return byte-level representation
        $encoder = new Encoder;
        $safeSecret = $encoder->ua2bin($clampSecret)->getBinary();
        return $safeSecret;
    }
}
