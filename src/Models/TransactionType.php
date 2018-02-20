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

class TransactionType
{
    /**
     * @internal
     * @var integer
     */
    const TRANSFER = 0x101; // 257

    /**
     * @internal
     * @var integer
     */
    const IMPORTANCE_TRANSFER = 0x801; // 2049

    /**
     * @internal
     * @var integer
     */
    const MULTISIG_MODIFICATION = 0x1001; // 4097

    /**
     * @internal
     * @var integer
     */
    const MULTISIG_SIGNATURE = 0x1002; // 4098

    /**
     * @internal
     * @var integer
     */
    const MULTISIG = 0x1004; // 4100

    /**
     * @internal
     * @var integer
     */
    const PROVISION_NAMESPACE = 0x2001; // 8193

    /**
     * @internal
     * @var integer
     */
    const MOSAIC_DEFINITION = 0x4001; // 16385

    /**
     * @internal
     * @var integer
     */
    const MOSAIC_SUPPLY_CHANGE = 0x4002; // 16386
}
