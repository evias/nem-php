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
namespace NEM\Models;

use NEM\Errors\NISInvalidNetworkName;
use NEM\Errors\NISInvalidVersionByte;
use NEM\Infrastructure\Network;
use NEM\Contracts\KeyPair;
use NEM\Core\Buffer;
use NEM\Core\Encoder;
use NEM\Core\Encryption;
use kornrunner\Keccak;

class Address
    extends Model
{
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
     * The Base32/Hex encoder.
     *
     * @var \NEM\Core\Encoder 
     */
    protected $encoder;

    /**
     * Generate an address corresponding to a `publicKey`
     * public key.
     *
     * The `publicKey` parameter can be either of a hexadecimal
     * public key representation, a byte level representation
     * of the public key, a Buffer object or a KeyPair object.
     *
     * @param   mixed           $publicKey
     * @param   string|integer  $networkId        A network ID OR a network name.  
     * @return  \NEM\Models\Address
     * @throws  \NEM\Errors\NISInvalidPublicKeyFormat   On unidentifiable public key format.
     * @throws  \NEM\Errors\NISInvalidNetworkName       On invalid network name provided in `version` (when string).
     * @throws  \NEM\Errors\NISInvalidVersionByte       On invalid network byte provided in `version` (when integer).
     */
    static public function fromPublicKey($publicKey, $networkId = 0x68)
    {
        // discover public key content
        if ($publicKey instanceof Buffer) {
            $pubKeyBuf = $publicKey;
        }
        elseif ($publicKey instanceof KeyPair) {
            $pubKeyBuf = $publicKey->getPublicKey(null);
        }
        elseif (is_string($publicKey) && ctype_xdigit($publicKey)) {
            $pubKeyBuf = Buffer::fromHex($publicKey, 32);
        }
        elseif (is_string($publicKey)) {
            $pubKeyBuf = new Buffer($publicKey, 32);
        }
        else {
            throw new NISInvalidPublicKeyFormat("Could not identify public key format: " . var_export($publicKey));
        }

        // discover network name / version byte
        if (is_string($networkId) && !is_numeric($networkId) 
            && in_array(strtolower($networkId), ["mainnet", "testnet", "mijin"])) {
            // network name provided, read version byte from SDK
            $networkId = Network::$networkInfos[strtolower($networkId)]["byte"];
        }
        // network name / version byte is important for address creation
        elseif (is_string($networkId)) {
            throw new NISInvalidNetworkName("Invalid network name '" . $networkId . "'");
        }
        elseif (is_integer($networkId) && !in_array($networkId, [0x68, 0x98, 0x60])) {
            throw new NISInvalidnetworkIdByte("Invalid version byte '" . $networkId . "'");
        }

        // instantiate address encoder
        $obj = new static;
        $enc = $obj->getEncoder();

        // step 1: keccak-256 hash of the public key
        $pubKeyHash = Keccak::hash($pubKeyBuf->getBinary(), 256, true); // raw=true

        // step 2: ripemd160 hash of (1)
        $step2Riped = new Buffer(hash("ripemd160", $pubKeyHash, true), 20);

        // step 3: add version byte in front of (2)
        $networkPrefix = Network::getPrefixFromId($networkId);
        $versionPrefixedPubKey = Buffer::fromHex($networkPrefix . $step2Riped->getHex());

        // step 4: get the checksum of (3)
        $checksum = Encryption::checksum("keccak-256", $versionPrefixedPubKey, 4); // checksumLen=4
        $hexedPart4 = $versionPrefixedPubKey->getHex() . $checksum->getHex();

        // step 5: concatenate (3) and (4)
        $encodedAddress = $enc->hex2chr($hexedPart4);
        $encodedBuffer  = new Buffer($encodedAddress);
        //dd($hexedPart4, $encodedAddress, $enc->base32_encode($encodedAddress), $enc->base32_encode($hexedPart4));

        // step 6: base32 encode (5)
        return new Address(["address" => $enc->base32_encode($encodedAddress)]);
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

    /**
     * Getter for the `encoder` property.
     *
     * @return  \NEM\Core\Encoder
     */
    public function getEncoder()
    {
        if (!isset($this->encoder)) {
            $this->encoder = new Encoder();
        }

        return $this->encoder;
    }

    /**
     * Setter for the `encoder` property.
     *
     * @param   \NEM\Core\Encoder    $encoder
     * @return  \NEM\Models\Address
     */
    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }
}
