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
namespace NEM\Models\Mutators;

use Illuminate\Support\Str;
use NEM\Models\Mutators\ModelMutator;
use NEM\Models\ModelCollection;
use NEM\Contracts\DataTransferObject;

use BadMethodCallException;

class CollectionMutator
{
    /**
     * Collect several items into a Collection of Models.
     *
     * The \NEM\Models\ModelMutator will be used internally to craft singular
     * model objects for each item you pass to this method.
     *
     * @internal
     * @param  string   $name           The model name you would like to store in the collection.
     * @param  array    $items          The collection's items data.
     * @return \Illuminate\Support\Collection
     */
    public function mutate($name, $items)
    {
        // snake_case to camelCase
        $modelClass = "\\NEM\\Models\\" . Str::studly($name);

        if (!class_exists($modelClass)) {
            throw new BadMethodCallException("Model class '" . $modelClass . "' could not be found in \\NEM\\Model namespace.");
        }

        if ($items instanceof ModelCollection)
            // collection already provided
            return $items;

        $mutator = new ModelMutator();
        $collection = new ModelCollection;
        $reflection = new $modelClass;

        if ($reflection instanceof ModelCollection) {
            // mutating Collection object, the model class is the singular
            // representation of the passed `$name`.

            $collection = $reflection; // specialize collection
            $name = Str::singular($name); // attachments=attachment, properties=property, etc..
        }

        for ($i = 0, $m = count($items); $i < $m; $i++) {
            if (!isset($items[$i]))
                $data = $items;
            elseif ($items[$i] instanceof DataTransferObject)
                $data = $items[$i]->toDTO();
            else
                $data = $items[$i];

            // load Model instance with item data
            $collection->push($mutator->mutate($name, $data));
        }

        return $collection;
    }
}
