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

class MosaicLevy
    extends Model
{
    const TYPE_ABSOLUTE = 1;
    const TYPE_PERCENTILE = 2;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "type",
        "recipient",
        "mosaicId",
        "fee",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "mosaicId",
        "recipient",
    ];

    /**
     * Mosaic Levy DTO builds a package with offsets `type`,
     * `recipient`, `mosaicId` and `fee`. 
     * 
     * Those fields are required by NIS for the mosaic levy identification.
     *
     * @return  array       Associative array with fields present in `$fillable` property.
     */
    public function toDTO($filterByKey = null)
    {
        return [
            "type" => $this->type,
            "recipient" => $this->recipient()->address()->toClean(),
            "mosaicId" => $this->mosaic()->toDTO(),
            "fee" => $this->fee,
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
    public function mosaic(array $mosaicId = null)
    {
        return new Mosaic($mosaicId ?: $this->getAttribute("mosaicId"));
    }

    /**
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function recipient($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("recipient")]);
    }
}
