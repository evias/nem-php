<?php
/**
 * Part of the evias/php-nem-laravel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/php-nem-laravel
 * @version    0.1.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
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

        return trim(chunk_split($this->address, 5, '-'), "");
    }
}
