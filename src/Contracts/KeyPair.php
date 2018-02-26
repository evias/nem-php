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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
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
 */
interface KeyPair
{
    /**
     * This method should return a Hexadecimal representation
     * of a Public Key.
     *
     * @internal
     * @param   string|integer                  Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array
     */
    public function getPublicKey($enc = null);

    /**
     * This method should return a Hexadecimal representation
     * of a Private Key.
     *
     * @internal
     * @param   string|integer                  Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array
     */
    public function getPrivateKey($enc = null);

    /**
     * This method should return a Hexadecimal representation
     * of a Secret Key. The secret key is the *reversed byte-level
     * representation of the Private Key*.^
     *
     * @internal
     * @param   string|integer                  Which encoding to use (One of: "hex", "uint8", "int32")
     * @return  \NEM\Core\Buffer|string|array
     */
    public function getSecretKey($enc = null);
}
