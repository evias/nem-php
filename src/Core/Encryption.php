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
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Core;

use NEM\Core\KeyPair;
use NEM\Core\Buffer;
use kornrunner\Keccak;
use \SHA3;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519ref10;
use \SodiumException;
use RuntimeException;

class Encryption
{
    /**
     * The progressive hasher algorithm.
     * 
     * The `algorithm` property also accepts keccak-X 
     * denominations.
     *
     * @see https://php.net/hash_algos
     * @var string
     */
    static public $algorithm = "keccak-512";

    /**
     * PBKDF2 : Password Based Key Derivation Function
     *
     * For the name of selected hashing algorithms (i.e. md5,
     * sha256, haval160,4, etc..), see hash_algos() for a list
     * of supported algorithms.
     *
     * This method can be used when PBKDF2 must be used, typically with
     * NEM this is used to derive a Private key off a Password.
     * 
     * @param   string                  $algorithm  Which hash algorithm to use for key derivation.
     * @param   NEM\Core\Buffer         $password   Password for key derivation as *Buffer*.
     * @param   NEM\Core\Buffer         $salt       Salt for key derivation as *Buffer*.
     * @param   integer                 $count      Count of Derivation iterations.
     * @param   integer                 $keyLength  Length of produced Key (count of Bytes).
     * @return  NEM\Core\Buffer
     *
     * @throws  RuntimeException            On invalid hash algorithm (maybe missing php extension)
     * @throws  InvalidArgumentException    On negative *$keyLength* argument.
     * @throws  InvalidArgumentException    On invalid derivation iterations *$count* or invalid *$keyLength* arguments.
     */
    public static function derive($algorithm, Buffer $password, Buffer $salt, $count = 6000, $keyLength = 64) // 6000=NanoWallet, 64=512bits
    {
        if ($keyLength < 0) {
            throw new InvalidArgumentException('Cannot have a negative key-length for PBKDF2');
        }

        $algorithm  = strtolower($algorithm);

        if (!in_array($algorithm, hash_algos(), true)) {
            throw new RuntimeException('PBKDF2 ERROR: Invalid hash algorithm');
        }

        if ($count <= 0 || $keyLength <= 0) {
            throw new InvalidArgumentException('PBKDF2 ERROR: Invalid parameters.');
        }

        // Get binary data of derived key and wrap in Buffer
        return new Buffer(\hash_pbkdf2($algorithm, $password->getBinary(), $salt->getBinary(), $count, $keyLength, true), $keyLength);
    }

    /**
     * Helper to hash the provided buffer `data`'s content
     * with algorithm `algo`.
     * 
     * The hash algorithm can contain `keccak-256` for example.
     * 
     * @param   string              $algo
     * @param   \NEM\Core\Buffer    $data
     * @return  \NEM\Core\Buffer
     */
    public static function hash($algo, Buffer $data)
    {
        if (in_array($algo, hash_algos())) {
            $hash = hash($algo, $data->getBinary(), true);
        }
        if (strpos(strtolower($algo), "keccak") !== false) {
            $bits = (int) substr($algo, -3); // keccak-256, keccak-512, etc.

            // use Keccak instead of PHP hash()
            $hash = Keccak::hash($data->getBinary(), $bits, true);
        }
        else {
            throw new RuntimeException("Unsupported hash algorithm '" . $algo . "'.");
        }

        return new Buffer($hash);
    }

    /**
     * HMAC : Hash based Message Authentication Code
     *
     * A MAC authenticates a message. It is a signature based on a secret key (salt).
     *
     * @param   string              $algorithm  Which hash algorithm to use.
     * @param   NEM\Core\Buffer     $data
     * @param   NEM\Core\Buffer     $salt
     * @return  NEM\Core\Buffer
     */
    public static function hmac($algo, Buffer $data, Buffer $salt)
    {
        return new Buffer(hash_hmac($algo, $data->getBinary(), $salt->getBinary(), true));
    }

    /**
     * Generate a checksum of data buffer `data` and of length
     * `checksumLen`. Default length is 4 bytes.
     *
     * @param   string              $algo
     * @param   \NEM\Core\Buffer    $data
     * @param   integer             $checksumLen
     * @return  \NEM\Core\Buffer 
     */
    public static function checksum($algo, Buffer $data, $checksumLen = 4)
    {
        $hash = static::hash($algo, $data)->getBinary();
        $out = new Buffer(substr($hash, 0, $checksumLen), $checksumLen);
        return $out;
    }

    /**
     * Helper for encryption using a *sender private key* and *recipient public
     * key*.
     *
     * @param   string              $data               Plain text content of the Message to encrypt.
     * @param   \NEM\Core\KeyPair   $senderPrivateKey   Private Key of the Sender.
     * @param   \NEM\Core\KeyPair   $recipientPubKey    Public Key of the Recipient.
     * @return  string
     */
    public function encrypt($data, KeyPair $recipientPrivateKey, KeyPair $senderPublicKey)
    {
        return "";
    }

    /**
     * Helper for decryption using a *recipient private key* and *sender public
     * key*.
     *
     * @param   \NEM\Core\Buffer    $payload                An encrypted message payload.
     * @param   \NEM\Core\KeyPair   $recipientPrivateKey    Private Key of the Sender.
     * @param   \NEM\Core\KeyPair   $senderPubKey           Public Key of the Recipient.
     * @return  string
     */
    public function decrypt(Buffer $payload, KeyPair $recipientPrivateKey, KeyPair $senderPublicKey)
    {
        return "";
    }

    /**
     * This method lets you sign `data` with a given `secretKey`.
     * 
     * Beware that the `secretKey` is not your privateKey but the 
     * `KeyPair->getSecretKey()`.
     * 
     * @param   \NEM\Core\Buffer    $secretKey      The KeyPair calculated *secretKey* (byte-level reversed private key)
     * @param   \NEM\Core\Buffer    $data           The data that you want to sign.
     * @param   string              $algorithm      The hash algorithm used for signature creation.
     * @return  \NEM\Core\Buffer
     */
    public static function sign(KeyPair $keyPair, Buffer $data, $algorithm = "keccak-512")
    {
        // prepare sodium overload
        self::$algorithm = $algorithm ?: "keccak-512";

        // shortcuts
        $message = $data->getHex();
        $secret  = $keyPair->getSecretKey()->getBinary();
        $public  = $keyPair->getPublicKey()->getBinary();

        // use sodium overload
        return self::signDetached($message, $secret, $public);
    }

    /**
     * Overload of the ParagonIE_Sodium_Core_Ed25519::sign_detached method
     * to work with Keccak hashes needed for the NEM Network.
     *
     * More than just keccak-* hashes can be used through this change. Also
     * PHP supported `hash_algos()` are supported here.
     *
     * @see ParagonIE_Sodium_Core_Ed25519::sign_detached()
     * @param   string  $message    The message we need to sign
     * @param   string  $sk         The secret key used for Signing.
     * @param   string  $pk         The public key used for Reading.
     * @return  \NEM\Core\Buffer
     */
    public static function signDetached($message, $sk, $pk)
    {
        $algorithm = self::$algorithm ?: "keccak-512";
        $sha3Size  = SHA3::SHA3_512; // XXX multi-algo

        # crypto_hash_sha512(az, sk, 32);
        $privHash = hash("sha512/256", $sk, true);
        $bufPriv  = new Buffer($privHash, 32); // size security
        $binPriv  = $bufPriv->toUInt8();

        # clamp bits
        $binPriv[0] &= 0xf8; // 248
        $binPriv[31] &= 0x7f; // 127
        $binPriv[31] |= 0x49; // 64

        $encoder  = new Encoder;
        $safePriv = $encoder->ua2bin($binPriv);

        // SHA3=Keccak Hash
        $hs = SHA3::init($sha3Size);
        $hs->absorb(substr($privHash, 32, 32));
        $hs->absorb($message);
        $r = $hs->squeeze(64); // 64=fixedOutputLength

        $r = Ed25519ref10::sc_reduce($r);
        $encodedR = Ed25519ref10::ge_p3_tobytes(
            Ed25519ref10::ge_scalarmult_base($r)
        );

        // encodedR || public || data
        // SHA3=Keccak Hash
        $sigH = SHA3::init($sha3Size);
        $sigH->absorb($encodedR);
        $sigH->absorb($pk);
        $sigH->absorb($message);

        $sig = $sigH->squeeze(64);
        $sig = Ed25519ref10::sc_reduce($sig);
        $encodedS = Ed25519ref10::sc_muladd($sig, $safePriv, $r);

        $sig = substr($sig, 0, 32) . substr($encodedS, 0, 32);

        // create 64-bytes size-secured Buffer.
        $bufSignature = new Buffer($sig, 64);
        return $bufSignature;
/**
        # memmove(sig + 32, sk + 32, 32);
        $pk = self::substr($sk, 32, 32);
//dd($pk);
        # sc_reduce(nonce);
        # ge_scalarmult_base(&R, nonce);
        # ge_p3_tobytes(sig, &R);
        $nonce = self::sc_reduce($nonceHash) . self::substr($nonceHash, 32);
        $sig = self::ge_p3_tobytes(
            self::ge_scalarmult_base($nonce)
        );

        # crypto_hash_sha512_init(&hs);
        # crypto_hash_sha512_update(&hs, sig, 64);
        # crypto_hash_sha512_update(&hs, m, mlen);
        # crypto_hash_sha512_final(&hs, hram);
        $hs = SHA3::init($sha3Size);
        $hs->absorb(self::substr($sig, 0, 32));
        $hs->absorb(self::substr($pk, 0, 32));
        $hs->absorb($message);
        $hramHash = $hs->squeeze(64); // 64=fixedOutputLength

        # sc_reduce(hram);
        # sc_muladd(sig + 32, hram, az, nonce);
        $hram = self::sc_reduce($hramHash);
        $sigAfter = self::sc_muladd($hram, $az, $nonce);
        $sig = self::substr($sig, 0, 32) . self::substr($sigAfter, 0, 32);

        try {
            ParagonIE_Sodium_Compat::memzero($az);
        } catch (SodiumException $ex) {
            $az = null;
        }

        // create 64-bytes size-secured Buffer.
        $bufSignature = new Buffer($sig, 64);
        return $bufSignature;
**/
    }
}
