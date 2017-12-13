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
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Core;

class Encryption
{
    /**
     * Helper for password derivation using `iterations` count iterations
     * of SHA3-256.
     *
     * @param   string      $password
     * @param   integer     $iterations
     * @return  string
     */
    public function derive($password, $iterations = 6000) // 6000=NanoWallet
    {

    }
}
