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

use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Crypto;
use \ParagonIE_Sodium_Core_Ed25519;

class KeyGenerator
{
    /**
     * Derive the public key from the KeyPair's secret.
     *
     * @param   \NEM\Core\KeyPair   $keyPair    The initialized key pair from which to derive the public key.
     * @return  \NEM\Core\Buffer
     */
    public function derivePublicKey(KeyPair $keyPair)
    {
        $buffer = new Buffer;

        // secret key is the byte-level-reversed representation of the private key.
        $secretBuf = Buffer::fromHex($keyPair->getSecretKey(), 32);

        // hash the secret key with Keccak SHA3 variation with 512-bit output (64 bytes)
        $hashedSecret = Keccak::hash($secretBuf->getBinary(), 512, true);
        $hashedBuffer = new Buffer($hashedSecret, 64);
        $clampSecret  = $hashedBuffer->toUInt8();

        $clampSecret[0] &= 0xf8;
        $clampSecret[31] &= 0x7f;
        $clampSecret[31] |= 0x40;

        $encoder = new Encoder;
        $publicKey = ParagonIE_Sodium_Core_Ed25519::sk_to_pk($encoder->ua2bin($clampSecret)->getBinary());
        $publicBuf = new Buffer($publicKey, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);

        assert(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES === $publicBuf->getSize());
        return $publicBuf;
    }
}
