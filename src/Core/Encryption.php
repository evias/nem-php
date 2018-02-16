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
use NEM\Core\KeccakHasher;
use NEM\Core\KeccakSponge;
use \ParagonIE_Sodium_Compat;
use \ParagonIE_Sodium_Core_Ed25519 as Ed25519;
use \ParagonIE_Sodium_Core_X25519 as Ed25519ref10;
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
     * Helper to prepare a `data` attribute into a \NEM\Core\Buffer
     * object for easier internal data representations.
     * 
     * @param   null|string|\NEM\Core\Buffer    $data   The data that needs to be added to the returned Buffer.
     * @return  \NEM\Core\Buffer
     */
    protected static function prepareInputBuffer($data)
    {
        if ($data instanceof Buffer) {
            return $data;
        }
        elseif (is_string($data) && ctype_xdigit($data)) {
            return Buffer::fromHex($data);
        }
        elseif (is_string($data)) {
            return new Buffer($data);
        }

        return new Buffer((string) $data);
    }

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
     * @param   string|NEM\Core\Buffer  $password   Password for key derivation as *Buffer*.
     * @param   string|NEM\Core\Buffer  $salt       Salt for key derivation as *Buffer*.
     * @param   integer                 $count      Count of Derivation iterations.
     * @param   integer                 $keyLength  Length of produced Key (count of Bytes).
     * @return  NEM\Core\Buffer
     *
     * @throws  RuntimeException            On invalid hash algorithm (maybe missing php extension)
     * @throws  InvalidArgumentException    On negative *$keyLength* argument.
     * @throws  InvalidArgumentException    On invalid derivation iterations *$count* or invalid *$keyLength* arguments.
     */
    public static function derive($algorithm, $password, $salt, $count = 6000, $keyLength = 64) // 6000=NanoWallet, 64=512bits
    {
        // shortcuts + use Buffer always
        $password = self::prepareInputBuffer($password);
        $salt = self::prepareInputBuffer($salt);

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
     * @param   string                     $algo
     * @param   string|\NEM\Core\Buffer    $data
     * @param   boolean                    $returnRaw 
     * @return  \NEM\Core\Buffer
     */
    public static function hash($algo, $data, $returnRaw = false)
    {
        // shortcuts + use Buffer always
        $data = self::prepareInputBuffer($data);

        if (in_array($algo, hash_algos())) {
            // use PHP hash()
            $hash = hash($algo, $data->getBinary(), true);
        }
        elseif (strpos(strtolower($algo), "keccak-") !== false) {
            // use Keccak instead of PHP hash()
            $hash = KeccakHasher::hash($algo, $data->getBinary(), true);
        }
        else {
            throw new RuntimeException("Unsupported hash algorithm '" . $algo . "'.");
        }

        if ((bool) $returnRaw) {
            return $hash;
        }

        return new Buffer($hash);
    }

    /**
     * Helper to initialize *a incremental Hasher* for the hashing
     * algorithm `algorithm`.
     * 
     * This method will return a Resource that can be passed to the
     * hash_update() method.
     * 
     * @param   string              $algorithm
     * @return  resource|\NEM\Core\KeccakSponge
     */
    public static function hash_init($algorithm)
    {
        if (in_array($algorithm, hash_algos())) {
            // use PHP hash()
            $res = hash_init($algorithm);
        }
        elseif (strpos(strtolower($algorithm), "keccak") !== false) {
            // use Keccak instead of PHP hash()
            $res = KeccakHasher::hash_init($algorithm);
        }
        else {
            throw new RuntimeException("Unsupported hash algorithm '" . $algo . "'.");
        }

        //XXX should return Hasher class to keep track of key size
        return $res;
    }

    /**
     * Helper to update *a incremental Hasher* with some data.
     * 
     * This method will edit the Resource directly.
     * 
     * @param   resource|KeccakSponge        $hasher
     * @param   string|\NEM\Core\Buffer     $data
     * @return  resource|\NEM\Core\KeccakSponge
     */
    public static function hash_update($hasher, $data)
    {
        // use Buffer always
        $data = self::prepareInputBuffer($data);

        if ($hasher instanceof KeccakSponge) {
            return KeccakHasher::hash_update($hasher, $data->getBinary(), $data->getInternalSize() * 8);
        }

        //XXX should use Hasher class to keep track of key size
        return hash_update($hasher, $data->getBinary());
    }

    /**
     * Helper to finalize *a incremental Hasher*.
     * 
     * This will close the Input Phase of the incremental
     * hashing mechanism.
     * 
     * @param   resource|KeccakSponge    $hasher
     * @param   bool                    $returnRaw
     * @return  \NEM\Core\Buffer|string
     */
    public static function hash_final($hasher, $returnRaw = false)
    {
        if ($hasher instanceof KeccakSponge) {
            // use Keccak internal hasher
            $hash = KeccakHasher::hash_final($hasher, true);
        }
        else {
            // use PHP hasher
            $hash = hash_final($hasher, true);
        }
        
        if ((bool) $returnRaw) {
            return $hash;
        }

        //XXX should use Hasher class to keep track of key size
        return new Buffer($hash);
    }

    /**
     * HMAC : Hash based Message Authentication Code
     *
     * A MAC authenticates a message. It is a signature based on a secret key (salt).
     *
     * @param   string                  $algorithm  Which hash algorithm to use.
     * @param   string|NEM\Core\Buffer  $data
     * @param   string|NEM\Core\Buffer  $salt
     * @return  NEM\Core\Buffer
     */
    public static function hmac($algo, $data, $salt)
    {
        // shortcuts + use Buffer always
        $data = self::prepareInputBuffer($data);
        $salt = self::prepareInputBuffer($salt);

        return new Buffer(hash_hmac($algo, $data->getBinary(), $salt->getBinary(), true));
    }

    /**
     * Generate a checksum of data buffer `data` and of length
     * `checksumLen`. Default length is 4 bytes.
     *
     * @param   string                     $algo
     * @param   string|\NEM\Core\Buffer    $data
     * @param   integer                    $checksumLen
     * @return  \NEM\Core\Buffer 
     */
    public static function checksum($algo, $data, $checksumLen = 4)
    {
        // shortcuts + use Buffer always
        $data = self::prepareInputBuffer($data);
        $hash = self::hash($algo, $data, false)->getBinary();
        $out = new Buffer(substr($hash, 0, $checksumLen), $checksumLen);
        return $out;
    }

    /**
     * This method lets you sign `data` with a given `secretKey`.
     * 
     * Beware that the `secretKey` is not your privateKey but the 
     * `KeyPair->getSecretKey()`.
     * 
     * @param   \NEM\Core\KeyPair           $keyPair       The KeyPair used for encryption.
     * @param   string|\NEM\Core\Buffer     $data           The data that you want to sign.
     * @param   string                      $algorithm      The hash algorithm used for signature creation.
     * @return  \NEM\Core\Buffer
     */
    public static function sign(KeyPair $keyPair, $data, $algorithm = "keccak-512")
    {
        // shortcuts + use Buffer always
        $data = self::prepareInputBuffer($data);

        // use detached signature implementation
        return self::signDetached($keyPair, $data, $algorithm);
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
     * @param   \NEM\Core\KeyPair           $keyPair       The KeyPair used for encryption.
     * @param   string|\NEM\Core\Buffer     $data          The data that you want to sign. 
     * @param   string                      $algorithm      The hash algorithm used for signature creation.
     * @return  \NEM\Core\Buffer
     */
    public static function signDetached(KeyPair $keyPair, $data, $algorithm = "keccak-512")
    {
        // shortcuts
        $secretKey = $keyPair->getSecretKey()->getBinary();
        $publicKey = $keyPair->getPublicKey()->getBinary();

        // use Buffer always
        $data = self::prepareInputBuffer($data);
        $message   = $data->getBinary();

        // crypto_hash_sha512(az, sk, 32);
        $privHash = self::hash($algorithm, $keyPair->getSecretKey()->getBinary(), true);

        // clamp bits for secret key + size secure
        $safePriv = Buffer::clampBits($privHash, 64);
        $bufferPriv = new Buffer($safePriv, 64);

        // generate `r` for scalar multiplication
        // `r = H(priv[32..64], data)`
        $hashData = $bufferPriv->slice(32)->getBinary() . $data->getBinary();
        $r = self::hash($algorithm, $hashData, true);

        // generate encoded version of `r` for `s` creation
        // `R = rB`
        $r = Ed25519::sc_reduce($r);
        $encodedR = Ed25519::ge_p3_tobytes(
            Ed25519::ge_scalarmult_base($r)
        );

        // create `s` with: encodedR || public key || data
        // `S = H(R,A,m)`
        $hramData = $encodedR . $publicKey . $data->getBinary();
        $hramHash = self::hash($algorithm, $hramData, true);

        // safe secret generation for `encodedS` which is the HIGH part of
        // the signature in scalar form.
        // `S = H(R,A,m)a`
        // `S = (r + H(R,A,m)a)`
        $hram = Ed25519::sc_reduce($hramHash);
        $az   = $safePriv;
        $encodedS = Ed25519::sc_muladd($hram, $az, $r);

        // size secure encodedR and encodedS
        $bufferR = new Buffer($encodedR, 32);
        $bufferS = new Buffer($encodedS, 32);

        // signature[0:63] = r[0:31] || s[0:31]
        $sig = $bufferR->getBinary() . $bufferS->getBinary();

        // size secure signature
        $bufferSig = new Buffer($sig, 64);

        // check that signature is canonical
        // - s != 0
        // - ed25519 reduced `s` should be identical to `s`.
        // `S = (r + H(R,A,m)a) mod L`

        // make sure `s` != 0
        $sigZero = new Buffer(null, 32);
        if ($encodedS === $sigZero->getBinary()) {
            // re-issue signature because `s = 0`
            return false;
        }

        // create 64-bytes size-secured Buffer.
        $bufSignature = new Buffer($sig, 64);
        return $bufSignature;
    }
}
