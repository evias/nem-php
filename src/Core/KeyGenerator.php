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

use Mdanter\Ecc\EccFactory;
use kornrunner\Keccak;

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
        $eccSecret = sodium_crypto_sign_ed25519_sk_to_curve25519($hashedSecret);

        // crypto_box_publickey_from_secretkey() = sodium_crypto_scalarmult_base()
        $publicKey = sodium_crypto_box_publickey_from_secretkey($eccSecret);

        // public key is `basePoint` * `publicKey`
        $basePoint = Buffer::fromHex("5866666666666666666666666666666666666666666666666666666666666666", 32);
        $eccPublic = sodium_crypto_scalarmult($basePoint->getBinary(), $publicKey);

        dd(bin2hex($publicKey), bin2hex($eccPublic));

        assert(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES === $eccPublic->getSize());
        return $eccPublic;
    }
}
