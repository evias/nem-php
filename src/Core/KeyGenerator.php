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
        $publicBuf = new Buffer(null, 32);

        // secret key if the byte-level-reversed representation of the private key.
        $secretBuf = Buffer::fromHex($keyPair->getSecretKey());

        $publicK = hash("sha3-256", implode("", $secretBuf->ua2words()), true);
        $publicKey = new Buffer($publicK, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
        return $publicKey;

/*
        echo PHP_EOL;
        echo "SECRET: " . $secretBuf->getHex() . PHP_EOL;
        echo "UINT8: (" . count($secretBuf->toUInt8()) . ") [" . implode(", ", $secretBuf->toUInt8()) . "]" . PHP_EOL;
        echo "ua2words(): (" . count($secretBuf->ua2words()) . ") [" . implode(", ", $secretBuf->ua2words()) . "]" . PHP_EOL;

        echo PHP_EOL;

        $hash_words = hash("sha3-512", implode("", $secretBuf->ua2words()), true);
        $hash_int32 = hash("sha3-512", implode("", array_map(function($item) { return gmp_strval($item, 10); }, $secretBuf->ua2words())), true);
        $hash_sha3 = hash("sha256", implode("", $secretBuf->ua2words()), true);

        $buf_words = new Buffer($hash_words, 64);
        $buf_int32 = new Buffer($hash_int32, 64);
        $buf_sha3  = new Buffer($hash_sha3);
        //= $noctx_str = hash("sha3-512", implode("", array_map(function($item) { return gmp_strval($item); }, $secretBuf->ua2words())));
        //= $noctx_dec = hash("sha3-512", implode("", array_map(function($item) { return gmp_strval($item, 10); }, $secretBuf->ua2words())));

        $noctx_bin = hash("sha3-512", $secretBuf->getBinary());
        $noctx_b16 = hash("sha3-512", implode("", array_map(function($item) { return gmp_strval($item, 16); }, $secretBuf->ua2words())));
        $noctx_whex = hash("sha3-512", $secretBuf->words2hex());
        $noctx_hm = hash_hmac("sha3-512", implode("", $secretBuf->ua2words()), implode("", $secretBuf->ua2words()));

        echo "CONTEXT SHA3: " . PHP_EOL;
        echo "> hash_words: " . bin2hex($hash_words) . PHP_EOL;
        echo "> hash_int32: " . bin2hex($hash_int32) . PHP_EOL;
        echo "> hash_sha3 : " . bin2hex($hash_sha3) . PHP_EOL;
        echo "> " . $noctx_bin . PHP_EOL;
        //echo "> " . $noctx_str . PHP_EOL;
        echo "> " . $noctx_b16 . PHP_EOL;
        //echo "> " . $noctx_dec . PHP_EOL;
        echo "> " . $noctx_whex . PHP_EOL;
        echo "> " . $noctx_hm . PHP_EOL;
        echo "> buf_words.toUInt8: (" . count($buf_words->toUInt8()) . ") [" . implode(", ", $buf_words->toUInt8()) . "]" . PHP_EOL;
        echo "> buf_words.ua2words: (" . count($buf_words->ua2words()) . ") [" . implode(", ", $buf_words->ua2words()) . "]" . PHP_EOL;
        echo "> buf_int32.toUInt8: (" . count($buf_int32->toUInt8()) . ") [" . implode(", ", $buf_int32->toUInt8()) . "]" . PHP_EOL;
        echo "> buf_int32.ua2words: (" . count($buf_int32->ua2words()) . ") [" . implode(", ", $buf_int32->ua2words()) . "]" . PHP_EOL;
        echo "> buf_sha3.toUInt8: (" . count($buf_sha3->toUInt8()) . ") [" . implode(", ", $buf_sha3->toUInt8()) . "]" . PHP_EOL;
        echo "> buf_sha3.ua2words: (" . count($buf_sha3->ua2words()) . ") [" . implode(", ", $buf_sha3->ua2words()) . "]" . PHP_EOL;
        exit;

        $hashedPriv = Buffer::fromHex($privateKey->hash("sha512"));

        // prepare for scalar multiplication
        $binaryPriv = $hashedPriv->getUInt8Array();

        $binaryPriv[0] &= 0xf8; // 248
        $binaryPriv[31] &= 0x7f; // 127
        $binaryPriv[31] |= 0x40; // 64

        $scalarPriv = Buffer::fromHex((implode("", $binaryPriv)));
dd($scalarPriv, $scalarPriv->getSize(), SODIUM_CRYPTO_SCALARMULT_SCALARBYTES, $scalarPriv->getGmp(10));
        $publicK = sodium_crypto_scalarmult(0, $scalarPriv->getInt());

///////////
        $keypair = sodium_crypto_sign_seed_keypair($priv->getBinary());
        assert(SODIUM_CRYPTO_SIGN_KEYPAIRBYTES === strlen($keypair));

        $shared  = sodium_crypto_sign_secretkey($keypair);
        $secret  = sodium_crypto_sign_ed25519_sk_to_curve25519($shared);

        $binaryD = new Buffer($shared, SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);
        $publicK = sodium_crypto_sign_publickey_from_secretkey($binaryD->getBinary());
        assert(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES === strlen($publicK));
*/
    }

    /**
     * ed25519 Base Point calculation.
     *
     * @return  string
     
    public function getBasePoint()
    {
        $hexBase = Buffer::fromHex("5866666666666666666666666666666666666666666666666666666666666666");
        $baseBin = $hexBase->getBinary();
        
        dd($hexBase->decimalToBinary($hexBase->getInt()));
    }
    */
}