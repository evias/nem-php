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
    protected $fillable = ["address"];

    /**
     * Address DTO automatically cleans address representation.
     *
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO()
    {
        return ["address" => $this->toClean()];
    }

    /**
     * Helper to clean an address of any non alpha-numeric characters
     * back to the actual Base32 representation of the address.
     *
     * @return string
     */
    public function toClean()
    {
        if (empty($this->address))
            return "";

        return strtoupper(preg_replace("/[^a-zA-Z0-9]+/", "", $this->address));
    }

    /**
     * Helper to add dashes to Base32 address representations.
     *
     * @return string
     */
    public function toPretty()
    {
        if (empty($this->address))
            return "";

        return trim(chunk_split($this->address, 6, '-'), " -");
    }
}
