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

class MosaicAttachment
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "mosaicId",
        "quantity"
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "mosaicId",
    ];

    /**
     * Address DTO automatically cleans address representation.
     *
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO()
    {
        return [
            "mosaicId" => $this->mosaicId()->toDTO(),
            "quantity" => (int) $this->quantity,
        ];
    }

    /**
     * Mutator for `mosaicId` relation.
     *
     * This will return a NIS compliant [MosaicId](https://bob.nem.ninja/docs/#mosaicId) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `namespaceId` and `name`.
     * @return  \NEM\Models\Mosaic
     */
    public function mosaicId(array $mosaicId = null)
    {
        return new Mosaic($mosaicId ?: $this->attributes["mosaicId"]);
    }
}
