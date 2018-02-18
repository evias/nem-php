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

class Mosaic
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "namespaceId",
        "name"
    ];

    /**
     * Class method to create a new `Mosaic` object from `namespace`
     * name and `mosaic` mosaic name.
     * 
     * @param   string      $namespace
     * @param   string      $mosaic
     * @return  \NEM\Models\Mosaic
     */
    static public function create(string $namespace, string $mosaic = null)
    {
        if (empty($mosaic)) {
            // `namespace` should contain `FQN`
            $fullyQualifiedName = $namespace;
            $splitRegexp = "/([a-zA-Z0-9\\-_\.]+):([a-zA-Z0-9\\-_\.]+)/";

            // split with format: `namespace:mosaic`
            $namespace = preg_replace($splitRegexp, "$1", $fullyQualifiedName);
            $mosaic    = preg_replace($splitRegexp, "$2", $fullyQualifiedName);
        }

        if (empty($namespace) || empty($mosaic)) {
            throw new RuntimeException("Missing namespace or mosaic name for \\NEM\\Models\\Mosaic instance.");
        }

        return new static([
            "namespaceId" => $namespace,
            "name" => $mosaic
        ]);
    }

    /**
     * Mosaic DTO build a package with offsets `namespaceId` and
     * `name` as required by NIS for the mosaic identification.
     *
     * @return  array       Associative array with key `namespaceId` and `name` required for a NIS *compliable* mosaic identification.
     */
    public function toDTO($filterByKey = null)
    {
        return [
            "namespaceId" => $this->namespaceId,
            "name" => $this->name,
        ];
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *mosaicId* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // shortcuts
        $namespace = $this->namespaceId;
        $mosaicName = $this->name;
        $serializer = $this->getSerializer();

        $serializedNS = $serializer->serializeString($namespace);
        $serializedMos = $serializer->serializeString($mosaicName);

        return $serializer->aggregate($serializedNS, $serializedMos);
    }

    /**
     * Getter for the *fully qualified name* of the Mosaic.
     *
     * @return string
     */
    public function getFQN()
    {
        return sprintf("%s:%s", $this->namespaceId, $this->name);
    }
}
