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
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Infrastructure;

use Illuminate\Support\Str;

use NEM\API;
use NEM\Contracts\Service as ServiceContract;

/**
 * This is the Service class
 *
 * This class provides with an abstraction layer for
 * NEM Infrastructure services implementing API endpoints
 * of the NEM network nodes.
 * 
 * The \NEM\API class is used internally to provide with
 * connectivity to the NEM network and its nodes.
 */
class Service
    implements ServiceContract
{
    /**
     * The NEM API wrapper instance.
     *
     * @var \NEM\API
     */
    protected $api;

    /**
     * The Base URL for this Service.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Constructor for Infrastructure objects.
     *
     * @return void
     */
    public function __construct(API $api = null) 
    {
        $this->api = $api ?: new API([]);
    }

    /**
     * Setter for the `baseUrl` property.
     *
     * @param   string  $baseUrl
     * @return  \NEM\Infrastructure\Abstract
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Getter for the `baseUrl` property.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl ?: "";
    }

    /**
     * Helper for creating HTTP request full paths.
     *
     * @param   string      $uri
     * @param   array       $params 
     * @param   boolean     $withQuery 
     * @return  string
     */
    public function getPath($uri, array $params, $withQuery = true)
    {
        $cleanUrl = trim($this->getBaseUrl(), "/ ");
        $cleanUri = trim($uri, "/ ");

        if ($withQuery === false || empty($params))
            return sprintf("%s/%s", $this->getBaseUrl(), $cleanUri);

        // build HTTP query for GET request
        $query = http_build_query($params);
        return sprintf("/%s/%s?%s", $cleanUrl, $cleanUri, $query);
    }

    /**
     * This __call hook makes sure calls to methods likes createAccountModel,
     * createAccountCollection, createTransactionModel, etc. are parsed correctly
     * and will always create Model instances.
     *
     * This can be used to craft correctly formed Model and Collection objects.
     *
     * @param  string   $name           The model name you would like to create.
     * @param  array    $arguments     The model's attribute values.
     * @return \NEM\Contract\DataTransferObject
     */
    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name))
            // method overload exists, call it.
            return call_user_func_array([$this, $name], $arguments);

        // parse createXModel and createXCollection method calls automatically
        $parts = [];
        if ((bool) preg_match("/^create([A-Za-z0-9\_]+)(Model|Collection)/", $name, $parts)) {
            // valid createX(Model|Collection)() call.
            // @see \NEM\Models namespace classes
            $objectClass = $parts[1];
            $returnType  = $parts[2]; // Model or Collection

            if ("Base" === $objectClass)
                $objectClass = "Model"; // "Base" objects are generic models.

            $class   = "\\NEM\\Models\\Mutators\\" . Str::studly($returnType) . "Mutator"; // ModelMutator or CollectionMutator
            $mutator = new $class();
            $attribs = !empty($arguments) ? $arguments[0] : [];
            return $mutator->mutate(lcfirst($objectClass), $attribs);
        }

        throw new BadMethodCallException("Method or model '" . $name . "' could not be found.");
    }
}
