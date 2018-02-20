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

class MultisigInfo
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "cosignatoriesCount",
        "minCosignatories",
    ];

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "cosignatoriesCount" => "int",
        "minCosignatories" => "int",
    ];

    /**
     * MultisigModification DTO automatically builds a *NIS compliant*
     * [MultisigCosignatoryModification](https://bob.nem.ninja/docs/#multisigCosignatoryModification)
     *
     * @return  array       Associative array with key `modificationType` integer and `cosignatoryAccount` public key.
     */
    public function toDTO($filterByKey = null)
    {
        if (empty($this->cosignatoriesCount) || $this->cosignatoriesCount < 0)
            $this->cosignatoriesCount = 0;

        if (empty($this->minCosignatories) || $this->minCosignatories < 0)
            $this->minCosignatories = 0;

        return [
            "cosignatoriesCount" => (int) $this->cosignatoriesCount,
            "minCosignatories" => (int) $this->minCosignatories,
        ];
    }
}
