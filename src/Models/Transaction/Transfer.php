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
namespace NEM\Models\Transaction;

use NEM\Models\Transaction;
use NEM\Models\Fee;

class Transfer
    extends Transaction
{
    /**
     * The extend() method must be overloaded by any Transaction Type
     * which needs to extend the base DTO structure.
     *
     * @return array
     */
    public function extend() 
    {
        // Transfer transaction is *default transaction type* for NEM.
        // No data needs to be added to the base transaction DTO.
        return [];
    }

    /**
     * The extendMeta() method must be overloaded by any Transaction Type
     * which needs to extend the base META structure.
     *
     * @return array
     */
    public function extendMeta()
    {
        // Transfer transaction is *default transaction type* for NEM.
        // No data needs to be added to the base transaction META.
        return [];
    }

    /**
     * The extendFee() method must be overloaded by any Transaction Type
     * which needs to extend the base FEE to a custom FEE.
     *
     * @return array
     */
    public function extendFee()
    {
        // Transfer transaction is *default transaction type* for NEM.
        // No more fees need to be added to the base transaction FEE.
        return 0;
    }
}
