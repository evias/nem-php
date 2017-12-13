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

class MultisigModification
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "modificationType",
        "cosignatoryAccount" // Public Key
    ];

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "modificationType" => "int",
    ];

    /**
     * MultisigModification DTO automatically builds a *NIS compliant*
     * [MultisigCosignatoryModification](https://bob.nem.ninja/docs/#multisigCosignatoryModification)
     *
     * @return  array       Associative array with key `modificationType` integer and `cosignatoryAccount` public key.
     */
    public function toDTO($filterByKey = null)
    {
        return [
            "modificationType" => $this->modificationType,
            "cosignatoryAccount" => $this->cosignatoryAccount,
        ];
    }
}
