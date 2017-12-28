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
