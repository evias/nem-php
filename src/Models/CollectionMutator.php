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

use \Illuminate\Support\Collection;
use BadMethodCallException;

class CollectionMutator
{
    /**
     * Collect several items into a Collection.
     *
     * @param  string   $name           The model name you would like to store in the collection.
     * @param  array    $items          The collection's items data.
     * @return \Illuminate\Support\Collection
     */
    protected function mutate($name, array $items)
    {
        // snake_case to camelCase
        $normalized = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $name)), '_');
        $className  = ucfirst($normalized);
        $modelClass = "\\NEM\\Models\\" . $className;

        if (!class_exists($modelClass)) {
            throw new BadMethodCallException("Model class '" . $modelClass . "' could not be found in \\NEM\\Model namespace.");
        }

        return collect($items);
    }
}
