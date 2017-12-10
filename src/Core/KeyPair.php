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
namespace NEM\Core;

use NEM\Contracts\KeyPair as KeyPairContract;

class KeyPair
    implements KeyPairContract
{
    /**
     * The *hexadecimal* data of the public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * The *reversed hexadecimal* data of the private key.
     *
     * @var string
     */
    protected $secretKey;

    /**
     * This method creates a KeyPair 
     */
    public function create($hexData)
    {

    }

    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * Binary data should and will only be used internally.
     *
     * @internal
     * @return  string
     */
    public function getPublicKey()
    {
    }

    /**
     * This method should return a Hexadecimal representation
     * of a Private Key.
     *
     * Binary data should and will only be used internally.
     *
     * @internal
     * @return  string
     */
    public function getPrivateKey()
    {
    }
}