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

use \Illuminate\Support\Collection;
use \Illuminate\Support\Str;

use NEM\Infrastructure\ServiceInterface;
use NEM\Models\Mutators\ModelMutator;
use NEM\Contracts\DataTransferObject;

use ArrayObject;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * Generic Model class
 *
 * This class can be used to represent objects that need to be handled
 * with when working with NEM Data Transfer Objects.
 *
 * This abstraction layer aims to provide an easy and pre-configured
 * NIS compliant object creation base, which can be extended upon.
 *
 * @example Example of One-to-One relationship
 *
 * ```
 * class MyNewModel {
 *     protected $relations = [
 *         "address"
 *     ];
 * 
 *     // Relationship Method should return a DataTransferObject or ModelCollection!
 *     public function address($data = null)
 *     {
 *         return new Address($data ?: $this->address);
 *     }
 * }
 * ```
 * 
 * @example Example of One-to-Many relationship
 * 
 * ```
 * class MyNewModel {
 *     protected $relations = [
 *         "cosignatories"
 *     ];
 * 
 *     // Relationship Method should return a DataTransferObject or ModelCollection!
 *     public function cosignatories(array $data = null)
 *     {
 *         return new MosaicCollection($data ?: $this->cosignatories);
 *     }
 * }
 * ```
 *
 * @example Example of automatic Relationship crafting
 * 
 * ```
 * class MyNewModel {
 *     protected $relations = [
 *         "account", // this will automatically try to craft an *Account* 
 *                    // instance with the data stored in this field.
 *     ];
 * }
 */
class Model
    extends ArrayObject
    implements DataTransferObject
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The model instance's attribute values.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [];
    
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The model instance's RELATED OBJECTS.
     *
     * Can't overload this property as it is used internally. 
     * This property suits only the storage of previously loaded
     * relationship objects.
     *
     * @internal
     * @var array
     */
    private $related = [];

    /**
     * Construct a Model instance with attributes data.
     *
     * @param   array   $attributes         Associative array where keys are attribute names and values are attribute values
     * @return  void
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * Generic helper to convert a Model instance to a Data Transfer Object.
     *
     * This will make it easy to bridge implemented models to NEM *NIS compliant*
     * objects.
     *
     * More complicated objects may overload this method to provide with finer
     * grained data transfer objects.
     *
     * @see http://bob.nem.ninja/docs/  NIS API Documentation
     * @return  array       Associative array representation of the object *compliable* with NIS definition.
     */
    public function toDTO()
    {
        $dtos = [];
        foreach ($this->getAttributes() as $attrib => $data) {

            $attribDTO = $data; // default (unparsed or scalar)

            // we may need to parse the attribute relation or use
            // the model to get the subordinated Data Transfer Object.
            if ($data instanceof DataTransferObject || $data instanceof ModelCollection) {
                // sub DTO convert to array
                $attribDTO = $data->toDTO();
            }
            elseif (in_array($attrib, $this->relations) || method_exists($this, $attrib)) {
                // unparsed sub DTO passed - parse the DTO to make sure
                // we are working with NEM *NIS compliant* objects *always*.
                $related   = $this->resolveRelationship($attrib, $data);
                $attribDTO = $related->toDTO();
            }

            $dtos[$attrib] = $attribDTO;
        }

        return $dtos;
    }

    /**
     * Setter for the `fillable` property.
     *
     * @param   array   $fieldNames     An array of field names
     * @return  \NEM\Models\ModelInterface
     */
    public function setFields(array $fieldNames)
    {
        $this->fillable = $fieldNames;
        $this->appends  = [];
        return $this;
    }

    /**
     * Getter for the model's field names.
     *
     * This method will merge the `fillable` property and the `appends`
     * properties into one array of field names.
     *
     * @param   array   $fieldNames     An array of field names
     * @return  \NEM\Models\ModelInterface
     */
    public function getFields()
    {
        return array_merge($this->fillable, $this->appends);
    }

    /**
     * Setter for the `attributes` property.
     *
     * @return  \NEM\Contracts\DataTransferObject
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attrib => $data)
            $this->setAttribute($attrib, $data);

        return $this;
    }

    /**
     * Getter for the `attributes` property.
     *
     * The `attributes` property holds an array with key values representing
     * the Data of this Model instance.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Getter for singular attribute values by name.
     *
     * @param   string  $name   The attribute name.
     * @return  mixed
     */
    public function getAttribute($name)
    {
        if (in_array($name, $this->attributes))
            return $this->attributes[$name];

        return null;
    }

    /**
     * Setter for singular attribute values by name.
     *
     * @param   string  $name   The attribute name.
     * @param   mixed   $data   The attribute data.
     * @return  mixed
     */
    public function setAttribute($name, $data)
    {
        if (in_array($name, $this->relations) || method_exists($this, $name)) {
            // subordinate DTO data passed (one-to-one, one-to-many, etc.)
            // build the linked Model using Relationship configuration.
            $this->related[$name] = $this->resolveRelationship($name, $data);
            $this->attributes[$name] = $data; // attributes property contains scalar data
        }
        elseif (empty($this->fillable) || in_array($name, $this->getFields())) {
            // attribute is fillable or any attribute is fillable.
            $this->attributes[$name] = $data;
        }

        return $this;
    }

    /**
     * Setter for the `relations` property.
     *
     * The `relations` property should contain a list of field names
     * which will automatically be resolved into relationships.
     *
     * @param   array   $relations      An array of field names which need to be parsed as relationships.
     * @return  \NEM\Contracts\DataTransferObject
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * Getter for the `relations` property.
     *
     * @return  array       An array of field names
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Setter for the `appends` property.
     *
     * The `appends` property holds a *second* list of `fillable` fields.
     *
     * This list is used when extending models instances to provide with
     * class specific field additions.
     *
     * @see \NEM\Models\Transaction
     * @param   array   $relations      An array of field names which need to be parsed as relationships.
     * @return  \NEM\Contracts\DataTransferObject
     */
    public function setAppends(array $appends)
    {
        $this->appends = $appends;
        return $this;
    }

    /**
     * Getter for the `appends` property.
     *
     * @return  array       An array of field names
     */
    public function getAppends()
    {
        return $this->appends;
    }

    /**
     * Helper for direct attributes/property access.
     *
     * @see ArrayObject
     * @param   string      $name   The property/attribute name.
     * @return  mixed
     */
    public function __get($name)
    {
        // check for existing related object
        if (array_key_exists($name, $this->related))
            return $this->related[$name]; // return

        // attributes prevail over class properties
        if (array_key_exists($name, $this->attributes))
            return $this->attributes[$name];

        if ($this->offsetExists($name))
            return $this->offsetGet($name);

        return null;
    }

    /**
     * Helper for direct attributes/property setting.
     *
     * @see ArrayObject
     * @param   string      $name    The property/attribute name.
     * @param   mixed       $value   The new property/attribute value.
     * @return  mixed
     */
    public function __set($name, $value)
    {
        if ($this->offsetExists($name))
            // instance property available too
            $this->offsetSet($name, $value);

        // attributes prevail over class properties in __get()
        $this->setAttribute($name, $value);
        return $this->attributes[$name];
    }

    /**
     * Check whether or not a data key exists by name.
     *
     * @param   string  $name   A data name to check for
     * @return  boolean
     */
    public function __isset($name)
    {
        // if `attributes` is not set, the key should not be considered as set.
        return isset($this->attributes[$name]);
    }

    /**
     * Unsets an data key by name.
     *
     * @param   string  $name   A data name to check for
     * @return  void
     */
    public function __unset($name)
    {
        // no need to unset class property values, they are the defaults
        unset($this->attributes[$name]);
    }

    /**
     * Build a Model Relationship between \NEM\Contracts\DataTransferObject objects.
     *
     * Relation can be defined with the `$relations` property on extending classes.
     *
     * Relationships for which there is no method implementation, will automatically
     * try to instantiate using the uppercased CamelCase representation of the relation
     * alias.
     * 
     * @param   string  $alias      Relation alias name.
     * @param   array   $data       The relationship data.
     * @return  DataTransferObject|ModelCollection
     */
    public function resolveRelationship($alias, $data)
    {
        if (! in_array($alias, $this->relations) && ! method_exists($this, $alias)) {
            throw new InvalidArgumentException("Relationship for field '" . $alias . "' not configured in " . get_class($this));
        }

        if (method_exists($this, $alias)) {
            // Use relationship *method*
            $related = $this->$alias($data);
            return $related;
        }

        // try to craft relationship with simple snake_case to camelCase
        $relation = "\\NEM\\Models\\" . Str::studly($alias);

        if (! class_exists($relation)) {
            throw new BadMethodCallException("Relationship method for field '" . $alias . "' not implemented in " . get_class($this));
        }

        $related  = new $relation($data);
        return $related;
    }
}