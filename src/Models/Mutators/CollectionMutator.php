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
    public function mutate($name, array $items)
    {
        // snake_case to camelCase
        $modelClass = "\\NEM\\Models\\" . Str::studly($name);

        if (!class_exists($modelClass)) {
            throw new BadMethodCallException("Model class '" . $modelClass . "' could not be found in \\NEM\\Model namespace.");
        }

        $mutator = new ModelMutator();
        $collection = new ModelCollection;
        for ($i = 0, $m = count($items); $i < $m; $i++) {
            if (!isset($items[$i]))
                dd($items, $name);

            $data = $items[$i] instanceof DataTransferObject ? $items[$i]->toDTO() : $items[$i];

            // load Model instance with item data
            $collection->push($mutator->mutate($name, $data));
        }

        return $collection;
    }
}
