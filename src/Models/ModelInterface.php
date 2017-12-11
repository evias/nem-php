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

use NEM\NemSDK;
use NEM\Models\Account\Account;
use NEM\Models\Account\Address;
use NEM\Models\Fee\Fee;
use NEM\Models\Mosaic\Mosaic;
use NEM\Models\Mosaic\Xem;
use NEM\Models\Namespaces\Namespaces;
use NEM\Models\Blockchain\Blockchain;
use NEM\Models\Transaction\Transaction;

interface ModelInterface
{
    /**
     * Setter for the `attributes` property.
     *
     * @return  \NEM\Models\ModelInterface
     */
    public function setAttributes(array $attributes);

    /**
     * Getter for the `attributes` property.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Helper method to build NIS compliant Data Transfer
     * Objects.
     *
     * @return array
     */
    public function toDTO();
}
