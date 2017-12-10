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
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM\Models;

class TransactionType
{
    /**
     * @internal
     * @var integer
     */
    public const TRANSFER = 0x101; // 257

    /**
     * @internal
     * @var integer
     */
    public const IMPORTANCE_TRANSFER = 0x801; // 2049

    /**
     * @internal
     * @var integer
     */
    public const MULTISIG_MODIFICATION = 0x1001; // 4097

    /**
     * @internal
     * @var integer
     */
    public const MULTISIG_SIGNATURE = 0x1002; // 4098

    /**
     * @internal
     * @var integer
     */
    public const MULTISIG = 0x1004; // 4100

    /**
     * @internal
     * @var integer
     */
    public const PROVISION_NAMESPACE = 0x2001; // 8193

    /**
     * @internal
     * @var integer
     */
    public const MOSAIC_DEFINITION = 0x4001; // 16385

    /**
     * @internal
     * @var integer
     */
    public const MOSAIC_SUPPLY_CHANGE = 0x4002; // 16386
}
