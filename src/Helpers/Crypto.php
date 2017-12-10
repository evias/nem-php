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
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM\Helpers;

class Crypto
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
