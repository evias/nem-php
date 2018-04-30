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
namespace NEM\Models;

use NEM\Errors\NISInvalidNetworkName;
use NEM\Errors\NISInvalidNetworkId;
use NEM\Errors\NISInvalidPublicKeyFormat;
use NEM\Infrastructure\Network;
use NEM\Contracts\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encoder;
use NEM\Core\Encryption;
use kornrunner\Keccak;
use Base32\Base32;

/**
 * This is the Address class
 *
 * This class extends the NEM\Models\Model class
 * to provide with an integration of NEM's Wallet 
 * Addresses objects.
 * 
 * @link https://nemproject.github.io/
 */
class Address
    extends Model
{
    /**
     * The number of characters of a NEM Address.
     *
     * @var integer
     */
    const BYTES = 40;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "address",
        "publicKey",
        "privateKey"
    ];

    /**
     * Generate an address corresponding to a `publicKey`
     * public key.
     *
     * The `publicKey` parameter can be either a hexadecimal
     * public key representation, a byte level representation
     * of the public key, a Buffer object or a KeyPair object.
     *
     * @param   mixed           $publicKey
     * @param   string|integer  $networkId        A network ID OR a network name. (default mainnet)
     * @return  \NEM\Models\Address
     * @throws  \NEM\Errors\NISInvalidPublicKeyFormat   On unidentifiable public key format.
     * @throws  \NEM\Errors\NISInvalidNetworkName       On invalid network name provided in `version` (when string).
     * @throws  \NEM\Errors\NISInvalidVersionByte       On invalid network byte provided in `version` (when integer).
     */
    static public function fromPublicKey($publicKey, $networkId = 104) // 104=mainnet
    {
        // discover public key content
        if ($publicKey instanceof Buffer) {
            $pubKeyBuf = $publicKey; // Buffer to public key
        }
        elseif ($publicKey instanceof KeyPair) {
            $pubKeyBuf = $publicKey->getPublicKey(null); // Keypair to public key
        }
        elseif (is_string($publicKey) && ctype_xdigit($publicKey)) {
            $pubKeyBuf = Buffer::fromHex($publicKey, 32); // Hexadecimal to public key
        }
        elseif (is_string($publicKey) && mb_strlen($publicKey) === 32) {
            $pubKeyBuf = new Buffer($publicKey, 32); // Binary to public key
        }
        else {
            throw new NISInvalidPublicKeyFormat("Could not identify public key format: " . var_export($publicKey, true));
        }

        // discover network name / version byte
        if (is_string($networkId) && !is_numeric($networkId) 
            && in_array(strtolower($networkId), ["mainnet", "testnet", "mijin"])) {
            // network name provided, read version byte from SDK
            $networkId = Network::$networkInfos[strtolower($networkId)]["id"];
        }
        elseif (is_numeric($networkId) && !in_array($networkId, [104, -104, 96])) {
            throw new NISInvalidNetworkId("Invalid netword ID '" . $networkId . "'");
        }
        // network name / version byte is important for address creation
        elseif (is_string($networkId)) {
            throw new NISInvalidNetworkName("Invalid network name '" . $networkId . "'");
        }

        // step 1: keccak-256 hash of the public key
        $pubKeyHash = Encryption::hash("keccak-256", $pubKeyBuf->getBinary(), true); // raw=true

        // step 2: ripemd160 hash of (1)
        $pubKeyRiped = new Buffer(hash("ripemd160", $pubKeyHash, true), 20);

        // step 3: add version byte in front of (2)
        $networkPrefix = Network::getPrefixFromId($networkId);
        $versionPrefixedPubKey = Buffer::fromHex($networkPrefix . $pubKeyRiped->getHex());

        // step 4: get the checksum of (3)
        $checksum = Encryption::checksum("keccak-256", $versionPrefixedPubKey, 4); // checksumLen=4

        // step 5: concatenate (3) and (4)
        $addressHash = $versionPrefixedPubKey->getHex() . $checksum->getHex();
        $hashBuf = Buffer::fromHex($addressHash);
        $encodedAddress = hex2bin($addressHash);

        // step 6: base32 encode (5)
        $encodedBase32  = new Buffer(Base32::encode($encodedAddress), Address::BYTES);

        return new Address([
            "address" => $encodedBase32->getBinary(),
            "publicKey" => $pubKeyBuf->getHex(),
        ]);
    }

    /**
     * Getter for singular attribute values by name.
     *
     * Overloaded to provide with specific CLEAN FORMATTING
     * always when trying to read address attributes.
     *
     * @param   string  $alias   The attribute field alias.
     * @return  mixed
     */
    public function getAttribute($alias, $doCast = true)
    {
        if ($alias === 'address')
            return $this->toClean();

        return parent::getAttribute($alias, $doCast);
    }

    /**
     * Address DTO automatically cleans address representation.
     *
     * @see [KeyPairViewModel](https://nemproject.github.io/#keyPairViewModel)
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO($filterByKey = null)
    {
        $toDTO = ["address" => $this->toClean()];

        // KeyPair's public key/private key not always set
        // because \NEM\Models\Address is used for simple Address formatting
        if (!empty($this->publicKey))
            $toDTO["publicKey"] = $this->publicKey;

        if (!empty($this->privateKey))
            $toDTO["privateKey"] = $this->privateKey;

        if ($filterByKey && isset($toDTO[$filterByKey]))
            return $toDTO[$filterByKey];

        return $toDTO;
    }

    /**
     * Helper to clean an address of any non alpha-numeric characters
     * back to the actual Base32 representation of the address.
     *
     * @return string
     */
    public function toClean($string = null)
    {
        $attrib = $string;
        if (! $attrib && isset($this->attributes["address"])) 
            $attrib = $this->attributes["address"];

        return strtoupper(preg_replace("/[^a-zA-Z0-9]+/", "", $attrib));
    }

    /**
     * Helper to add dashes to Base32 address representations.
     *
     * @return string
     */
    public function toPretty()
    {
        $clean = $this->toClean();
        return trim(chunk_split($clean, 6, '-'), " -");
    }
}
