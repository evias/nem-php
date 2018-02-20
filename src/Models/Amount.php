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

use NEM\Errors\NISAmountOverflowException;

class Amount
    extends Model
{
    /**
     * Define the value of 1 XEM in micro XEM.
     *
     * @var integer
     */
    const XEM = 1000000;

    /**
     * Define the value of 1 micro XEM.
     *
     * @var integer
     */
    const MICRO_XEM = 1;

    /**
     * Define the MAXIMUM AMOUNT.
     *
     * @var integer
     */
    const MAX_AMOUNT = 9000000000000000;

    /**
     * Define the XEM total supply.
     *
     * @var integer
     */
    const XEM_SUPPLY = 8999999999;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = ["amount"];

    /**
     * Store the micro amount.
     *
     * @var integer
     */
    protected $micro;

    /**
     * The NEM Amount divisibility (Min. 0, Max. 6)
     *
     * @var integer
     */
    protected $divisibility = 6;

    /**
     * Factory for Amount models with MICRO amounts and provided
     * `divisibility`.
     *
     * @param   integer     $amount
     * @param   integer     $divisibility
     * @return  \NEM\Models\Amount
     */
    static public function fromMicro($amount, $divisibility = 6)
    {
        $amt = new Amount(["amount" => $amount]);
        $amt->setDivisibility($divisibility);

        return $amt;
    }

    /**
     * Setter for singular attribute values by name.
     *
     * Overload takes care of TOO BIG amounts.
     *
     * @param   string  $name   The attribute name.
     * @param   mixed   $data   The attribute data.
     * @return  mixed
     * @throws  \NEM\Errors\NISAmountOverflowException
     */
    public function setAttribute($name, $data)
    {
        if ($name === 'amount') {
            $this->attributes["amount"] = $data;

            // parse provided data and check for overflow
            $micro = $this->toMicro();
            if ($micro >= Amount::MAX_AMOUNT)
                throw new NISAmountOverflowException("Amounts cannot exceed " . Amount::MAX_AMOUNT . ".");
        }

        return parent::setAttribute($name, $data);
    }

    /**
     * Amount DTO automatically returns MICRO amount.
     *
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO($filterByKey = null)
    {
        $toDTO = ["amount" => $this->toMicro()];

        if ($filterByKey && isset($toDTO[$filterByKey]))
            return $toDTO[$filterByKey];

        return $toDTO;
    }

    /**
     * Helper to return a MICRO amount. This means to get the smallest unit
     * of an Amount on the NEM Blockchain. Maximum Divisibility is up to 6
     * decimal places.
     *
     * @return integer
     */
    public function toMicro()
    {
        $inner = $this->getAttribute("amount", false); //cast=false
        $decimals = $this->getDivisibility();

        if (is_integer($inner)) {
            $attrib = $inner;
        }
        elseif (is_float($inner)) {
            // we want only integer!
            $attrib = $inner * pow(10, $decimals);
        }
        elseif (is_string($inner)) {
            // parse number string representation. Parsing to float.
            $isFloat = false !== strpos($inner, ".");
            $number = $isFloat ? (float) $inner : (int) $inner;
            $multi  = $isFloat ? pow(10, $decimals) : 1;
            $attrib = $number * $multi;
        }
        elseif (is_array($inner)) {
            // try to read first value of array
            $attrib = array_shift($inner);
        }
        else {
            $attrib = (int) $inner;
        }

        $this->micro = (int) $attrib;
        if ($this->micro < 0)
            // not allowed: 0 in micro XEM is the minimum possible value!
            $this->micro = 0;

        return $this->micro;
    }

    /**
     * Helper to return UNITs amounts. This method will return floating
     * point numbers. The divisibility can be set using `setDivisibility`
     * in case of different mosaics.
     *
     * @return float
     */
    public function toUnit()
    {
        if ($this->divisibility <= 0)
            return $this->toMicro();

        $div = pow(10, $this->getDivisibility());
        return ($this->toMicro() / $div);
    }

    /**
     * Setter for the `divisibility` property.
     *
     * @param   integer     $divisibility
     * @return  \NEM\Models\Amount
     */
    public function setDivisibility($divisibility)
    {
        if (!is_integer($divisibility) || $divisibility < 0)
            $divisibility = 6; // default 6

        $this->divisibility = $divisibility;
        return $this;
    }

    /**
     * Getter for the `divisibility` property.
     *
     * @return  integer
     */
    public function getDivisibility()
    {
        return (int) $this->divisibility;
    }

    /**
     * Helper to get the XEM equivalent for a given mosaic definition
     * `definition` and attachment quantity `quantity`.
     *
     * This method is used internally to calculate the equivalent XEM
     * amounts of a given mosaic quantity for *fees calculation*.
     *
     * @internal
     * @param   \NEM\Models\MosaicDefinition  $definition
     * @param   integer                       $quantity
     * @return  integer
     */
    static public function mosaicQuantityToXEM($divisibility, $supply, $quantity, $multiplier = Amount::XEM)
    {
        if ((int) $supply <= 0) return 0;
        if ((int) $divisibility <= 0) $divisibility = 0;

        return self::XEM_SUPPLY * $quantity * $multiplier / $supply / pow(10, $divisibility + 6);
    }
}
