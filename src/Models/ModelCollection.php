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

class ModelCollection
    extends Collection
{
    /**
     * Overwrite toArray() functionality to make sure it will always use 
     * Data Transfer Objects when the collection is cast into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toDTO();
    }

    /**
     * Generic helper to convert a Collection instance to a Data Transfer Object (array).
     *
     * This will make it easy to bridge implemented models to NEM *NIS compliant*
     * objects.
     *
     * @see http://bob.nem.ninja/docs/  NIS API Documentation
     * @return  array       Array representation of the collection objects *compliable* with NIS definition.
     */
    public function toDTO() 
    {
        $dtos = [];
        foreach ($this->all() as $ix => $item) {
            if ($item instanceof ModelInterface)
                array_push($dtos, $item->toDTO());
            else
                array_push($dtos, $item);
        }

        return $dtos;
    }
}
