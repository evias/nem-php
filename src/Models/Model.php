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

use \Illuminate\Support\Collection;
use \Illuminate\Support\Str;
use \Illuminate\Support\Arr;

use NEM\Infrastructure\ServiceInterface;
use NEM\Models\Mutators\ModelMutator;
use NEM\Contracts\DataTransferObject;
use NEM\Contracts\Serializable;

use ArrayObject;
use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;

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
 *         return new Address($data ?: $this->getAttribute("address"));
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
 *         return new MosaicCollection($data ?: $this->getAttribute("cosignatories"));
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
 * ```
 *
 * @example Example of aliased Attributes
 *
 * Settings a key-values array in the `fillable` property will trigger the 
 * aliases attributes feature. This lets you give attribute names the desired
 * aliases. The value in the `fillable` property should represent the dot
 * notation of the exact path *to the value* in the corresponding NIS DTO.
 *
 * ```
 * class MyAccountModel {
 *     protected $fillable = [
 *         "status" => "meta.status",
 *         "address" => "account.address",
 *     ];
 * }
 * ```
 */
class Model
    extends ArrayObject
    implements DataTransferObject, Serializable
{
    /**
     * Inject getSerializer() and setSerializer()
     * 
     * @see \NEM\Traits\Serializable
     * @see \NEM\Core\Serializer
     */
    use \NEM\Traits\Serializable;

    /**
     * List of fillable attributes
     *
     * When values are provided they should correspond to the *dot notation*
     * of the value in the NIS Data Transfer Object reference.
     *
     * Values are optional.
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
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Attributes values stored in *dot notation*.
     *
     * @see https://laravel.com/api/4.2/Illuminate/Support/Arr.html#method_dot
     * @internal
     * @var array
     */
    protected $dotAttributes = [];

    /**
     * Field names sorted with indexes.
     * 
     * @internal
     * @var array
     */
    protected $sortedFields = [];

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
    public function __construct($attributes = [])
    {
        if (is_array($attributes) && !empty($attributes)) {
            // assign attributes
            $this->setAttributes($attributes);
        }
        elseif ($attributes instanceof DataTransferObject) {
            // copy attributes
            $this->setAttributes($attributes->getAttributes());
        }
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
     * @param   null|string $filterByKey    non-null will return only the named sub-dtos.
     * @return  array       Associative     array representation of the object *compliable* with NIS definition.
     */
    public function toDTO($filterByKey = null)
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

        if ($filterByKey && isset($dtos[$filterByKey]))
            return $dtos[$filterByKey];

        return $dtos;
    }

    /**
     * This method should return a *byte-array* with UInt8
     * representation of bytes for the said object.
     *
     * Each class implementing this interface should provide
     * with a specific *serializing process* where the data
     * is grouped and organized correctly according to the 
     * NIS reference.
     * 
     * The `parameters` argument can be used to filter sub-dtos
     * or fields of a given model instance.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $json = json_encode($this->toDTO($parameters));
        return $this->getSerializer()->serializeString($json);
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
        $fields = array_keys($this->fillable);
        if (!empty($fields) && is_integer($fields[0]))
            // only alias provided
            $fields = array_values($this->fillable);

        return array_merge($fields, $this->appends, array_keys($this->attributes));
    }

    /**
     * Setter for the `attributes` property.
     *
     * This method uses a *dot notation* for attributes and resolves
     * relationships automatically when needed.
     *
     * @internal
     * @return  \NEM\Contracts\DataTransferObject
     */
    public function setAttributes(array $attributes)
    {
        $flattened = array_dot($attributes);

        $fields = $this->getFields();
        if (empty($fields))
            $fields = array_keys($attributes);

        foreach ($fields as $field) : 

            // make sure we have an aliased fields list
            $fillableKeys   = array_keys($this->fillable);
            $aliasedFields  = !empty($fillableKeys) && !is_integer($fillableKeys[0]);

            // read full path to attribute (get dot notation if available).
            $attribFullPath = isset($this->fillable[$field]) ? $this->fillable[$field] : $field;

            $hasByPath  = array_has($flattened, $attribFullPath);
            $hasByAlias = array_has($attributes, $field);

            if (! $hasByPath && ! $hasByAlias) {
                // try deep find and continue

                // browse attributes array in depth.
                $attrib = $attributes;
                foreach (explode(".", $attribFullPath) as $key) {
                    if (! isset($attrib[$key]))
                        $attrib[$key] = null;

                    $attrib = $attrib[$key];
                }

                $this->setAttribute($field, $attrib);
                continue;
            }

            // use attribute path or alias
            $attribValue = $hasByPath ? array_get($flattened, $attribFullPath)
                                      : array_get($attributes, $field);

            $this->setAttribute($field, $attribValue);
        endforeach ;

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
        // prevent order of fields from changing
        $sortedAttribs = [];
        foreach ($this->sortedFields as $ix => $attribute) {
            $sortedAttribs[$attribute] = $this->attributes[$attribute];
        }

        return $sortedAttribs;
    }

    /**
     * Getter for singular attribute values by name.
     *
     * @param   string  $alias   The attribute field alias.
     * @return  mixed
     */
    public function getAttribute($alias, $doCast = true)
    {
        if (property_exists($this, $alias))
            return $this->$alias;

        if (array_key_exists($alias, $this->attributes))
            // value available
            return $this->castValue($alias, $this->attributes[$alias], $doCast);

        // check whether we have an aliased fillable fields list.
        $fillableKeys = array_keys($this->fillable);
        $aliasedFields = !empty($fillableKeys) && !is_integer($fillableKeys[0]);

        if ($aliasedFields) {
            // get the dot notation for the said `alias` alias (the dot notation is the full path).
            $dotNotation = isset($this->fillable[$alias]) ? $this->fillable[$alias] : $alias;
            if (array_key_exists($dotNotation, $this->dotAttributes))
                return $this->castValue($alias, $this->dotAttributes[$dotNotation], $doCast);
        }

        if (! $this->hasRelation($alias) || ! isset($this->related[$alias]))
            // no value available + no relation
            return isset($this->dotAttributes[$alias]) ? $this->castValue($alias, $this->dotAttributes[$alias], $doCast) : null;

        if ($this->related[$alias] instanceof Model) {
            // getAttribute should return DTO data.
            return $this->attributes[$alias];
        }
        elseif ($this->related[$alias] instanceof ModelCollection) {
            return $this->related[$alias]->toDTO();
        }

        return $this->related[$alias];
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
        // new fields are detected to *prevent the order of fields*
        // from changing during data processes.

        $attributes = array_keys($this->attributes);
        $cntAttribs = count($attributes);
        if (! in_array($name, $attributes)) {
            // new field detected, store index for correct order
            $this->sortedFields[$cntAttribs-1] = $name;
        }

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

        // get the dot notation for the said `name` alias (the dot notation is the full path).
        $dotNotation = isset($this->fillable[$name]) ? $this->fillable[$name] : $name;
        $this->dotAttributes[$dotNotation] = $data;

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
     * Helper method to check whether a *relationship can be resolved*
     * or not.
     *
     * Relations are defined using *fields aliases*. The last part of the dot 
     * notation of the attribute name should be the relation alias.
     * 
     * @param   string   $alias
     * @return  boolean
     */
    public function hasRelation($alias)
    {
        return array_key_exists($alias, $this->related) || method_exists($this, $alias);
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
     * Getter for the `dotAttributes` property.
     *
     * @return array
     */
    public function getDotAttributes()
    {
        return $this->dotAttributes;
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

        $attrib = $this->getAttribute($name);
        return $attrib;
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
     * This method will read the `casts` instance property and
     * automatically cast the `value` provided in the set cast
     * type.
     *
     * @see http://php.net/manual/en/function.settype.php
     * @param   string      $field
     * @param   mixed       $value
     * @return  mixed
     */
    public function castValue($field, $value, $cast = true)
    {
        if (! array_key_exists($field, $this->casts) || ! $cast)
            // no cast configured for said field. Nothing done.
            return $value;

        $types = [
            "boolean", "bool", "integer", "int", "float", "double",
            "string", "array", "object", "null"
        ];

        // validate type cast is valid
        $type = $this->casts[$field];
        if (! in_array($type, $types)) {
            throw new RuntimeException("Cast of field '" . $field . "' to type '" . $type . "' not possible. Please define only scalar type casts.");
        }

        $output = $value;
        $result = settype($output, $type);

        if ($result === true)
            return $output;

        return $value;
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
            throw new BadMethodCallException("Relationship for field '" . $alias . "' not configured in " . get_class($this));
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
