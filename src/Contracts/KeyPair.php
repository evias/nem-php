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
namespace NEM\Contracts;

/**
 * This is the KeyPair interface
 *
 * This interface defines a Contract for NEM network Key Pairs.
 *
 * A Key Pair consists of a Public Key and a Private Key. The 
 * address of the account can always be computed from the 
 * Public Key.
 *
 * @author Grégory Saive <greg@evias.be>
 */
interface KeyPair
{
    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * Binary data should and will only be used internally.
     *
     * @internal
     * @return  string
     */
    public function getPublicKey();

    /**
     * This method should return a Hexadecimal representation
     * of a Private Key.
     *
     * Binary data should and will only be used internally.
     *
     * @internal
     * @return  string
     */
    public function getPrivateKey();
}
